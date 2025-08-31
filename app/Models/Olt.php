<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FilterableByLocation;

class Olt extends BaseModel
{
    use HasFactory, FilterableByLocation;

    protected $fillable = [
        'location_id',
        'name',
        'ip_address',
        'username',
        'password',
    ];

    protected function password(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => decrypt($value),
            set: fn ($value) => encrypt($value),
        );
    }
}