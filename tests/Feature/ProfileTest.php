<?php

use App\Models\Ticket;
use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('profile page no longer offers self deletion and points to the admin', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/profile')
        ->assertOk()
        ->assertSee('hubungi admin untuk menonaktifkannya', false)
        ->assertDontSee('confirm-user-deletion');
});

test('self deletion is refused with a friendly message, even for accounts with history', function () {
    $user = User::factory()->create();
    Ticket::factory()->create(['created_by' => $user->id]);

    $this->actingAs($user)
        ->delete('/profile', ['password' => 'password'])
        ->assertRedirect('/profile')
        ->assertSessionHas('error', 'Akun tidak dapat dihapus sendiri. Hubungi admin untuk menonaktifkan akun Anda.');

    $this->assertAuthenticatedAs($user);
    expect($user->fresh())->not->toBeNull()
        ->and($user->fresh()->deactivated_at)->toBeNull();
});

test('profile email is stored lowercase and checked for duplicates ignoring case', function () {
    $other = User::factory()->create(['email' => 'budi@example.com']);
    $user = User::factory()->create();

    $this->actingAs($user)->from('/profile')
        ->patch('/profile', ['name' => 'X', 'email' => 'BUDI@Example.com'])
        ->assertSessionHasErrors('email');

    $this->actingAs($user)
        ->patch('/profile', ['name' => 'X', 'email' => 'Baru@Example.COM'])
        ->assertSessionHasNoErrors();

    expect($user->fresh()->email)->toBe('baru@example.com')
        ->and($other->fresh()->email)->toBe('budi@example.com');
});
