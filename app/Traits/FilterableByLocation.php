<?php

namespace App\Traits;

use App\Models\Location;
use App\Scopes\LocationScope;

trait FilterableByLocation
{
    /**
     * The "booted" method of the model.
     */
    protected static function bootFilterableByLocation(): void
    {
        // Hanya terapkan saringan global agar admin hanya bisa melihat data lokasinya
        static::addGlobalScope(new LocationScope);
    }

    /**
     * Mendefinisikan relasi bahwa model ini "milik" sebuah Lokasi.
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
