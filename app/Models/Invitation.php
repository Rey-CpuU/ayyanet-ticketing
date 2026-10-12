<?php

namespace App\Models;

use App\Mail\UserInvitation;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class Invitation extends Model
{
    /**
     * How long an invitation link stays valid. The email text reads this too.
     */
    public const EXPIRY_HOURS = 24;

    protected $fillable = [
        'email',
        'role',
        'token',
        'expires_at',
        'accepted_at',
        'created_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Send the invitation email. It is sent synchronously on purpose: the link carries a sign-up
     * token, which must not be written to the jobs table. Returns false when the mail could not be
     * sent, so the caller can tell the admin instead of failing the request.
     */
    public function sendMail(): bool
    {
        try {
            Mail::to($this->email)->send(new UserInvitation($this));

            return true;
        } catch (Throwable $e) {
            report($e);
            Log::error('Failed to send user invitation email.', [
                'invitation_id' => $this->id,
                'exception' => get_class($e),
            ]);

            return false;
        }
    }

    protected function email(): Attribute
    {
        return Attribute::make(set: fn (?string $value) => User::normalizeEmail($value));
    }
}
