<?php

namespace App\Listeners;

use App\Mail\SuspiciousLoginAlert;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendLockoutAlert
{
    /**
     * Handle the event.
     */
    public function handle(Lockout $event): void
    {
        $email = $event->request->input('email');
        $ip = $event->request->ip();
        $time = now()->toDateTimeString();

        // Log only a hashed, non-reversible reference so the security event is
        // auditable without writing raw PII (email / IP) to the log files.
        Log::warning('SECURITY ALERT: Repeated failed login attempts causing lockout.', [
            'email_hash' => hash('sha256', $email ?? ''),
            'ip_hash'    => hash('sha256', $ip ?? ''),
            'time'       => $time,
        ]);

        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // If the user exists in database, send alert to that user
            $user = User::where('email', $email)->first();
            if ($user) {
                try {
                    Mail::to($email)->send(new SuspiciousLoginAlert($email, $ip, $time));
                } catch (\Throwable $e) {
                    // Avoid leaking driver config / stack traces into the log;
                    // report a generic class-only message instead.
                    report($e);
                    Log::error('Failed to send suspicious login alert email.', [
                        'exception' => get_class($e),
                        'email_hash' => hash('sha256', $email),
                    ]);
                }
            }
        }
    }
}
