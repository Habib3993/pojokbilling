<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToLocation;

class Transaction extends Model
{
    use HasFactory, BelongsToLocation;

    protected $fillable = [
        'location_id',
        'date',
        'status',
        'note',
        'debit',
        'kredit',
        'optional_note',
        'payment_id',
    ];
}