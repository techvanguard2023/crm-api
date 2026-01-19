<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_service_id',
        'request_id',
        'your_number',
        'amount',
        'status',
        'paid_at',
        'payment_method',
        'our_number',
        'barcode',
        'digitable_line',
        'txid',
        'pix_copy_paste',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function customerService()
    {
        return $this->belongsTo(CustomerService::class);
    }
}
