<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

test('public registration is closed', function () {
    expect(Route::has('register'))->toBeFalse();

    $this->get('/register')->assertNotFound();

    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertNotFound();

    $this->assertGuest();
    expect(User::where('email', 'test@example.com')->exists())->toBeFalse();
});

test('login page does not link to registration', function () {
    $this->get('/login')
        ->assertOk()
        ->assertDontSee('/register')
        ->assertDontSee('Register');
});
