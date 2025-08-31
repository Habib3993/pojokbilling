<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    // Menggabungkan semua Trait yang dibutuhkan
    use HasFactory, Notifiable, HasRoles, LogsActivity;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'location_id', // <-- PASTIKAN INI ADA untuk multi-lokasi
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Fungsi ini akan mengatur bagaimana aktivitas dicatat.
     * (Fungsi lama Anda dipertahankan)
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email']) // Hanya catat perubahan pada kolom ini
            ->logOnlyDirty()            // Hanya catat jika ada perubahan
            ->dontSubmitEmptyLogs()     // Jangan simpan log kosong
            ->setDescriptionForEvent(fn(string $eventName) => "melakukan aksi {$eventName} pada data user")
            ->useLogName('User'); // Nama log untuk mempermudah filter
    }

    /**
     * Relasi ke Location untuk sistem multi-lokasi.
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
