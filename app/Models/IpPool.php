<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FilterableByLocation;

class IpPool extends BaseModel
{
    use HasFactory, FilterableByLocation;

    protected $fillable = [
        'location_id',
        'pool_name',
        'ranges',
        'router_id'
    ];

    public function router()
    {
        return $this->belongsTo(Router::class);
    }

    /**
     * REVISI: Tambahkan fungsi ini untuk mendefinisikan relasi.
     * Ini memberitahu Laravel bahwa satu IP Pool bisa dimiliki oleh banyak Paket.
     */
    public function packages()
    {
        return $this->hasMany(Package::class);
    }
}
