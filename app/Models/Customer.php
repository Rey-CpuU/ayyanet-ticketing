<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'address',
        'package',
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
