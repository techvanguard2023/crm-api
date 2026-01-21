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
        'company_name',
        'email',
        'phone',
        'type',
        'document',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
    ];

    public function domains()
    {
        return $this->hasMany(Domain::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class)
                    ->using(CustomerService::class)
                    ->withPivot('id', 'price', 'recurrence', 'start_date', 'next_due_date')
                    ->withTimestamps();
    }
}
