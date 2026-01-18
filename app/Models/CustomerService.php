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
    ];
}
