<?php

use App\Models\User;

it('exposes working navigation routes for ticketing actions', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get('/dashboard');

    $response->assertOk();
    $response->assertSee('/tickets');
    $response->assertSee('/tickets/create');
    $response->assertSee('/reports');
    $response->assertSee('/settings');
});
