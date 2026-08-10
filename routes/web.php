<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\CustomerController; // Ditambah jika ada CustomerController
use App\Http\Controllers\TicketMessageController;
use App\Http\Controllers\StatusBannerController;
use App\Http\Controllers\UserController;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $tickets = Ticket::with(['customer', 'messages', 'assignee'])->latest()->get();

    $stats = [
        'total'    => $tickets->count(),
        'open'     => $tickets->where('status', 'Open')->count(),
        'checking' => $tickets->where('status', 'Checking')->count(),
        'waiting'  => $tickets->where('status', 'Waiting Customer')->count(),
        'escalated' => $tickets->where('status', 'Escalated')->count(),
        'solved'   => $tickets->where('status', 'Solved')->count(),
        'closed'   => $tickets->where('status', 'Closed')->count(),
        'unassigned' => $tickets->where('assigned_to', null)->count(),
        'mine'     => $tickets->where('assigned_to', auth()->id())->count(),
        'critical' => $tickets->where('impact', 'Critical')->count(),
    ];

    $assignableUsers = User::whereNotNull('role')->orderBy('name')->get();

    return view('dashboard', compact('tickets', 'stats', 'assignableUsers'));
})->middleware(['auth', 'verified'])->name('dashboard');

// --- TARUH DI SINI (LUAR AUTH) UNTUK SEMENTARA ---
Route::post('tickets/classify', [TicketController::class, 'classify'])->name('tickets.classify');
Route::resource('tickets', TicketController::class);
Route::resource('customers', CustomerController::class); // jika butuh kelola customer
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
    });
});


require __DIR__.'/auth.php';
