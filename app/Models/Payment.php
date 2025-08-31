<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToLocation;

class Payment extends BaseModel
{
    use HasFactory, BelongsToLocation;

    protected $fillable = [
        'location_id',
        'customer_id',
        'amount',
        'duration_months',
        'payment_date',
        'sales_person',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}