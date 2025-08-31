<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\FilterableByLocation;

class Vlan extends BaseModel
{
    use HasFactory, FilterableByLocation;

    protected $fillable = ['vlan_id', 'name'];

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'customer_vlan');
    }
}
