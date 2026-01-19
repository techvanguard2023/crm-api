<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRenewal extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_service_id',
        'amount',
        'renewed_at',
        'renews_until',
    ];

    protected $casts = [
        'renewed_at' => 'date',
        'renews_until' => 'date',
        'amount' => 'decimal:2',
    ];

    public function customerService()
    {
        return $this->belongsTo(CustomerService::class);
    }
}
