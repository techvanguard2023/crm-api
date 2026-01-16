<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Domain extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'name',
        'status',
        'expired_at',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
