<?php

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

/*
| The system is internal: only staff (admin, cs, lapangan) sign in. Customers never log in.
*/

function staffCustomer(): Customer
{
    return Customer::create([
        'customer_id' => 'C-PRIV-1',
        'name' => 'Budi Santoso',
        'phone' => '081200001111',
        'address' => 'Jl. Melati 1',
        'package' => 'Home 20 Mbps',
    ]);
}

// --- Login page ---------------------------------------------------------------------------

test('login page is staff-only and has no public sign-up or customer wording', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee('Masuk')
        ->assertSee('Lupa kata sandi?')
        ->assertDontSee('Sign Up')
        ->assertDontSee('Daftar')
        ->assertDontSee('Register')
        ->assertDontSee('portal', false)
        ->assertDontSee('Portal')
        ->assertDontSee('pelanggan');
});

test('login is throttled per email and ip after five failed attempts', function () {
    $user = User::factory()->create(['email' => 'cs@ayyanet.test']);
    $other = User::factory()->create(['email' => 'teknisi@ayyanet.test']);

    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', ['email' => $user->email, 'password' => 'salah'])
            ->assertSessionHasErrors(['email' => 'Email atau kata sandi salah.']);
    }

    // Sixth attempt is locked out even with the correct password.
    $this->post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertSessionHasErrors('email');
    expect(session('errors')->first('email'))->toContain('Terlalu banyak percobaan masuk');
    $this->assertGuest();

    // The key is email+IP: another account from the same IP is not locked.
    $this->post('/login', ['email' => $other->email, 'password' => 'password']);
    $this->assertAuthenticatedAs($other);
});

// --- Channel categories -------------------------------------------------------------------

test('public channels web form and portal are no longer offered', function () {
    expect(Ticket::CATEGORIES)->toBe(['Email', 'WhatsApp', 'Live Chat']);

    $cs = User::factory()->create(['role' => 'cs']);

    $this->actingAs($cs)->get('/tickets/create')
        ->assertOk()
        ->assertDontSee('Web Form')
        ->assertDontSee('Portal');

    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)->get('/settings')
        ->assertOk()
        ->assertSee('Live Chat')
        ->assertDontSee('Web Form')
        ->assertDontSee('Portal');
});

test('new tickets cannot use the removed channels', function (string $channel) {
    $cs = User::factory()->create(['role' => 'cs']);
    $customer = staffCustomer();

    $this->actingAs($cs)->post('/tickets', [
        'customer_id' => $customer->id,
        'title' => 'Internet mati',
        'description' => 'Tidak ada koneksi',
        'priority' => 'High',
        'category' => $channel,
    ])->assertSessionHasErrors('category');

    expect(Ticket::count())->toBe(0);
})->with(['Web Form', 'Portal']);

test('legacy channel values still display and validate on edit', function () {
    $cs = User::factory()->create(['role' => 'cs']);
    $customer = staffCustomer();
    $ticket = Ticket::create([
        'ticket_number' => 'TKT-LEGACY-1',
        'customer_id' => $customer->id,
        'created_by' => $cs->id,
        'title' => 'Tiket lama',
        'description' => 'Dibuat lewat portal lama',
        'category' => 'Portal',
        'priority' => 'Medium',
        'status' => 'Open',
    ]);

    $this->actingAs($cs)->get("/tickets/{$ticket->id}/edit")
        ->assertOk()
        ->assertSee('Portal');

    $payload = [
        'customer_id' => $customer->id,
        'title' => 'Tiket lama (diperbarui)',
        'description' => $ticket->description,
        'priority' => 'Medium',
        'status' => 'Open',
        'category' => 'Portal',
    ];

    $this->actingAs($cs)->put("/tickets/{$ticket->id}", $payload)->assertSessionHasNoErrors();
    expect($ticket->fresh()->title)->toBe('Tiket lama (diperbarui)');

    // Switching to a different removed channel is still rejected.
    $this->actingAs($cs)->put("/tickets/{$ticket->id}", [...$payload, 'category' => 'Web Form'])
        ->assertSessionHasErrors('category');
    expect($ticket->fresh()->category)->toBe('Portal');
});

// --- Route audit --------------------------------------------------------------------------

test('every route except auth entry points and the telegram webhook requires auth and a staff role', function () {
    // Guest entry points (login / password reset / staff invitation), auth-session plumbing, and infra.
    $public = ['login', 'password.request', 'password.email', 'password.reset', 'password.store',
        'register.invite', 'telegram.webhook', 'home'];
    $authOnly = ['logout', 'verification.notice', 'verification.verify', 'verification.send', 'password.confirm'];
    $publicUris = ['up', 'login', 'register/invite/{token}', 'storage/{path}'];
    $authOnlyUris = ['confirm-password'];

    $violations = [];

    foreach (Route::getRoutes() as $route) {
        $name = $route->getName();
        $uri = $route->uri();

        if (in_array($name, $public, true) || in_array($uri, $publicUris, true)
            || str_starts_with($uri, '_') || str_starts_with($uri, 'sanctum/')) {
            continue;
        }

        $middleware = $route->gatherMiddleware();
        $hasAuth = in_array('auth', $middleware, true);
        $hasRole = collect($middleware)->contains(fn ($m) => is_string($m) && str_starts_with($m, 'role:'));

        if (in_array($name, $authOnly, true) || in_array($uri, $authOnlyUris, true)) {
            if (! $hasAuth) {
                $violations[] = $uri;
            }

            continue;
        }

        if (! $hasAuth || ! $hasRole) {
            $violations[] = implode('|', $route->methods()).' '.$uri;
        }
    }

    expect($violations)->toBe([]);
});

test('there is no public chat or customer endpoint', function () {
    foreach (Route::getRoutes() as $route) {
        $uri = $route->uri();
        if (preg_match('/chat|portal|web-?form|submit|public/i', $uri)) {
            expect($route->gatherMiddleware())->toContain('auth');
        }
    }

    $this->get('/portal')->assertNotFound();
    $this->get('/chat')->assertNotFound();
    $this->post('/live-chat')->assertNotFound();
});

// --- Telegram webhook ---------------------------------------------------------------------

function telegramUpdate(): array
{
    return ['update_id' => 1, 'message' => ['message_id' => 1, 'chat' => ['id' => 4242], 'text' => '/start']];
}

test('telegram webhook fails closed in production when the secret is empty', function () {
    Http::fake();
    config(['telegram.bot_token' => 'dummy', 'telegram.webhook_secret' => '']);
    $this->app->detectEnvironment(fn () => 'production');

    $this->postJson('/telegram/webhook', telegramUpdate())->assertUnauthorized();
    Http::assertNothingSent();
});

test('telegram webhook rejects a wrong or missing secret', function () {
    Http::fake();
    config(['telegram.bot_token' => 'dummy', 'telegram.webhook_secret' => 'rahasia-benar']);
    $this->app->detectEnvironment(fn () => 'production');

    $this->postJson('/telegram/webhook', telegramUpdate())->assertUnauthorized();
    $this->postJson('/telegram/webhook', telegramUpdate(), ['X-Telegram-Bot-Api-Secret-Token' => 'salah'])
        ->assertUnauthorized();
    Http::assertNothingSent();
});

test('telegram webhook accepts the correct secret in production', function () {
    Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => []])]);
    config(['telegram.bot_token' => 'dummy', 'telegram.webhook_secret' => 'rahasia-benar']);
    $this->app->detectEnvironment(fn () => 'production');

    $this->postJson('/telegram/webhook', telegramUpdate(), ['X-Telegram-Bot-Api-Secret-Token' => 'rahasia-benar'])
        ->assertOk()
        ->assertJson(['ok' => true]);
});

test('telegram webhook without a secret is still allowed in local and testing', function (string $env) {
    Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => []])]);
    config(['telegram.bot_token' => 'dummy', 'telegram.webhook_secret' => '']);
    $this->app->detectEnvironment(fn () => $env);

    $this->postJson('/telegram/webhook', telegramUpdate())->assertOk();
})->with(['local', 'testing']);
