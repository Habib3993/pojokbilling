<?php

namespace App\Http\Controllers;

use App\Jobs\CreateBackupJob; // <-- Import Job yang baru kita buat
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function index()
    {
        $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
        $files = $disk->files(config('backup.backup.name'));
        
        $backups = [];
        foreach ($files as $file) {
            if (substr($file, -4) === '.zip' && $disk->exists($file)) {
                $backups[] = [
                    'file_path' => $file,
                    'file_name' => str_replace(config('backup.backup.name') . '/', '', $file),
                    'file_size' => $this->formatSizeUnits($disk->size($file)),
                    'last_modified' => date('d M Y, H:i', $disk->lastModified($file)),
                ];
            }
        }
        $backups = array_reverse($backups);

        return view('backup.index', compact('backups'));
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
            $bytes = '0 bytes';
        }
        return $bytes;
    }

    public function create()
    {
        // Cukup kirim tugas ke antrian
        CreateBackupJob::dispatch();

        return back()->with('success', 'Tugas pencadangan telah ditambahkan ke antrian dan akan segera diproses.');
    }

    public function download($fileName)
    {
        $filePath = config('backup.backup.name') . '/' . $fileName;
        $disk = Storage::disk(config('backup.backup.destination.disks')[0]);

        if ($disk->exists($filePath)) {
            return $disk->download($filePath);
        }
        abort(404, "File backup tidak ditemukan.");
    }

    public function destroy($fileName)
    {
        $filePath = config('backup.backup.name') . '/' . $fileName;
        $disk = Storage::disk(config('backup.backup.destination.disks')[0]);

        if ($disk->exists($filePath)) {
            $disk->delete($filePath);
            return back()->with('success', 'File backup berhasil dihapus.');
        }
        abort(404, "File backup tidak ditemukan.");
    }
}
