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

    /**
     * Get masked phone number for data privacy (e.g. 0812****7890).
     */
    public function getMaskedPhoneAttribute(): string
    {
        if (empty($this->phone)) {
            return '—';
        }

        $phone = preg_replace('/[^\d+]/', '', $this->phone);
        $len = strlen($phone);

        if ($len <= 6) {
            return $this->phone;
        }

        $start = substr($phone, 0, 4);
        $end = substr($phone, -3);
        $maskedLen = max(3, $len - 7);

        return $start . str_repeat('*', $maskedLen) . $end;
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
