<?php

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;

it('filters the dashboard by ticket status and shows the selected ticket details', function () {
    $user = User::factory()->create();

    $customer = Customer::create([
        'customer_id' => 'C-7001',
        'name' => 'Alya Salsabila',
        'phone' => '0812347001',
        'address' => 'Semarang',
        'package' => 'Business',
    ]);

    Ticket::create([
        'ticket_number' => 'TKT-7001',
        'customer_id' => $customer->id,
        'created_by' => $user->id,
        'title' => 'Open ticket for status test',
        'description' => 'Should be visible in open filter.',
        'category' => 'Email',
        'priority' => 'High',
        'status' => 'Open',
    ]);

    Ticket::create([
        'ticket_number' => 'TKT-7002',
        'customer_id' => $customer->id,
        'created_by' => $user->id,
        'title' => 'Solved ticket for status test',
        'description' => 'Should be hidden in open filter.',
        'category' => 'WhatsApp',
        'priority' => 'Medium',
        'status' => 'Solved',
    ]);

    $this->actingAs($user)
        ->get('/dashboard?status=Open')
        ->assertOk()
        ->assertSee('Open ticket for status test')
        ->assertDontSee('Solved ticket for status test');

    $ticket = Ticket::first();

    $this->actingAs($user)
        ->get('/tickets/' . $ticket->id)
        ->assertOk()
        ->assertSee('Open ticket for status test')
        ->assertSee('Alya Salsabila');
});

it('supports editing a ticket, selecting it from the dashboard, and storing reply notes', function () {
    $user = User::factory()->create();
    $customer = Customer::create([
        'customer_id' => 'C-7003',
        'name' => 'Reza Pratama',
        'phone' => '0812347003',
        'address' => 'Yogyakarta',
        'package' => 'Premium',
    ]);

    $ticket = Ticket::create([
        'ticket_number' => 'TKT-7003',
        'customer_id' => $customer->id,
        'created_by' => $user->id,
        'title' => 'Payment issue ticket',
        'description' => 'Original description',
        'category' => 'Portal',
        'priority' => 'Medium',
        'status' => 'Open',
    ]);

    $this->actingAs($user)
        ->get('/tickets/' . $ticket->id . '/edit')
        ->assertOk()
        ->assertSee('Edit Ticket');

    $this->actingAs($user)
        ->get('/dashboard?selected=' . $ticket->id)
        ->assertOk()
        ->assertSee('Payment issue ticket');

    $this->actingAs($user)
        ->post('/tickets/' . $ticket->id . '/messages', [
            'message' => 'This is an internal note',
            'type' => 'internal',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('ticket_messages', [
        'ticket_id' => $ticket->id,
        'message' => 'This is an internal note',
        'type' => 'internal',
    ]);
});
