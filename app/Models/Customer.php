<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'name',
        'email',
        'phone',
        'address',
        'package',
    ];

    /**
     * Customers created without an explicit customer_id (e.g. the web form) get the
     * standard "C-0001" number derived from the row id.
     */
    protected static function booted(): void
    {
        static::creating(function (Customer $customer) {
            if (blank($customer->customer_id)) {
                // Unique placeholder; replaced in the created hook once the row id is known.
                $customer->customer_id = 'TMP-'.Str::uuid();
            }
        });

        static::created(function (Customer $customer) {
            if (str_starts_with((string) $customer->customer_id, 'TMP-')) {
                $customer->customer_id = static::numberFor($customer->id);
                $customer->saveQuietly();
            }
        });
    }

    public static function numberFor(int $id): string
    {
        return 'C-'.str_pad((string) $id, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Phone number masked for display in shared channels (e.g. 0812****890).
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

        return substr($phone, 0, 4).str_repeat('*', max(3, $len - 7)).substr($phone, -3);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
