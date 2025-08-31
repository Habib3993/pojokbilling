<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends BaseModel
{
    protected $fillable = [
        'customer_id', 'invoice_number', 'billing_period_start', 'due_date', 'amount', 'status'
    ];
    
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
