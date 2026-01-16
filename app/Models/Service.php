<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    public $timestamps = false; // No timestamps in migration

    protected $fillable = [
        'customer_id',
        'name',
        'description',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function prices()
    {
        return $this->hasMany(Price::class);
    }

    public function recurrences()
    {
        return $this->hasMany(Recurrence::class);
    }
}
