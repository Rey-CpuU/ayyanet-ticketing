<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\TicketMessageController;
use App\Http\Controllers\StatusBannerController;
use App\Http\Controllers\UserController;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::get('/dashboard', function (\Illuminate\Http\Request $request) {
    // Generate base stats independent of search/filter
    $stats = [
        'total'    => Ticket::count(),
        'open'     => Ticket::where('status', 'Open')->count(),
        'checking' => Ticket::where('status', 'Checking')->count(),
        'waiting'  => Ticket::where('status', 'Waiting Customer')->count(),
        'escalated' => Ticket::where('status', 'Escalated')->count(),
        'solved'   => Ticket::where('status', 'Solved')->count(),
        'closed'   => Ticket::where('status', 'Closed')->count(),
        'unassigned' => Ticket::whereNull('assigned_to')->count(),
        'mine'     => Ticket::where('assigned_to', auth()->id())->count(),
        'critical' => Ticket::where('impact', 'Critical')->count(),
    ];

    $query = Ticket::with(['customer', 'messages', 'assignee'])->latest();

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('ticket_number', 'like', "%{$search}%")
              ->orWhere('title', 'like', "%{$search}%");
        });
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('priority')) {
        $query->where('priority', $request->priority);
    }

    $tickets = $query->paginate(15)->withQueryString();

    $assignableUsers = User::whereNotNull('role')->orderBy('name')->get();
    $uniqueCustomers = Customer::orderBy('name')->get();

    return view('dashboard', compact('tickets', 'stats', 'assignableUsers', 'uniqueCustomers'));
})->middleware(['auth', 'verified'])->name('dashboard');

// --- TARUH DI SINI (LUAR AUTH) UNTUK SEMENTARA ---
Route::post('tickets/classify', [TicketController::class, 'classify'])->name('tickets.classify');
Route::resource('tickets', TicketController::class);
Route::resource('customers', CustomerController::class); // if need to manage customers
Route::get('status-banners/active', [StatusBannerController::class, 'active'])->name('status-banners.active');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('tickets/{ticket}/messages', [TicketMessageController::class, 'store'])->name('tickets.messages.store');
    Route::patch('tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.update-status');
    Route::patch('tickets/{ticket}/assignee', [TicketController::class, 'assign'])->name('tickets.assign');

    Route::get('/status-banners', [StatusBannerController::class, 'index'])->name('status-banners.index');
    Route::get('/status-banners/create', [StatusBannerController::class, 'create'])->name('status-banners.create');
    Route::post('/status-banners', [StatusBannerController::class, 'store'])->name('status-banners.store');
    Route::get('/status-banners/{statusBanner}/edit', [StatusBannerController::class, 'edit'])->name('status-banners.edit');
    Route::put('/status-banners/{statusBanner}', [StatusBannerController::class, 'update'])->name('status-banners.update');
    Route::delete('/status-banners/{statusBanner}', [StatusBannerController::class, 'destroy'])->name('status-banners.destroy');

    Route::middleware('admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        
        Route::post('/invitations', [\App\Http\Controllers\InvitationController::class, 'store'])->name('invitations.store');
        Route::post('/invitations/{invitation}/resend', [\App\Http\Controllers\InvitationController::class, 'resend'])->name('invitations.resend');
    });
});

Route::middleware('guest')->group(function () {
    Route::get('/register/invite/{token}', [\App\Http\Controllers\InvitationController::class, 'show'])->name('register.invite');
    Route::post('/register/invite/{token}', [\App\Http\Controllers\InvitationController::class, 'register']);
});

require __DIR__.'/auth.php';
