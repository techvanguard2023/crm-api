<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
    ];

    public function domains()
    {
        return $this->hasMany(Domain::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class)
                    ->using(CustomerService::class)
                    ->withPivot('price', 'recurrence')
                    ->withTimestamps();
    }
}
