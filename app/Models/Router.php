<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FilterableByLocation;

class Router extends BaseModel
{
    use HasFactory, FilterableByLocation;

    protected $fillable = [
        'location_id',
        'name',
        'ip_address',
        'port',
        'username',
        'password',
    ];

/*    protected function password(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => decrypt($value),
            set: fn ($value) => encrypt($value),
        );
    }

    public function ipPools()
    {
        return $this->hasMany(IpPool::class);
    }

    /**
     * REVISI: Tambahkan fungsi ini untuk mendefinisikan relasi.
     * Ini memberitahu Laravel bahwa satu Router bisa dimiliki oleh banyak Paket.
     */
    public function packages()
    {
        return $this->hasMany(Package::class);
    }
    protected static function booted(): void
    {
        // Event ini akan berjalan TEPAT SEBELUM sebuah router dihapus dari database
        static::deleting(function (Router $router) {
            // Hapus semua IP Pool yang memiliki router_id yang sama dengan router ini
            $router->ipPools()->delete();
        });
    }
}
