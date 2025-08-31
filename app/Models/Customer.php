<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToLocation;

class Customer extends BaseModel
{
    use HasFactory, BelongsToLocation;

    protected $fillable = [
        'location_id',
        'name', 
        'phone', 
        'lokasi', 
        'package_id', 
        'serial_number',
        'server', 
        'distribusi',
        'odp', 
        'subscription_date',
        'sales',
        'olt_id',
        'register_port',
        'setor',
        'active_until',
        // Kolom baru
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    // Relasi baru ke OLT
    public function olt()
    {
        return $this->belongsTo(Olt::class);
    }

    // Relasi baru ke VLAN (Many-to-Many)
    public function vlans()
    {
        return $this->belongsToMany(Vlan::class, 'customer_vlan');
    }
    /**
     * REVISI: Tambahkan dua fungsi ini.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latestOfMany('payment_date');
    }
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
    
}
