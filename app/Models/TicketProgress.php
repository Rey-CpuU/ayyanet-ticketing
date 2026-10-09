<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketProgress extends Model
{
    protected $fillable = [
        'ticket_id',
        'status',
        'note',
        'updated_by',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
