<?php

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;

it('shows live ticket data on the dashboard', function () {
    $user = User::factory()->create();

    $customer = Customer::create([
        'customer_id' => 'C-1001',
        'name' => 'Mara Okonkwo',
        'phone' => '08123456789',
        'address' => 'Jakarta',
        'package' => 'Business',
    ]);

    Ticket::create([
        'ticket_number' => 'TKT-4812',
        'customer_id' => $customer->id,
        'created_by' => $user->id,
        'assigned_to' => $user->id,
        'title' => 'Payment gateway returning 500 errors on checkout',
        'description' => 'Stripe error',
        'category' => 'Billing',
        'olt' => 'OLT-01',
        'location' => 'Jakarta',
        'priority' => 'High',
        'status' => 'Open',
    ]);

    $response = $this->actingAs($user)
        ->get('/dashboard');

    $response->assertOk();
    $response->assertSee('TKT-4812');
    $response->assertSee('Mara Okonkwo');
    $response->assertSee('Payment gateway returning 500 errors on checkout');
});
