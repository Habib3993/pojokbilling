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
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job should run.
     */
    public $timeout = 300; // 5 menit

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
            Log::info('Starting backup job execution...');
            
            // Set memory limit dan timeout
            ini_set('memory_limit', '512M');
            set_time_limit(300);
            
            // Jalankan perintah backup dengan timeout
            $exitCode = Artisan::call('backup:run', [
                '--only-db' => true,
                '--disable-notifications' => true
            ]);
            
            $output = Artisan::output();
            
            if ($exitCode === 0) {
                Log::info('Backup job executed successfully.');
                Log::info('Backup output: ' . $output);
            } else {
                Log::error('Backup job failed with exit code: ' . $exitCode);
                Log::error('Backup output: ' . $output);
                throw new \Exception('Backup command failed with exit code: ' . $exitCode);
            }
            
        } catch (\Exception $e) {
            Log::error('Backup job failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Re-throw exception agar job masuk ke failed_jobs table
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Backup job failed permanently: ' . $exception->getMessage());
        // Di sini Anda bisa menambahkan notifikasi email atau notifikasi lainnya
    }
}
