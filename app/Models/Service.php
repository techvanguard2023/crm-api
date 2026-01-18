<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    public $timestamps = false; // No timestamps in migration

    protected $fillable = [
        'name',
        'description',
    ];

    public function customers()
    {
        return $this->belongsToMany(Customer::class)
                    ->using(CustomerService::class)
                    ->withPivot('price', 'recurrence')
                    ->withTimestamps();
    }
}
