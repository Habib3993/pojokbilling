<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class CreateBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Jalankan perintah backup. Ini akan berhasil karena dijalankan oleh worker.
            Artisan::call('backup:run', ['--only-db' => true]);
            Log::info('Backup job executed successfully.');
        } catch (\Exception $e) {
            Log::error('Backup job failed: ' . $e->getMessage());
        }
    }
}
