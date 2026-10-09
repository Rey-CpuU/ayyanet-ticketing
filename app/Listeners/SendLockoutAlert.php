<?php

namespace App\Listeners;

use App\Http\Requests\Auth\LoginRequest;
use App\Mail\SuspiciousLoginAlert;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Runs synchronously (the Lockout event carries the request, which cannot be serialized),
 * but only does cheap work: the alert email itself is queued.
 */
class SendLockoutAlert
{
    /**
     * Handle the event.
     */
    public function handle(Lockout $event): void
    {
        $email = User::normalizeEmail($event->request->input('email'));
        $email = is_string($email) ? $email : '';
        $ip = $event->request->ip();

        // Lockout fires on every throttled attempt: alert (and log) only once per throttle
        // window per account, so an attacker cannot mail-bomb the owner of the address.
        if (! Cache::add('lockout-alert:'.hash('sha256', $email), true, LoginRequest::DECAY_SECONDS)) {
            return;
        }

        $time = now()->toDateTimeString();

        // Log only a hashed, non-reversible reference so the security event is
        // auditable without writing raw PII (email / IP) to the log files.
        Log::warning('SECURITY ALERT: Repeated failed login attempts causing lockout.', [
            'email_hash' => hash('sha256', $email),
            'ip_hash' => hash('sha256', $ip ?? ''),
            'time' => $time,
        ]);

        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || ! User::where('email', $email)->exists()) {
            return;
        }

        try {
            Mail::to($email)->queue(new SuspiciousLoginAlert($email, (string) $ip, $time));
        } catch (\Throwable $e) {
            // Avoid leaking driver config / stack traces into the log;
            // report a generic class-only message instead.
            report($e);
            Log::error('Failed to queue suspicious login alert email.', [
                'exception' => get_class($e),
                'email_hash' => hash('sha256', $email),
            ]);
        }
    }
}
