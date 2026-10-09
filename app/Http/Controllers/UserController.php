<?php

namespace App\Http\Controllers;

use App\Mail\UserInvitation;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::withCount('createdTickets')->latest()->get();
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
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(User::ROLES)],
        ]);

        // One invitation row per email: re-inviting refreshes the token instead of failing on the unique key.
        $invitation = Invitation::updateOrCreate(
            ['email' => $data['email']],
            [
                'role' => $data['role'],
                'token' => Str::random(64),
                'expires_at' => now()->addHours(24),
                'accepted_at' => null,
                'created_by' => $request->user()->id,
            ],
        );

        Mail::to($invitation->email)->send(new UserInvitation($invitation));

        return redirect()->route('users.index')
            ->with('success', "Undangan dikirim ke {$invitation->email}.");
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', Rule::in(User::ROLES)],
        ]);

        if ($error = User::roleChangeError($request->user(), $user, $data['role'])) {
            return back()->withErrors(['role' => $error])->withInput();
        }

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Akun dihapus.');
    }
}
