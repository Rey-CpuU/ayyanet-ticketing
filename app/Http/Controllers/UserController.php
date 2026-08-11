<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    private const ROLES = ['admin', 'cs', 'lapangan'];

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

    // store is now handled by InvitationController

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role'  => ['required', 'in:' . implode(',', self::ROLES)],
        ]);

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
