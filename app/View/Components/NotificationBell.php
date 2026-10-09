<?php

namespace App\View\Components;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use Illuminate\View\View;

class NotificationBell extends Component
{
    public int $unreadCount = 0;

    public Collection $notifications;

    public function __construct()
    {
        $user = Auth::user();

        $this->notifications = $user ? $user->notifications()->latest()->limit(10)->get() : collect();
        $this->unreadCount = $user ? $user->unreadNotifications()->count() : 0;
    }

    public function shouldRender(): bool
    {
        return Auth::check();
    }

    public function render(): View
    {
        return view('components.notification-bell');
    }
}
