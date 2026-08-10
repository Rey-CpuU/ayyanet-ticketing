<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'customer_id',
        'name',
        'phone',
        'address',
        'package',
    ];

    protected static function booted()
    {
        static::creating(function ($customer) {
            if (empty($customer->customer_id)) {
                $customer->customer_id = 'CUS-' . strtoupper(uniqid());
            }
        });
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
