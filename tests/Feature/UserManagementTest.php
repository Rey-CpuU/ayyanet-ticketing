<?php

use App\Mail\UserInvitation;
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

test('admin creates a user and sends invitation email with temporary password', function () {
    Mail::fake();

    $this->actingAs($this->admin)->post('/users', [
        'name'  => 'Sari Ningsih',
        'email' => 'sari@example.com',
        'role'  => 'cs',
    ])->assertRedirect('/users');

    $this->assertDatabaseHas('users', [
        'name'  => 'Sari Ningsih',
        'email' => 'sari@example.com',
        'role'  => 'cs',
    ]);

    Mail::assertSent(UserInvitation::class, function (UserInvitation $mail) {
        return $mail->hasTo('sari@example.com') && ! empty($mail->temporaryPassword);
    });

    // The stored password is hashed, not the plaintext temporary password.
    $plain = Mail::sent(UserInvitation::class)->first()->temporaryPassword;
    $user = User::where('email', 'sari@example.com')->first();
    expect($user->password)->not->toBe($plain)
        ->and(Hash::check($plain, $user->password))->toBeTrue();
});

test('invited user can log in with the temporary password', function () {
    Mail::fake();

    $this->actingAs($this->admin)->post('/users', [
        'name'  => 'Rina',
        'email' => 'rina@example.com',
        'role'  => 'lapangan',
    ]);

    Mail::assertSent(UserInvitation::class, function (UserInvitation $mail) {
        return $mail->hasTo('rina@example.com');
    });

    $plain = Mail::sent(UserInvitation::class)->first()->temporaryPassword;

    $this->post('/login', [
        'email'    => 'rina@example.com',
        'password' => $plain,
    ])->assertRedirect(route('dashboard'));
});

test('duplicate email is rejected', function () {
    $this->actingAs($this->admin)->post('/users', [
        'name'  => 'Dup',
        'email' => $this->cs->email,
        'role'  => 'cs',
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
        'name'  => $this->cs->name,
        'email' => $this->cs->email,
        'role'  => 'lapangan',
    ])->assertRedirect('/users');

    expect($this->cs->fresh()->role)->toBe('lapangan');
});

test('admin cannot delete own account', function () {
    $this->actingAs($this->admin)->from('/users')
        ->delete("/users/{$this->admin->id}")
        ->assertRedirect('/users');

    expect(User::find($this->admin->id))->not->toBeNull();
});
