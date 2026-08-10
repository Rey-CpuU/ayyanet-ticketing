<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'ticket_number',
        'customer_id',
        'created_by',
        'assigned_to',
        'title',
        'description',
        'category',
        'olt',
        'location',
        'priority',
        'impact',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'assigned_to' => 'integer',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    public function messages()
{
    return $this->hasMany(TicketMessage::class);
}
public function activities()
{
    return $this->hasMany(TicketActivity::class);
}
}
