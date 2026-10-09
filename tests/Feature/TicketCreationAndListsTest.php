<?php

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;

it('creates a ticket even when no user is authenticated', function () {
    $user = User::factory()->create();
    $customer = Customer::create([
        'customer_id' => 'C-4001',
        'name' => 'Asep Wijaya',
        'phone' => '0812340001',
        'address' => 'Bandung',
        'package' => 'Business',
    ]);

    $response = $this->actingAs($user)->post('/tickets', [
        'customer_id' => $customer->id,
        'title' => 'VPN cannot connect',
        'description' => 'Customer cannot reach VPN from office.',
        'category' => 'Email',
        'priority' => 'High',
        'status' => 'Open',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('tickets', [
        'title' => 'VPN cannot connect',
        'customer_id' => $customer->id,
    ]);
});

it('allows creating a customer via the form', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/customers', [
        'name' => 'Budi Santoso',
        'email' => 'budi@example.com',
        'phone' => '0812345678',
        'address' => 'Bandung',
        'package' => 'Premium',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('customers', [
        'name' => 'Budi Santoso',
        'email' => 'budi@example.com',
    ]);
});

it('shows only the authenticated user tickets on my tickets page', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $customerA = Customer::create([
        'customer_id' => 'C-5001',
        'name' => 'Rina Putri',
        'phone' => '0812345001',
        'address' => 'Jakarta',
        'package' => 'Home',
    ]);

    $customerB = Customer::create([
        'customer_id' => 'C-5002',
        'name' => 'Doni Hartono',
        'phone' => '0812345002',
        'address' => 'Surabaya',
        'package' => 'Business',
    ]);

    Ticket::create([
        'ticket_number' => 'TKT-5001',
        'customer_id' => $customerA->id,
        'created_by' => $owner->id,
        'title' => 'My own ticket',
        'description' => 'Should appear.',
        'category' => 'Email',
        'priority' => 'Medium',
        'status' => 'Open',
    ]);

    Ticket::create([
        'ticket_number' => 'TKT-5002',
        'customer_id' => $customerB->id,
        'created_by' => $other->id,
        'title' => 'Other ticket',
        'description' => 'Should not appear.',
        'category' => 'Email',
        'priority' => 'Medium',
        'status' => 'Open',
    ]);

    $this->actingAs($owner)
        ->get('/my-tickets')
        ->assertOk()
        ->assertSee('My own ticket')
        ->assertDontSee('Other ticket');
});
