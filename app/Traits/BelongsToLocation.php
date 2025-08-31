<?php

namespace App\Traits;

use App\Models\Location;
use App\Scopes\LocationScope;
use Illuminate\Support\Facades\Auth;

trait BelongsToLocation
{
    /**
     * The "booted" method of the model.
     * Ini akan berjalan secara otomatis.
     */
    protected static function bootBelongsToLocation(): void
    {
        // 1. Terapkan saringan otomatis (Global Scope)
        static::addGlobalScope(new LocationScope);

        // 2. Beri "stempel" lokasi saat membuat data baru
        static::creating(function ($model) {
            // Cek jika yang membuat adalah admin, bukan superadmin
            // PERBAIKAN: Gunakan hasRole() untuk mengecek peran
            if (Auth::check() && Auth::user()->hasRole('admin')) {
                // Set location_id model baru sesuai dengan lokasi admin
                $model->location_id = Auth::user()->location_id;
            }
        });
    }

    /**
     * Mendefinisikan relasi bahwa model ini "milik" sebuah Lokasi.
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}