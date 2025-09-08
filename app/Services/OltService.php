<?php

namespace App\Services;

use App\Models\Olt;
use App\Models\Package;
use phpseclib3\Net\SSH2;
use Exception;

class OltService
{
    private $ssh;

    /**
     * Fungsi utama untuk menghubungkan dan melakukan login ke OLT.
     */
    private function connect(Olt $olt)
    {
        $this->ssh = new SSH2($olt->ip_address, 22, 60); // IP, Port, Timeout
        
        // PERBAIKAN: Tambahkan algoritma kex yang kompatibel dengan ZTE C320
        $this->ssh->setPreferredAlgorithms([
            'kex' => [
                'diffie-hellman-group1-sha1',
                'diffie-hellman-group14-sha1',
                'diffie-hellman-group-exchange-sha1'
            ],
            'hostkey' => ['ssh-rsa', 'ssh-dss'],
            'client_to_server' => [
                'crypt' => ['aes128-ctr', 'aes128-cbc', '3des-cbc'],
                'mac' => ['hmac-sha1', 'hmac-md5']
            ],
            'server_to_client' => [
                'crypt' => ['aes128-ctr', 'aes128-cbc', '3des-cbc'],
                'mac' => ['hmac-sha1', 'hmac-md5']
            ]
        ]);
        
        if (!$this->ssh->login($olt->username, $olt->password)) {
            throw new Exception('Login SSH ke OLT gagal. Periksa username/password.');
        }
        
        // PERBAIKAN: Tunggu prompt yang lebih fleksibel
        $this->ssh->setTimeout(10);
        $this->ssh->read('/[>#]\s*$/', SSH2::READ_REGEX);
    }

    /**
     * Fungsi internal untuk mengirim perintah dan menunggu prompt balasan.
     */
    private function execute(string $command, string $expectedPrompt = '#')
    {
        $this->ssh->write($command . "\n");
        
        // PERBAIKAN: Regex yang lebih fleksibel untuk berbagai prompt ZTE
        $promptPatterns = [
            '#' => '/ZXAN[^#]*#\s*$/',
            '(config)#' => '/ZXAN\(config[^)]*\)#\s*$/',
            '(config-if)#' => '/ZXAN\(config[^)]*\)#\s*$/',
            '(pon-onu-mng)#' => '/ZXAN\([^)]*onu[^)]*\)#\s*$/'
        ];
        
        $pattern = $promptPatterns[$expectedPrompt] ?? '/ZXAN[^#]*#\s*$/';
        $output = $this->ssh->read($pattern, SSH2::READ_REGEX);

        // PERBAIKAN: Error detection yang lebih akurat untuk ZTE
        if (preg_match('/(%Error|Invalid|Failure|Failed|Unknown command)/i', $output)) {
            throw new Exception("Perintah OLT '{$command}' gagal dengan respons: " . trim($output));
        }
        
        return $output;
    }

    /**
     * Mendaftarkan ONT baru di OLT ZTE C320.
     */
    public function registerOnu(Olt $olt, string $port, string $serialNumber, string $customerName, $odp, Package $package, $vlans)
    {
        $this->connect($olt);

        // 1. Memecah data port (format: 1/2/8:11)
        [$gponPort, $onuId] = explode(':', $port);

        // 2. Ekstrak speed dari nama paket dan format untuk OLT
        $packageName = $package->speed ?? $package->name;
        
        // Ekstrak angka dari nama paket (contoh: "CCRRETAIL - 50Mbps" -> "50MB")
        if (preg_match('/(\d+)Mbps/', $packageName, $matches)) {
            $packageSpeed = $matches[1] . 'MB';
        } elseif (preg_match('/(\d+)M/', $packageName, $matches)) {
            $packageSpeed = $matches[1] . 'MB';
        } else {
            // Fallback: ambil angka pertama dan tambah MB
            $numbers = preg_replace('/[^0-9]/', '', $packageName);
            $packageSpeed = (substr($numbers, 0, 2) ?: '10') . 'MB';
        }
        
        // 3. Bersihkan nama customer untuk command OLT
        $customerNameClean = preg_replace('/[^0-9A-Za-z_-]/', '_', $customerName);

        try {
            // PERBAIKAN: Cek status ONU saat ini
            $this->execute('end', '#');
            $currentStatus = $this->execute("show gpon onu state gpon-olt_{$gponPort}", '#');
            
            $onuExists = str_contains($currentStatus, "gpon-onu_{$port}");
            $onuWorking = str_contains($currentStatus, "working");
            
            \Log::info("OltService: ONU {$port} exists: " . ($onuExists ? 'yes' : 'no') . ", working: " . ($onuWorking ? 'yes' : 'no'));
            
            // Jika ONU sudah ada dan working, langsung lanjut ke konfigurasi
            if (!$onuExists) {
                // PERBAIKAN: Cek apakah serial number sudah terdaftar di tempat lain
                $checkResult = $this->execute("show gpon onu by sn {$serialNumber}", '#');
                
                if (str_contains($checkResult, $serialNumber) && !str_contains($checkResult, 'not found')) {
                    throw new \Exception("Serial Number {$serialNumber} sudah terdaftar di port lain di OLT. Silakan gunakan serial number yang berbeda atau hapus registrasi lama terlebih dahulu.");
                }

                // LANGKAH REGISTRASI: Hanya jika ONU belum terdaftar
                $this->execute('configure terminal', '(config)#');
                $this->execute("interface gpon-olt_{$gponPort}", '(config-if)#');
                
                \Log::info("OltService: Registering new ONU {$onuId} with SN {$serialNumber} on port {$gponPort}");
                $this->execute("onu {$onuId} type ALL sn {$serialNumber}", '(config-if)#');
                $this->execute('exit', '(config)#');
                
                // Tunggu registrasi selesai
                for ($attempt = 1; $attempt <= 5; $attempt++) {
                    sleep(2);
                    $this->execute('end', '#');
                    $statusCheck = $this->execute("show gpon onu state gpon-olt_{$gponPort}", '#');
                    
                    if (str_contains($statusCheck, "gpon-onu_{$port}")) {
                        \Log::info("OltService: ONU {$port} successfully registered on attempt {$attempt}");
                        break;
                    }
                    
                    if ($attempt === 5) {
                        throw new \Exception("ONU {$port} gagal terdaftar setelah 5 percobaan. Silakan cek koneksi fisik ONT dan coba lagi.");
                    }
                }
            } else {
                \Log::info("OltService: ONU {$port} already exists, proceeding to configuration");
            }
            
            // LANGKAH KONFIGURASI: Lanjutkan konfigurasi (baik ONU baru maupun yang sudah ada)
            $this->execute('configure terminal', '(config)#');
            
            // Coba masuk ke interface gpon-onu
            try {
                $this->execute("interface gpon-onu_{$port}", '(config-if)#');
            } catch (\Exception $e) {
                // Jika masih gagal, kemungkinan ONU belum siap, tunggu sebentar
                sleep(3);
                $this->execute("interface gpon-onu_{$port}", '(config-if)#');
            }

            // LANGKAH 5: Set nama customer
            $this->execute("name {$customerNameClean}", '(config-if)#');

            // LANGKAH 6: Set description jika ada ODP
            if ($odp) {
                $odpClean = preg_replace('/[^0-9A-Za-z_-]/', '_', $odp);
                $this->execute("description {$odpClean}", '(config-if)#');
            }

            // LANGKAH 7: Enable SN binding
            $this->execute('sn-bind enable sn', '(config-if)#');

            // LANGKAH 8: Set TCONT dengan format yang benar
            $this->execute("tcont 1 name pppoe profile {$packageSpeed}", '(config-if)#');

            // LANGKAH 9: Set gemport
            $this->execute('gemport 1 name pppoe tcont 1 queue 1', '(config-if)#');

            // LANGKAH 10 & 11: Tambahkan service-port untuk setiap VLAN
            $servicePortIndex = 1;
            foreach ($vlans as $vlan) {
                $this->execute("service-port {$servicePortIndex} vport 1 user-vlan {$vlan->vlan_id} vlan {$vlan->vlan_id}", '(config-if)#');
                $servicePortIndex++;
            }

            // LANGKAH 12: Exit dari interface gpon-onu
            $this->execute('exit', '(config)#');

            // LANGKAH 13: Masuk ke pon-onu-mng
            $this->execute("pon-onu-mng gpon-onu_{$port}", '(pon-onu-mng)#');

            // LANGKAH 14 & 15: Tambahkan service untuk setiap VLAN
            foreach ($vlans as $vlan) {
                $serviceName = $vlan->name ?: "service{$vlan->vlan_id}";
                $serviceNameClean = preg_replace('/[^0-9A-Za-z]/', '', $serviceName);
                $this->execute("service {$serviceNameClean} gemport 1 vlan {$vlan->vlan_id}", '(pon-onu-mng)#');
            }

            // Exit dan simpan konfigurasi
            $this->execute('exit', '(config)#');
            $this->execute('end', '#');
            $this->execute('write', '#');
            
            \Log::info("OltService: Configuration completed successfully for ONU {$port}");

        } catch (Exception $e) {
            $this->ssh->disconnect();
            throw new Exception("Gagal konfigurasi OLT: " . $e->getMessage());
        }

        $this->ssh->disconnect();
    }
    
    /**
     * Menghapus ONT dari OLT.
     */
    public function deleteOnu(Olt $olt, string $port)
    {
        $this->connect($olt);
        [$gponPort, $onuId] = explode(':', $port);

        try {
            $this->execute('configure terminal', '(config)#');
            $this->execute("interface gpon-olt_{$gponPort}", '(config-if)#');
            $this->execute("no onu {$onuId}", '(config-if)#');
            $this->execute('exit', '(config)#');
            $this->execute('end', '#');
            $this->execute('write', '#');
        } catch (Exception $e) {
            $this->ssh->disconnect();
            throw new Exception("Gagal menghapus ONU dari OLT: " . $e->getMessage());
        }

        $this->ssh->disconnect();
    }

    /**
     * Memperbarui konfigurasi ONT.
     */
    public function updateOnu(Olt $oldOlt, string $oldPort, Olt $newOlt, string $newPort, string $serialNumber, string $customerName, $vlans)
    {
        // Hapus dari OLT lama
        if ($oldOlt->id !== $newOlt->id || $oldPort !== $newPort) {
            $this->deleteOnu($oldOlt, $oldPort);
        }
        
        // Tidak perlu registrasi ulang jika sama, cukup update konfigurasi
        // Implementasi update konfigurasi bisa ditambahkan di sini
    }

    /**
     * Uji koneksi ke OLT.
     */
    public function testConnection(string $ip, string $username, string $password)
    {
        $olt = new Olt(['ip_address' => $ip, 'username' => $username, 'password' => $password]);
        $this->connect($olt);
        $this->ssh->disconnect();
        return true;
    }
}
