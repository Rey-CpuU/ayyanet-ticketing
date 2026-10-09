<?php

namespace App\Http\Controllers;

use App\Mail\UserInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::withCount('createdTickets')->latest()->get();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(User::ROLES)],
        ]);

        $temporaryPassword = Str::password(12);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => Hash::make($temporaryPassword),
            'email_verified_at' => now(),
        ]);

        Mail::to($user)->send(new UserInvitation($user, $temporaryPassword));

        return redirect()->route('users.index')
            ->with('success', "Akun {$user->name} dibuat — password sementara dikirim ke email.");
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
