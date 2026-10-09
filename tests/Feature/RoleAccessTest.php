<?php

use App\Models\User;

test('a user without a role is denied by the role middleware', function () {
    $user = User::factory()->create(['role' => 'cs']);
    // The column is NOT NULL, so simulate a missing role on the authenticated instance.
    $user->role = null;

    $this->actingAs($user)->get('/tickets')->assertForbidden();
    $this->actingAs($user)->get('/dashboard')->assertForbidden();
    $this->actingAs($user)->get('/settings')->assertForbidden();
});

test('a user with an unknown role is denied by the role middleware', function () {
    $user = User::factory()->create(['role' => 'cs']);
    $user->role = 'customer';

    $this->actingAs($user)->get('/tickets')->assertForbidden();
});

test('admin cannot change their own role', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->from('/settings')
        ->patch("/settings/users/{$admin->id}/role", ['role' => 'cs'])
        ->assertRedirect('/settings')
        ->assertSessionHasErrors('role');

    expect($admin->fresh()->role)->toBe('admin');

    $this->actingAs($admin)
        ->from("/users/{$admin->id}/edit")
        ->put("/users/{$admin->id}", ['name' => $admin->name, 'email' => $admin->email, 'role' => 'lapangan'])
        ->assertSessionHasErrors('role');

    expect($admin->fresh()->role)->toBe('admin');
});

test('admin can change another user role', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $cs = User::factory()->create(['role' => 'cs']);

    $this->actingAs($admin)
        ->patch("/settings/users/{$cs->id}/role", ['role' => 'lapangan'])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($cs->fresh()->role)->toBe('lapangan');
});

test('the last admin cannot be demoted', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $other = User::factory()->create(['role' => 'admin']);

    // Two admins: demoting the other one is fine.
    expect(User::roleChangeError($admin, $other, 'cs'))->toBeNull();

    $other->update(['role' => 'cs']);

    // Now only one admin is left; nobody may demote it.
    expect(User::roleChangeError($other, $admin, 'cs'))->toBe('Role admin terakhir tidak dapat diturunkan.');
});

test('cs cannot change roles', function () {
    $cs = User::factory()->create(['role' => 'cs']);

    $this->actingAs($cs)
        ->patch("/settings/users/{$cs->id}/role", ['role' => 'admin'])
        ->assertForbidden();

    expect($cs->fresh()->role)->toBe('cs');
});

test('security headers are sent without the deprecated XSS header', function () {
    $response = $this->get('/login');

    $response->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeaderMissing('X-XSS-Protection')
        ->assertHeaderMissing('Strict-Transport-Security');
});

test('HSTS is sent on secure requests', function () {
    $this->get('https://localhost/login')
        ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
});
