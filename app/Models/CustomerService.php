<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CustomerService extends Pivot
{
    public $incrementing = true; // Since we added an ID to the pivot table

    protected $table = 'customer_service';

    protected $fillable = [
        'customer_id',
        'service_id',
        'price',
        'recurrence',
        'start_date',
        'next_due_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'next_due_date' => 'date',
    ];

    public function renewals()
    {
        return $this->hasMany(ServiceRenewal::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
