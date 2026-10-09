<?php

use App\Models\User;

it('has working ticketing navigation routes and ticket creation page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')->assertOk();
    $this->actingAs($user)
        ->get('/tickets')->assertOk();
    $this->actingAs($user)
        ->get('/tickets/create')->assertOk();
    $this->actingAs($user)
        ->get('/my-tickets')->assertOk();
    $this->actingAs($user)
        ->get('/reports')->assertOk();
    $this->actingAs($user)
        ->get('/settings')->assertOk();
});
