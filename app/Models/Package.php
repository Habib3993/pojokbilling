<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FilterableByLocation;

class Package extends BaseModel
{
    use HasFactory, FilterableByLocation;

    protected $fillable = [
        'location_id',
        'name',
        'speed',
        'price',
        'router_id',
        'ip_pool_id',
    ];

    /**
     * Mendefinisikan relasi ke model Router.
     * Satu paket 'milik' satu router.
     */
    public function router()
    {
        return $this->belongsTo(Router::class);
    }

    /**
     * Mendefinisikan relasi ke model IpPool.
     * Satu paket 'milik' satu IP Pool.
     */
    public function ipPool()
    {
        return $this->belongsTo(IpPool::class);
    }
    /**
     * Accessor untuk mendapatkan nama paket saja (misal: "RETAIL").
     *
     * @return string
     */
    public function getServiceNameAttribute()
    {
        // Memecah string berdasarkan " - " dan mengambil bagian pertama
        $parts = explode(' - ', $this->name);
        return trim($parts[0]);
    }

    /**
     * Accessor untuk mendapatkan kecepatan paket saja (misal: "10Mbps").
     *
     * @return string|null
     */
    public function getSpeedAttribute()
    {
        // Memecah string berdasarkan " - " dan mengambil bagian kedua
        $parts = explode(' - ', $this->name);
        // Pastikan ada bagian kedua sebelum mengaksesnya
        return isset($parts[1]) ? trim($parts[1]) : null;
    }
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}