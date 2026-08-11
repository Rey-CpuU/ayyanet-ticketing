<?php

namespace App\Http\Controllers;

use App\Mail\UserInvitation;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class InvitationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'unique:users,email', 'unique:invitations,email'],
            'role'  => ['required', 'in:admin,cs,lapangan'],
        ]);

        $invitation = Invitation::create([
            'email'      => $request->email,
            'role'       => $request->role,
            'token'      => Str::random(32),
            'expires_at' => now()->addHours(5),
            'created_by' => Auth::id(),
        ]);

        Mail::to($invitation->email)->send(new UserInvitation($invitation));

        return back()->with('success', "Undangan dikirim ke {$invitation->email}");
    }

    public function resend(Invitation $invitation)
    {
        if ($invitation->accepted_at) {
            return back()->with('error', 'Undangan sudah diterima.');
        }

        $invitation->update([
            'token'      => Str::random(32),
            'expires_at' => now()->addHours(5),
        ]);

        Mail::to($invitation->email)->send(new UserInvitation($invitation));

        return back()->with('success', "Undangan dikirim ulang ke {$invitation->email}");
    }

    public function show(string $token)
    {
        $invitation = Invitation::where('token', $token)->first();

        if (! $invitation || $invitation->accepted_at || $invitation->expires_at->isPast()) {
            return view('auth.invite-invalid');
        }

        return view('auth.register-invite', compact('invitation'));
    }

    public function register(Request $request, string $token)
    {
        $invitation = Invitation::where('token', $token)->first();

        if (! $invitation || $invitation->accepted_at || $invitation->expires_at->isPast()) {
            return back()->withErrors(['token' => 'Link undangan tidak valid atau sudah kedaluwarsa.']);
        }

        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $invitation->email,
            'role'     => $invitation->role,
            'password' => Hash::make($request->password),
        ]);

        $invitation->update(['accepted_at' => now()]);

        Auth::login($user);

        return redirect('/dashboard')->with('success', 'Selamat datang! Akun berhasil dibuat.');
    }
}
