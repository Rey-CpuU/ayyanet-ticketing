<?php

use App\Http\Requests\Auth\LoginRequest;
use App\Mail\SuspiciousLoginAlert;
use App\Mail\UserInvitation;
use App\Models\Invitation;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
});

// ── 1. Deactivate instead of delete ──────────────────────────────────────────

test('admin deactivates a user with ticket history without errors', function () {
    $agent = User::factory()->create(['role' => 'lapangan', 'name' => 'Teknisi Lama']);
    $ticket = Ticket::factory()->assignedTo($agent)->create(['created_by' => $agent->id]);
    TicketMessage::factory()->create(['ticket_id' => $ticket->id, 'user_id' => $agent->id, 'message' => 'Sudah dicek']);
    TicketActivity::create([
        'ticket_id' => $ticket->id,
        'user_id' => $agent->id,
        'action' => 'internal_note',
        'new_value' => 'Catatan teknisi',
    ]);

    $this->actingAs($this->admin)
        ->patch("/users/{$agent->id}/deactivate")
        ->assertRedirect('/users')
        ->assertSessionHas('success');

    $agent->refresh();
    expect($agent->deactivated_at)->not->toBeNull()
        ->and($agent->remember_token)->toBeNull()
        ->and(User::find($agent->id))->not->toBeNull();

    // Old tickets still show the deactivated account by name.
    $this->actingAs($this->admin)->get("/tickets/{$ticket->id}")
        ->assertOk()
        ->assertSee('Teknisi Lama')
        ->assertSee('Teknisi Lama (nonaktif)');

    $this->actingAs($this->admin)->get('/users')
        ->assertOk()
        ->assertSee('Nonaktif')
        ->assertSee('Aktifkan kembali');
});

test('the last active admin cannot be deactivated or demoted', function () {
    $other = User::factory()->create(['role' => 'admin']);
    $other->forceFill(['deactivated_at' => now()])->save();

    // $this->admin is now the only active admin.
    expect(User::deactivationError($other, $this->admin))->toBe('Admin aktif terakhir tidak dapat dinonaktifkan.')
        ->and(User::roleChangeError($other, $this->admin, 'cs'))->toBe('Role admin terakhir tidak dapat diturunkan.');

    // With a second active admin both are allowed again.
    $other->forceFill(['deactivated_at' => null])->save();
    expect(User::deactivationError($other, $this->admin))->toBeNull();
});

test('admin can deactivate another admin while one active admin remains', function () {
    $other = User::factory()->create(['role' => 'admin']);

    $this->actingAs($this->admin)->patch("/users/{$other->id}/deactivate")->assertRedirect('/users');

    expect($other->fresh()->isDeactivated())->toBeTrue();
});

test('a deactivated user cannot log in, but a wrong password still gets the generic message', function () {
    $user = User::factory()->deactivated()->create(['email' => 'nonaktif@example.com']);

    $this->post('/login', ['email' => 'nonaktif@example.com', 'password' => 'wrong'])
        ->assertSessionHasErrors(['email' => 'Email atau kata sandi salah.']);

    $this->post('/login', ['email' => 'nonaktif@example.com', 'password' => 'password'])
        ->assertSessionHasErrors(['email' => LoginRequest::DEACTIVATED_MESSAGE]);

    $this->assertGuest();
});

test('an open session of a deactivated user is rejected', function () {
    $user = User::factory()->create(['role' => 'cs']);
    $this->actingAs($user)->get('/dashboard')->assertOk();

    $user->forceFill(['deactivated_at' => now()])->save();

    $this->actingAs($user->fresh())->get('/dashboard')
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors(['email' => LoginRequest::DEACTIVATED_MESSAGE]);

    $this->assertGuest();
});

test('reactivated users can log in again', function () {
    $user = User::factory()->deactivated()->create(['email' => 'kembali@example.com']);

    $this->actingAs($this->admin)->patch("/users/{$user->id}/reactivate")->assertRedirect('/users');
    expect($user->fresh()->deactivated_at)->toBeNull();

    $this->post('/logout');
    $this->post('/login', ['email' => 'kembali@example.com', 'password' => 'password'])
        ->assertRedirect(route('dashboard'));
});

test('deactivated users are excluded from assignee pickers and cannot be assigned', function () {
    $active = User::factory()->create(['role' => 'cs', 'name' => 'Agen Aktif']);
    $inactive = User::factory()->deactivated()->create(['role' => 'cs', 'name' => 'Agen Nonaktif']);
    $ticket = Ticket::factory()->create(['created_by' => $this->admin->id]);

    $this->actingAs($this->admin)->get("/tickets/{$ticket->id}")
        ->assertOk()
        ->assertSee('Agen Aktif')
        ->assertDontSee('Agen Nonaktif');

    $this->actingAs($this->admin)->get('/dashboard')
        ->assertOk()
        ->assertDontSee('Agen Nonaktif');

    $this->actingAs($this->admin)
        ->patch("/tickets/{$ticket->id}/assignee", ['assigned_to' => $inactive->id])
        ->assertSessionHasErrors('assigned_to');

    expect($ticket->fresh()->assigned_to)->toBeNull();
});

test('resending an invitation for a deactivated account is refused', function () {
    Mail::fake();
    $user = User::factory()->deactivated()->create(['email' => 'lama@example.com']);
    $invitation = Invitation::create([
        'email' => 'lama@example.com',
        'role' => 'cs',
        'token' => str_repeat('b', 64),
        'expires_at' => now()->addHour(),
        'created_by' => $this->admin->id,
    ]);

    $this->actingAs($this->admin)->from('/users')
        ->post("/invitations/{$invitation->id}/resend")
        ->assertRedirect('/users')
        ->assertSessionHas('error');

    Mail::assertNothingSent();
    expect($invitation->fresh()->token)->toBe(str_repeat('b', 64));
});

// ── 2 + 17. Invitation link and expiry text ──────────────────────────────────

test('invitation link uses the named route and the email states the real expiry', function () {
    $invitation = Invitation::create([
        'email' => 'baru@example.com',
        'role' => 'cs',
        'token' => str_repeat('c', 64),
        'expires_at' => now()->addHours(Invitation::EXPIRY_HOURS),
        'created_by' => $this->admin->id,
    ]);

    $mail = new UserInvitation($invitation);

    expect($mail->inviteUrl())->toBe(route('register.invite', $invitation->token))
        ->and($mail->inviteUrl())->toStartWith(rtrim(config('app.url'), '/'))
        ->and($mail->inviteUrl())->not->toContain('vercel.app');

    $mail->assertSeeInHtml(route('register.invite', $invitation->token), false);
    $mail->assertSeeInText('berlaku selama '.Invitation::EXPIRY_HOURS.' jam');
});

test('new invitations expire after the configured number of hours', function () {
    Mail::fake();

    $this->actingAs($this->admin)->post('/users', ['email' => 'jam@example.com', 'role' => 'cs']);

    $invitation = Invitation::where('email', 'jam@example.com')->firstOrFail();
    expect((int) round(now()->diffInHours($invitation->expires_at)))->toBe(Invitation::EXPIRY_HOURS);
});

// ── 8. Lockout alert mail-bomb ───────────────────────────────────────────────

test('only one lockout alert is queued per throttle window', function () {
    Mail::fake();
    User::factory()->create(['email' => 'target@example.com']);

    for ($i = 0; $i < 10; $i++) {
        $this->post('/login', ['email' => 'target@example.com', 'password' => 'salah']);
    }

    Mail::assertQueued(SuspiciousLoginAlert::class, 1);
    Mail::assertNotSent(SuspiciousLoginAlert::class);

    // A new lockout after the window may alert again.
    $this->travel(LoginRequest::DECAY_SECONDS + 1)->seconds();
    for ($i = 0; $i < 6; $i++) {
        $this->post('/login', ['email' => 'TARGET@example.com', 'password' => 'salah']);
    }

    Mail::assertQueued(SuspiciousLoginAlert::class, 2);
});

test('lockout for an unknown email queues no alert', function () {
    Mail::fake();

    for ($i = 0; $i < 7; $i++) {
        $this->post('/login', ['email' => 'nobody@example.com', 'password' => 'salah']);
    }

    Mail::assertNothingOutgoing();
});

// ── 10. Email case ───────────────────────────────────────────────────────────

test('invitations are stored lowercase and duplicates are detected ignoring case', function () {
    Mail::fake();
    User::factory()->create(['email' => 'ada@example.com']);

    $this->actingAs($this->admin)->post('/users', ['email' => 'ADA@Example.com', 'role' => 'cs'])
        ->assertSessionHasErrors('email');

    $this->actingAs($this->admin)->post('/users', ['email' => 'Sari@Example.COM', 'role' => 'cs'])
        ->assertRedirect('/users');

    $this->assertDatabaseHas('invitations', ['email' => 'sari@example.com']);
    Mail::assertSent(UserInvitation::class, fn (UserInvitation $mail) => $mail->hasTo('sari@example.com'));
});

test('accepting a legacy mixed-case invitation creates a lowercase account', function () {
    DB::table('invitations')->insert([
        'email' => 'Legacy@Example.com',
        'role' => 'cs',
        'token' => str_repeat('d', 64),
        'expires_at' => now()->addHour(),
        'created_by' => $this->admin->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->post('/register/invite/'.str_repeat('d', 64), [
        'name' => 'Legacy',
        'password' => 'Rahasia#2026',
        'password_confirmation' => 'Rahasia#2026',
    ])->assertRedirect(route('login'));

    $this->assertDatabaseHas('users', ['email' => 'legacy@example.com']);
});

test('accepting an invitation is refused when the email exists with different case', function () {
    User::factory()->create(['email' => 'kembar@example.com']);
    DB::table('invitations')->insert([
        'email' => 'Kembar@Example.com',
        'role' => 'cs',
        'token' => str_repeat('e', 64),
        'expires_at' => now()->addHour(),
        'created_by' => $this->admin->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->post('/register/invite/'.str_repeat('e', 64), [
        'name' => 'Kembar',
        'password' => 'Rahasia#2026',
        'password_confirmation' => 'Rahasia#2026',
    ])->assertSessionHasErrors('token');

    expect(User::whereRaw('LOWER(email) = ?', ['kembar@example.com'])->count())->toBe(1);
});

test('login is case-insensitive on the email', function () {
    User::factory()->create(['email' => 'rina@example.com']);

    $this->post('/login', ['email' => '  RINA@Example.com ', 'password' => 'password'])
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticated();
});

test('admin edit normalises email and rejects case-insensitive duplicates', function () {
    $cs = User::factory()->create(['role' => 'cs', 'email' => 'cs@example.com']);
    User::factory()->create(['email' => 'lain@example.com']);

    $this->actingAs($this->admin)->put("/users/{$cs->id}", [
        'name' => $cs->name, 'email' => 'LAIN@example.com', 'role' => 'cs',
    ])->assertSessionHasErrors('email');

    $this->actingAs($this->admin)->put("/users/{$cs->id}", [
        'name' => $cs->name, 'email' => 'CS.Baru@Example.com', 'role' => 'cs',
    ])->assertRedirect('/users');

    expect($cs->fresh()->email)->toBe('cs.baru@example.com');
});

test('data migration lowercases emails and skips collisions', function () {
    DB::table('users')->insert([
        ['name' => 'A', 'email' => 'Mixed@Example.com', 'password' => 'x', 'role' => 'cs', 'created_at' => now(), 'updated_at' => now()],
        ['name' => 'B', 'email' => 'Dup@Example.com', 'password' => 'x', 'role' => 'cs', 'created_at' => now(), 'updated_at' => now()],
        ['name' => 'C', 'email' => 'dup@example.com', 'password' => 'x', 'role' => 'cs', 'created_at' => now(), 'updated_at' => now()],
    ]);
    Log::spy();

    $migration = require database_path('migrations/2026_10_09_300100_lowercase_user_and_invitation_emails.php');
    $migration->up();

    expect(DB::table('users')->where('name', 'A')->value('email'))->toBe('mixed@example.com')
        ->and(DB::table('users')->where('name', 'B')->value('email'))->toBe('Dup@Example.com')
        ->and(DB::table('users')->where('name', 'C')->value('email'))->toBe('dup@example.com');

    Log::shouldHaveReceived('warning')->once();
});

// ── 11. Invitation mail failures ─────────────────────────────────────────────

test('a failing invitation mail keeps the invitation and shows a friendly error', function () {
    Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP down'));

    $this->actingAs($this->admin)->post('/users', ['email' => 'gagal@example.com', 'role' => 'cs'])
        ->assertRedirect('/users')
        ->assertSessionHas('error', fn (string $message) => str_contains($message, 'email gagal dikirim'));

    $this->assertDatabaseHas('invitations', ['email' => 'gagal@example.com', 'accepted_at' => null]);
});

test('a failing invitation resend shows a friendly error instead of a 500', function () {
    $invitation = Invitation::create([
        'email' => 'ulang@example.com',
        'role' => 'cs',
        'token' => str_repeat('f', 64),
        'expires_at' => now()->addHour(),
        'created_by' => $this->admin->id,
    ]);
    Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP down'));

    $this->actingAs($this->admin)->from('/users')
        ->post("/invitations/{$invitation->id}/resend")
        ->assertRedirect('/users')
        ->assertSessionHas('error', 'Email undangan gagal dikirim. Silakan coba lagi beberapa saat lagi.');
});

test('invitation mail is sent synchronously so the sign-up token never reaches the jobs table', function () {
    Mail::fake();

    $this->actingAs($this->admin)->post('/users', ['email' => 'sinkron@example.com', 'role' => 'cs']);

    Mail::assertSent(UserInvitation::class);
    Mail::assertNotQueued(UserInvitation::class);
    expect(new UserInvitation(new Invitation))->not->toBeInstanceOf(ShouldQueue::class);
});
