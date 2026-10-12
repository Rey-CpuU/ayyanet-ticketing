<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        // Active accounts first; deactivated ones stay listed so they can be reactivated.
        $users = User::withCount('createdTickets')
            ->orderByRaw('deactivated_at IS NOT NULL')
            ->latest()
            ->get();
        $invitations = Invitation::whereNull('accepted_at')->latest()->get();

        return view('users.index', compact('users', 'invitations'));
    }

    public function create()
    {
        return view('users.create');
    }

    /**
     * Public registration is closed: an admin invites a new account by email. The invitee picks
     * a name and password through the signed invitation link (see InvitationController).
     */
    public function store(Request $request)
    {
        $request->merge(['email' => User::normalizeEmail($request->input('email'))]);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', User::uniqueEmailRule()],
            'role' => ['required', Rule::in(User::ROLES)],
        ]);

        // One invitation row per email: re-inviting refreshes the token instead of failing on the unique key.
        $invitation = Invitation::updateOrCreate(
            ['email' => $data['email']],
            [
                'role' => $data['role'],
                'token' => Str::random(64),
                'expires_at' => now()->addHours(Invitation::EXPIRY_HOURS),
                'accepted_at' => null,
                'created_by' => $request->user()->id,
            ],
        );

        if (! $invitation->sendMail()) {
            return redirect()->route('users.index')
                ->with('error', "Undangan untuk {$invitation->email} sudah dibuat, tetapi email gagal dikirim. Silakan coba kirim ulang beberapa saat lagi.");
        }

        return redirect()->route('users.index')
            ->with('success', "Undangan dikirim ke {$invitation->email}.");
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->merge(['email' => User::normalizeEmail($request->input('email'))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', User::uniqueEmailRule($user->id)],
            'role' => ['required', Rule::in(User::ROLES)],
        ]);

        if ($error = User::roleChangeError($request->user(), $user, $data['role'])) {
            return back()->withErrors(['role' => $error])->withInput();
        }

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'Akun berhasil diperbarui.');
    }

    /**
     * Accounts are never deleted (tickets, messages and activities keep referring to them);
     * a deactivated account can no longer sign in and is hidden from assignee pickers.
     */
    public function deactivate(Request $request, User $user)
    {
        if ($user->isDeactivated()) {
            return back()->with('error', "Akun {$user->name} sudah nonaktif.");
        }

        if ($error = User::deactivationError($request->user(), $user)) {
            return back()->with('error', $error);
        }

        $user->forceFill([
            'deactivated_at' => now(),
            // Invalidate "remember me" cookies; open sessions are rejected by EnsureUserIsActive.
            'remember_token' => null,
        ])->save();

        return redirect()->route('users.index')
            ->with('success', "Akun {$user->name} dinonaktifkan.");
    }

    public function reactivate(User $user)
    {
        if ($user->isActive()) {
            return back()->with('error', "Akun {$user->name} sudah aktif.");
        }

        $user->forceFill(['deactivated_at' => null])->save();

        return redirect()->route('users.index')
            ->with('success', "Akun {$user->name} diaktifkan kembali.");
    }
}
