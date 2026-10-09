<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StatusBannerController;
use App\Http\Controllers\TelegramWebhookController;
use App\Http\Controllers\TicketAssignmentController;
use App\Http\Controllers\TicketAuditLogController;
use App\Http\Controllers\TicketClassifyController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketExportController;
use App\Http\Controllers\TicketMessageController;
use App\Http\Controllers\TicketStatusController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;

// Telegram bot updates. Authenticated by the X-Telegram-Bot-Api-Secret-Token header (TELEGRAM_WEBHOOK_SECRET),
// fail-closed outside local/testing. The web group uses PreventRequestForgery (Laravel 13), so exclude that class.
Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle'])
    ->withoutMiddleware([PreventRequestForgery::class])
    ->middleware('throttle:120,1')
    ->name('telegram.webhook');

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// Invitation-based sign-up (public registration is closed).
Route::middleware('guest')->group(function () {
    Route::get('/register/invite/{token}', [InvitationController::class, 'show'])->name('register.invite');
    Route::post('/register/invite/{token}', [InvitationController::class, 'register'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->middleware('role:admin,cs,lapangan')->name('dashboard');

    Route::middleware('role:admin,cs')->group(function () {
        Route::get('/reports', ReportsController::class)->name('reports.index');
        Route::get('/settings', SettingsController::class)->name('settings.index');
        Route::resource('status-banners', StatusBannerController::class)->except('show');
    });

    // Admin-only: account management (public registration is closed) and destructive actions.
    Route::middleware('role:admin')->group(function () {
        Route::patch('/settings/users/{user}/role', [SettingsController::class, 'updateRole'])->name('settings.update-role');
        Route::resource('users', UserController::class)->except('show');
        Route::post('/invitations/{invitation}/resend', [InvitationController::class, 'resend'])->name('invitations.resend');
        Route::patch('/tickets/{ticket}/restore', [TicketController::class, 'restore'])->withTrashed()->name('tickets.restore');
        Route::delete('/tickets/{ticket}/force-delete', [TicketController::class, 'forceDelete'])->withTrashed()->name('tickets.force-delete');
        Route::patch('/customers/{customer}/restore', [CustomerController::class, 'restore'])->withTrashed()->name('customers.restore');
        Route::delete('/customers/{customer}/force-delete', [CustomerController::class, 'forceDelete'])->withTrashed()->name('customers.force-delete');
    });

    Route::middleware('role:admin,cs,lapangan')->group(function () {
        Route::get('/status-banners/active', [StatusBannerController::class, 'active'])->name('status-banners.active');
        Route::patch('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
        Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');

        Route::get('/my-tickets', [TicketController::class, 'myTickets'])->name('my.tickets');

        // Export routes must be registered before the resource, otherwise GET tickets/{ticket} swallows them.
        Route::middleware('throttle:exports')->group(function () {
            Route::get('/tickets/export/csv', [TicketExportController::class, 'exportCsv'])->name('tickets.export.csv');
            Route::get('/tickets/export/pdf', [TicketExportController::class, 'exportPdf'])->name('tickets.export.pdf');
        });
        Route::post('/tickets/classify', TicketClassifyController::class)
            ->middleware('throttle:ticket-classify')
            ->name('tickets.classify');
        // Dashboard live search (debounced client-side); also registered before the resource.
        Route::get('/tickets/live-search', [TicketController::class, 'liveSearch'])
            ->middleware('throttle:ticket-classify')
            ->name('tickets.live-search');
        Route::resource('tickets', TicketController::class)
            ->middlewareFor('store', 'throttle:ticket-writes');
        Route::get('/tickets/{ticket}/attachment', [TicketController::class, 'downloadAttachment'])->name('tickets.attachment');
        Route::patch('/tickets/{ticket}/assignee', TicketAssignmentController::class)->name('tickets.assignee.update');
        Route::patch('/tickets/{ticket}/status', TicketStatusController::class)->name('tickets.status.update');
        Route::get('/tickets/{ticket}/audit-log', [TicketAuditLogController::class, 'index'])->name('tickets.audit-log');
        // Quick view modal + quick chat popover on the dashboard (JSON).
        Route::get('/tickets/{ticket}/quick-details', [TicketController::class, 'quickDetails'])->name('tickets.quick-details');
        Route::post('/tickets/{ticket}/quick-message', [TicketMessageController::class, 'quickStore'])
            ->middleware('throttle:ticket-messages')
            ->name('tickets.quick-message');
        Route::post('tickets/{ticket}/messages', [TicketMessageController::class, 'store'])
            ->middleware('throttle:ticket-messages')
            ->name('tickets.messages.store');
        // Must be registered before the resource, otherwise GET customers/{customer} swallows it.
        Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search');
        Route::resource('customers', CustomerController::class);
    });

    Route::middleware('role:admin,cs,lapangan')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
});

require __DIR__.'/auth.php';
