<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function __invoke(Request $request)
    {
        $users = User::all();
        $roles = [
            'admin' => 'Admin',
            'cs' => 'Customer Service',
            'lapangan' => 'Teknisi Lapangan',
        ];

        return view('settings.index', [
            'users' => $users,
            'roles' => $roles,
            'slaSettings' => [
                'high' => Config::get('ticketing.sla.high', '5h'),
                'medium' => Config::get('ticketing.sla.medium', '8h'),
                'low' => Config::get('ticketing.sla.low', '24h'),
            ],
            'channels' => [
                'email' => ['enabled' => true, 'label' => 'Email'],
                'live_chat' => ['enabled' => true, 'label' => 'Live Chat'],
                'whatsapp' => ['enabled' => true, 'label' => 'WhatsApp'],
                'web_form' => ['enabled' => false, 'label' => 'Web Form'],
                'portal' => ['enabled' => true, 'label' => 'Portal'],
            ],
        ]);
    }

    public function updateRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => ['required', 'string', Rule::in(User::ROLES)],
        ]);

        if ($error = User::roleChangeError($request->user(), $user, $validated['role'])) {
            return back()->withErrors(['role' => $error]);
        }

        $user->update(['role' => $validated['role']]);

        return back()->with('success', 'Role berhasil diperbarui.');
    }
}
