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
        if (!$this->ssh->login($olt->username, $olt->password)) {
            throw new Exception('Login SSH ke OLT gagal. Periksa username/password.');
        }
        $this->ssh->read('/.*#$/', SSH2::READ_REGEX); // Tunggu prompt awal
    }


    /**
     * Fungsi internal untuk mengirim perintah dan menunggu prompt balasan.
     */
    private function execute(string $command, string $prompt = '#')
    {
        $this->ssh->write($command . "\n");
        // Regex diubah agar lebih toleran terhadap spasi dan baris baru
        $output = $this->ssh->read('/' . preg_quote($prompt, '/') . '\s*$/', SSH2::READ_REGEX);

        if (preg_match('/(%Error|Invalid|Fail)/i', $output)) {
            throw new Exception("Perintah OLT '{$command}' gagal dengan respons: " . $output);
        }
    }

        /**
         * Mendaftarkan ONT baru di OLT ZTE C320.
         */
        /**
     * Mendaftarkan ONT baru di OLT ZTE C320 menggunakan metode "batch script".
     */
    public function registerOnu(Olt $olt, string $port, string $serialNumber, string $customerName, $odp, Package $package, $vlans)
    {
        $this->connect($olt);

        // 1. Memecah data port
        [$gponPort, $onuId] = explode(':', $port);

        // 2. Mengambil nama profil TCONT dari data Paket (PENTING!)
        // Pastikan kolom 'name' di tabel packages sesuai dengan nama profil di OLT
        $tcontProfile = $package->name;

        try {
            // 3. Masuk ke mode konfigurasi
            $this->execute('configure terminal', '(config)#');

            // 4. Bangun blok perintah utama sebagai satu array
            $commandBlock = [
                "interface gpon-olt_{$gponPort}",
                "onu {$onuId} type ALL sn {$serialNumber}",
                '!',
                "interface gpon-onu_{$port}",
                "name {$customerName}",
            ];

            // Tambahkan deskripsi ODP hanya jika diisi
            if ($odp) {
                $commandBlock[] = "description {$odp}";
            }
            
            // Lanjutkan dengan sisa konfigurasi statis
            $commandBlock = array_merge($commandBlock, [
                'sn-bind enable sn', // Disesuaikan dengan contoh Anda (tanpa 'sn' di akhir)
                "tcont 1 profile {$tcontProfile}",
                'gemport 1 tcont 1',
            ]);

            // 5. Tambahkan service-port secara dinamis berdasarkan VLAN
            $servicePortIndex = 1;
            foreach ($vlans as $vlan) {
                $commandBlock[] = "service-port {$servicePortIndex} user-vlan {$vlan->vlan_id} vlan {$vlan->vlan_id}";
                $servicePortIndex++;
            }

            // 6. Lanjutkan ke konfigurasi pon-onu-mng
            $commandBlock[] = '!';
            $commandBlock[] = "pon-onu-mng gpon-onu_{$port}";

            // 7. Tambahkan service secara dinamis
            // Ini akan menggunakan nama dari tabel VLAN (misal: 'PPPoE', 'TR069')
            foreach ($vlans as $vlan) {
                $commandBlock[] = "service {$vlan->name} gemport 1 vlan {$vlan->vlan_id}";
            }

            // 8. Gabungkan semua perintah dalam array menjadi satu string
            $fullCommandString = implode("\n", $commandBlock);

            // 9. Kirim seluruh blok perintah dalam satu kali kirim!
            $this->ssh->write($fullCommandString . "\n");
            // Tunggu prompt config muncul lagi setelah semua perintah selesai
            $output = $this->ssh->read('/\(config.*\)#\s*$/', SSH2::READ_REGEX);

            // Periksa apakah ada error dalam output setelah eksekusi blok
            if (preg_match('/(%Error|Invalid|Fail)/i', $output)) {
                throw new Exception("Terjadi error saat eksekusi blok perintah di OLT: " . $output);
            }

            // 10. Keluar dari mode konfigurasi dan simpan
            $this->execute('end', '#');
            $this->execute('write', '#');

        } catch (Exception $e) {
            // Jika terjadi error, pastikan koneksi ditutup
            $this->ssh->disconnect();
            throw $e; // Lempar kembali errornya ke Controller
        }

        $this->ssh->disconnect();
        // Jika sampai sini, berarti semua berhasil
    }
    
    /**
     * Menghapus ONT dari OLT.
     */
    public function deleteOnu(Olt $olt, string $port)
    {
        $this->connect($olt);
        [$gponPort, $onuId] = explode(':', $port);

        $this->execute('configure terminal', 'ZXAN(config)#');
        $this->execute("interface gpon-olt_{$gponPort}", 'ZXAN(config-if)#');
        $this->execute("no onu {$onuId}", 'ZXAN(config-if)#');
        $this->execute('exit', 'ZXAN(config)#');
        $this->execute('end', 'ZXAN#');
        $this->execute('write', 'ZXAN#');

        $this->ssh->disconnect();
    }

    /**
     * Memperbarui konfigurasi ONT.
     */
    public function updateOnu(Olt $oldOlt, string $oldPort, Olt $newOlt, string $newPort, string $serialNumber, string $customerName, string $odp, Package $package, $vlans)
    {
        $this->deleteOnu($oldOlt, $oldPort);
        $this->registerOnu($newOlt, $newPort, $serialNumber, $customerName, $odp, $package, $vlans);
    }

    /**
     * Uji koneksi ke OLT.
     */
    public function testConnection(string $ip, string $username, string $password)
    {
        $olt = new Olt(['ip_address' => $ip, 'username' => $username, 'password' => $password]);
        $this->connect($olt);
        $this->ssh->disconnect();
    }
}
