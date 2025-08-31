<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

abstract class BaseModel extends Model
{
    use LogsActivity;

    /**
     * Metode ini akan secara otomatis mengatur logging
     * untuk semua model yang mewarisinya.
     */
    public function getActivitylogOptions(): LogOptions
    {
        // Mengambil semua kolom yang bisa diisi dari properti $fillable model anak
        $fillableFields = $this->getFillable();

        // Mengambil nama kelas model anak (misal: "Transaction", "Customer")
        $logName = Str::of(class_basename($this))->snake(' ')->title()->toString();

        return LogOptions::defaults()
            // Secara dinamis mencatat semua kolom yang ada di $fillable
            ->logOnly($fillableFields)
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "melakukan aksi {$eventName} pada data {$logName}")
            ->useLogName($logName);
    }
}
