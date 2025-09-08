<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Jobs\CreateBackupJob;

class BackupController extends Controller
{
    public function index()
    {
        try {
            $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
            
            // Debug: Log the backup name from config
            $backupName = config('backup.backup.name');
            Log::info('Looking for backups in folder: ' . $backupName);
            
            $files = $disk->files($backupName);
            Log::info('Files found: ', $files);
            
            $backups = [];
            foreach ($files as $file) {
                if ((substr($file, -4) === '.zip' || substr($file, -4) === '.sql') && $disk->exists($file)) {
                    $backups[] = [
                        'file_path' => $file,
                        'file_name' => str_replace($backupName . '/', '', $file),
                        'file_size' => $this->formatSizeUnits($disk->size($file)),
                        'last_modified' => date('d M Y, H:i', $disk->lastModified($file)),
                    ];
                }
            }
            $backups = array_reverse($backups);

            Log::info('Total backups found: ' . count($backups));
            return view('backup.index', compact('backups'));
        } catch (\Exception $e) {
            Log::error('Error in backup index: ' . $e->getMessage());
            return view('backup.index', ['backups' => []]);
        }
    }

    private function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } else {
            $bytes = $bytes . ' bytes';
        }
        return $bytes;
    }

    public function create(Request $request)
    {
        try {
            // Debug log
            Log::info('Backup create method called');
            Log::info('Request data: ', $request->all());
            
            // Periksa koneksi database terlebih dahulu
            try {
                \DB::connection()->getPdo();
                Log::info('Database connection successful');
            } catch (\Exception $e) {
                Log::error('Database connection failed: ' . $e->getMessage());
                return back()->with('error', 'Gagal koneksi database: ' . $e->getMessage());
            }
            
            // Periksa apakah direktori backup exists
            $backupPath = storage_path('app/' . config('backup.backup.name'));
            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
                Log::info('Created backup directory: ' . $backupPath);
            }
            
            // Cek parameter untuk menentukan mode backup
            $useQueue = $request->input('use_queue', '0') === '1';
            
            if ($useQueue) {
                // Mode Queue - perlu queue worker
                Log::info('Dispatching backup job to queue...');
                CreateBackupJob::dispatch();
                return back()->with('success', 'Permintaan backup telah dikirim ke antrian. Backup akan diproses dalam beberapa saat. Refresh halaman untuk melihat progress.');
            } else {
                // Mode Langsung - tanpa queue worker
                Log::info('Starting direct backup...');
                return $this->createBackupDirectly();
            }
            
        } catch (\Exception $e) {
            Log::error('Exception saat backup: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function createBackupDirectly()
    {
        try {
            // Set timeout dan memory limit
            set_time_limit(300);
            ini_set('memory_limit', '512M');
            
            Log::info('Starting direct backup process...');
            
            // Langsung ke backup manual tanpa mencoba artisan dulu
            return $this->createManualBackup();
            
        } catch (\Exception $e) {
            Log::error('Exception saat membuat backup langsung: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->with('error', 'Terjadi kesalahan saat backup: ' . $e->getMessage());
        }
    }

    private function createManualBackup()
    {
        try {
            // Gunakan Storage disk untuk konsistensi
            $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
            $backupName = config('backup.backup.name');
            
            // Path relatif untuk storage disk
            $backupDir = $backupName;
            
            // Path absolut untuk command mysqldump
            $backupPath = $disk->path($backupDir);
            
            // Pastikan direktori ada
            if (!$disk->exists($backupDir)) {
                $disk->makeDirectory($backupDir);
                Log::info('Created backup directory via Storage: ' . $backupPath);
            }
            
            Log::info('Backup will be stored in: ' . $backupPath);
            
            // Ambil konfigurasi database
            $dbConfig = config('database.connections.' . config('database.default'));
            Log::info('Database config: ', [
                'host' => $dbConfig['host'],
                'port' => $dbConfig['port'],
                'database' => $dbConfig['database'],
                'username' => $dbConfig['username']
            ]);
            
            // Path mysqldump
            $mysqldumpPath = env('DB_DUMP_BINARY_PATH', 'C:\\xampp\\mysql\\bin') . '\\mysqldump.exe';
            
            if (!file_exists($mysqldumpPath)) {
                $mysqldumpPath = 'mysqldump'; // Fallback ke sistem PATH
                Log::info('Using mysqldump from system PATH');
            } else {
                Log::info('Using mysqldump from: ' . $mysqldumpPath);
            }
            
            $filename = 'backup-' . date('Y-m-d-H-i-s') . '.sql';
            $filepath = $backupPath . DIRECTORY_SEPARATOR . $filename;
            
            // Build command
            $command = sprintf(
                '"%s" --host=%s --port=%s --user=%s %s %s',
                $mysqldumpPath,
                $dbConfig['host'],
                $dbConfig['port'],
                $dbConfig['username'],
                $dbConfig['password'] ? '--password=' . escapeshellarg($dbConfig['password']) : '',
                escapeshellarg($dbConfig['database'])
            );
            
            // Redirect output to file
            $command .= ' > ' . escapeshellarg($filepath) . ' 2>&1';
            
            Log::info('Manual backup command: ' . str_replace($dbConfig['password'] ?? '', '***', $command));
            
            // Execute command
            exec($command, $output, $returnCode);
            
            Log::info('Command return code: ' . $returnCode);
            Log::info('Command output: ', $output);
            
            if ($returnCode === 0 && file_exists($filepath) && filesize($filepath) > 0) {
                // Berhasil, coba buat ZIP
                $zipFilename = str_replace('.sql', '.zip', $filename);
                $zipPath = $backupPath . DIRECTORY_SEPARATOR . $zipFilename;
                
                if (class_exists('ZipArchive')) {
                    $zip = new \ZipArchive();
                    if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
                        $zip->addFile($filepath, $filename);
                        $zip->close();
                        unlink($filepath); // Hapus file SQL, simpan yang ZIP
                        
                        Log::info('Manual backup berhasil dibuat (ZIP): ' . $zipFilename);
                        Log::info('File saved to: ' . $zipPath);
                        return back()->with('success', 'Backup database berhasil dibuat! File: ' . $zipFilename . '. Refresh halaman untuk melihat file backup baru.');
                    }
                }
                
                // Jika ZIP gagal, tetap pakai SQL
                Log::info('Manual backup berhasil dibuat (SQL): ' . $filename);
                Log::info('File saved to: ' . $filepath);
                return back()->with('success', 'Backup database berhasil dibuat! File: ' . $filename . '. Refresh halaman untuk melihat file backup baru.');
                
            } else {
                $errorOutput = implode("\n", $output);
                Log::error('Manual backup gagal. Return code: ' . $returnCode . '. Output: ' . $errorOutput);
                
                // Coba method alternatif
                return $this->createBackupAlternative();
            }
            
        } catch (\Exception $e) {
            Log::error('Exception saat manual backup: ' . $e->getMessage());
            return $this->createBackupAlternative();
        }
    }

    private function createBackupAlternative()
    {
        try {
            Log::info('Trying alternative backup method using Laravel DB facade...');
            
            // Gunakan Storage disk untuk konsistensi
            $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
            $backupName = config('backup.backup.name');
            $backupDir = $backupName;
            
            // Pastikan direktori ada
            if (!$disk->exists($backupDir)) {
                $disk->makeDirectory($backupDir);
            }
            
            $filename = 'backup-laravel-' . date('Y-m-d-H-i-s') . '.sql';
            $relativePath = $backupDir . '/' . $filename;
            
            // Ambil semua tabel
            $tables = \DB::select('SHOW TABLES');
            $dbName = config('database.connections.mysql.database');
            
            $sql = "-- Laravel Backup\n";
            $sql .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
            $sql .= "-- Database: " . $dbName . "\n\n";
            
            foreach ($tables as $table) {
                $tableName = $table->{'Tables_in_' . $dbName};
                
                // Get CREATE TABLE statement
                $createTable = \DB::select("SHOW CREATE TABLE `{$tableName}`");
                $sql .= "\n-- Table: {$tableName}\n";
                $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                $sql .= $createTable[0]->{'Create Table'} . ";\n\n";
                
                // Get table data
                $rows = \DB::table($tableName)->get();
                if ($rows->count() > 0) {
                    $sql .= "-- Data for table: {$tableName}\n";
                    foreach ($rows as $row) {
                        $values = array_map(function($value) {
                            return is_null($value) ? 'NULL' : "'" . addslashes($value) . "'";
                        }, (array)$row);
                        
                        $sql .= "INSERT INTO `{$tableName}` VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $sql .= "\n";
                }
            }
            
            // Simpan ke file menggunakan Storage disk
            $disk->put($relativePath, $sql);
            
            Log::info('Alternative backup berhasil dibuat: ' . $filename);
            Log::info('File saved to: ' . $disk->path($relativePath));
            return back()->with('success', 'Backup database berhasil dibuat menggunakan method alternatif! File: ' . $filename . '. Refresh halaman untuk melihat file backup baru.');
            
        } catch (\Exception $e) {
            Log::error('Alternative backup failed: ' . $e->getMessage());
            return back()->with('error', 'Semua method backup gagal. Error: ' . $e->getMessage());
        }
    }

    public function download($fileName)
    {
        try {
            $filePath = config('backup.backup.name') . '/' . $fileName;
            $disk = Storage::disk(config('backup.backup.destination.disks')[0]);

            if ($disk->exists($filePath)) {
                return $disk->download($filePath);
            }
            abort(404, "File backup tidak ditemukan.");
        } catch (\Exception $e) {
            Log::error('Download error: ' . $e->getMessage());
            return back()->with('error', 'Error saat download: ' . $e->getMessage());
        }
    }

    public function destroy($fileName)
    {
        try {
            $filePath = config('backup.backup.name') . '/' . $fileName;
            $disk = Storage::disk(config('backup.backup.destination.disks')[0]);

            if ($disk->exists($filePath)) {
                $disk->delete($filePath);
                return back()->with('success', 'File backup berhasil dihapus.');
            }
            abort(404, "File backup tidak ditemukan.");
        } catch (\Exception $e) {
            Log::error('Delete error: ' . $e->getMessage());
            return back()->with('error', 'Error saat hapus file: ' . $e->getMessage());
        }
    }
}
