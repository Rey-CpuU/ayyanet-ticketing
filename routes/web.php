<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\TicketMessageController;
use App\Http\Controllers\StatusBannerController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TelegramWebhookController;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Support\Facades\Route;

Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::get('/dashboard', function (\Illuminate\Http\Request $request) {
    $userId = \Illuminate\Support\Facades\Auth::id();

    // 1 query single aggregate for dashboard statistics
    $rawStats = \Illuminate\Support\Facades\DB::table('tickets')
        ->selectRaw("
            COUNT(*) as total,
            COUNT(CASE WHEN status = 'Open' THEN 1 END) as open,
            COUNT(CASE WHEN status = 'Checking' THEN 1 END) as checking,
            COUNT(CASE WHEN status = 'Waiting Customer' THEN 1 END) as waiting,
            COUNT(CASE WHEN status = 'Escalated' THEN 1 END) as escalated,
            COUNT(CASE WHEN status = 'Solved' THEN 1 END) as solved,
            COUNT(CASE WHEN status = 'Closed' THEN 1 END) as closed,
            COUNT(CASE WHEN assigned_to IS NULL THEN 1 END) as unassigned,
            COUNT(CASE WHEN assigned_to = ? THEN 1 END) as mine,
            COUNT(CASE WHEN priority = 'High' THEN 1 END) as high
        ", [$userId])
        ->first();

    $stats = (array) $rawStats;

    // Streamlined eager loading ordered by most recently visited/clicked
    $query = Ticket::with([
        'customer:id,name,customer_id,phone,address',
        'assignee:id,name,email,role'
    ])->orderByRaw('COALESCE(last_visited_at, updated_at, created_at) DESC');

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

    // Dedicated query for the "Recently Visited" panel — global top 5 by
    // last_visited_at, independent of the paginated $tickets collection.
    $latestTickets = Ticket::with([
        'customer:id,name,customer_id',
        'assignee:id,name',
    ])
        ->orderByRaw('COALESCE(last_visited_at, created_at) DESC')
        ->limit(5)
        ->get();

    $assignableUsers = User::select('id', 'name', 'role')->whereNotNull('role')->orderBy('name')->get();
    $uniqueCustomers = Customer::select('id', 'name')->orderBy('name')->get();

    return view('dashboard', compact('tickets', 'stats', 'assignableUsers', 'uniqueCustomers', 'latestTickets'));
})->middleware(['auth', 'verified'])->name('dashboard');

// --- Live search & classify ---
Route::get('tickets/live-search', [TicketController::class, 'liveSearch'])->name('tickets.live-search');
Route::post('tickets/classify', [TicketController::class, 'classify'])->name('tickets.classify');
Route::resource('tickets', TicketController::class);
Route::resource('customers', CustomerController::class); // if need to manage customers
Route::get('status-banners/active', [StatusBannerController::class, 'active'])->name('status-banners.active');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('tickets/{ticket}/messages', [TicketMessageController::class, 'store'])->name('tickets.messages.store');
    Route::get('tickets/{ticket}/quick-details', [TicketController::class, 'quickDetails'])->name('tickets.quick-details');
    Route::post('tickets/{ticket}/quick-message', [TicketMessageController::class, 'quickStore'])->name('tickets.quick-message');
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
