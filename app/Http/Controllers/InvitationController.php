<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

/**
 * Invitation-based onboarding. Admins create invitations from user management
 * (UserController::store); invitees complete their account through the emailed link.
 */
class InvitationController extends Controller
{
    public function resend(Invitation $invitation)
    {
        if ($invitation->accepted_at) {
            return back()->with('error', 'Undangan sudah diterima.');
        }

        // An account already exists for this address (possibly deactivated): never re-invite it.
        $existing = User::whereRaw('LOWER(email) = ?', [User::normalizeEmail($invitation->email)])->first();

        if ($existing) {
            return back()->with('error', $existing->isDeactivated()
                ? 'Akun dengan email ini sudah ada tetapi dinonaktifkan. Aktifkan kembali akun tersebut dari daftar pengguna.'
                : 'Akun dengan email ini sudah terdaftar.');
        }

        $invitation->update([
            'token' => Str::random(64),
            'expires_at' => now()->addHours(Invitation::EXPIRY_HOURS),
        ]);

        if (! $invitation->sendMail()) {
            return back()->with('error', 'Email undangan gagal dikirim. Silakan coba lagi beberapa saat lagi.');
        }

        return back()->with('success', 'Undangan dikirim ulang ke alamat email yang terdaftar.');
    }

    public function show(string $token)
    {
        $invitation = $this->findValid($token);

        if (! $invitation) {
            return view('auth.invite-invalid');
        }

        return view('auth.register-invite', compact('invitation'));
    }

    public function register(Request $request, string $token)
    {
        $invitation = $this->findValid($token);

        if (! $invitation) {
            return back()->withErrors(['token' => 'Link undangan tidak valid atau sudah kedaluwarsa.']);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (User::emailTaken($invitation->email)) {
            return back()->withErrors(['token' => 'Akun dengan email ini sudah terdaftar. Silakan masuk.']);
        }

        DB::transaction(function () use ($request, $invitation) {
            $user = User::create([
                'name' => $request->name,
                'email' => User::normalizeEmail($invitation->email),
                'role' => $invitation->role,
                'password' => Hash::make($request->password),
            ]);

            // Following the emailed link proves ownership of the address.
            $user->markEmailAsVerified();

            $invitation->update(['accepted_at' => now()]);
        });

        return redirect()->route('login')->with('status', 'Pendaftaran akun berhasil! Silakan masuk menggunakan email dan password Anda.');
    }

    private function findValid(string $token): ?Invitation
    {
        $invitation = Invitation::where('token', $token)->first();

        if (! $invitation || $invitation->accepted_at || $invitation->expires_at->isPast()) {
            return null;
        }

        return $invitation;
    }
}
