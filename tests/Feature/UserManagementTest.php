<?php

use App\Mail\UserInvitation;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->cs = User::factory()->create(['role' => 'cs']);
});

test('admin can list users', function () {
    $this->actingAs($this->admin)->get('/users')
        ->assertOk()
        ->assertSee($this->cs->name);
});

test('admin invites a user by email instead of creating the account directly', function () {
    Mail::fake();

    $this->actingAs($this->admin)->post('/users', [
        'email' => 'sari@example.com',
        'role' => 'cs',
    ])->assertRedirect('/users');

    // No account exists until the invitee completes the sign-up link.
    $this->assertDatabaseMissing('users', ['email' => 'sari@example.com']);
    $this->assertDatabaseHas('invitations', [
        'email' => 'sari@example.com',
        'role' => 'cs',
        'created_by' => $this->admin->id,
    ]);

    Mail::assertSent(UserInvitation::class, function (UserInvitation $mail) {
        return $mail->hasTo('sari@example.com')
            && str_contains($mail->inviteUrl, '/register/invite/'.$mail->invitation->token);
    });
});

test('invited user can register through the link and then log in', function () {
    Mail::fake();

    $this->actingAs($this->admin)->post('/users', [
        'email' => 'rina@example.com',
        'role' => 'lapangan',
    ]);
    $this->post('/logout');

    $invitation = Invitation::where('email', 'rina@example.com')->firstOrFail();

    $this->get("/register/invite/{$invitation->token}")->assertOk()->assertSee('rina@example.com');

    $this->post("/register/invite/{$invitation->token}", [
        'name' => 'Rina',
        'password' => 'Rahasia#2026',
        'password_confirmation' => 'Rahasia#2026',
    ])->assertRedirect(route('login'));

    $user = User::where('email', 'rina@example.com')->firstOrFail();
    expect($user->role)->toBe('lapangan')
        ->and($user->email_verified_at)->not->toBeNull()
        ->and(Hash::check('Rahasia#2026', $user->password))->toBeTrue()
        ->and($invitation->fresh()->accepted_at)->not->toBeNull();

    // The link is single-use.
    $this->get("/register/invite/{$invitation->token}")->assertSee('Undangan Tidak Valid');

    $this->post('/login', [
        'email' => 'rina@example.com',
        'password' => 'Rahasia#2026',
    ])->assertRedirect(route('dashboard'));
});

test('expired invitations cannot be used', function () {
    $invitation = Invitation::create([
        'email' => 'late@example.com',
        'role' => 'cs',
        'token' => str_repeat('a', 64),
        'expires_at' => now()->subMinute(),
        'created_by' => $this->admin->id,
    ]);

    $this->post("/register/invite/{$invitation->token}", [
        'name' => 'Late',
        'password' => 'Rahasia#2026',
        'password_confirmation' => 'Rahasia#2026',
    ])->assertSessionHasErrors('token');

    $this->assertDatabaseMissing('users', ['email' => 'late@example.com']);
});

test('re-inviting the same email refreshes the pending invitation', function () {
    Mail::fake();

    $this->actingAs($this->admin)->post('/users', ['email' => 'dua@example.com', 'role' => 'cs']);
    $firstToken = Invitation::where('email', 'dua@example.com')->value('token');

    $this->actingAs($this->admin)->post('/users', ['email' => 'dua@example.com', 'role' => 'admin'])
        ->assertRedirect('/users');

    expect(Invitation::where('email', 'dua@example.com')->count())->toBe(1);
    $invitation = Invitation::where('email', 'dua@example.com')->first();
    expect($invitation->token)->not->toBe($firstToken)
        ->and($invitation->role)->toBe('admin');
});

test('duplicate email is rejected', function () {
    $this->actingAs($this->admin)->post('/users', [
        'email' => $this->cs->email,
        'role' => 'cs',
    ])->assertSessionHasErrors('email');
});

test('non-admin is forbidden from user management', function () {
    $this->actingAs($this->cs)->get('/users')->assertForbidden();
    $this->actingAs($this->cs)->post('/users', [
        'name' => 'x', 'email' => 'x@example.com', 'role' => 'cs',
    ])->assertForbidden();
});

test('guest is redirected to login', function () {
    $this->get('/users')->assertRedirect('/login');
});

test('admin can edit a user role', function () {
    $this->actingAs($this->admin)->put("/users/{$this->cs->id}", [
        'name' => $this->cs->name,
        'email' => $this->cs->email,
        'role' => 'lapangan',
    ])->assertRedirect('/users');

    expect($this->cs->fresh()->role)->toBe('lapangan');
});

test('admin cannot delete own account', function () {
    $this->actingAs($this->admin)->from('/users')
        ->delete("/users/{$this->admin->id}")
        ->assertRedirect('/users');

    expect(User::find($this->admin->id))->not->toBeNull();
});
