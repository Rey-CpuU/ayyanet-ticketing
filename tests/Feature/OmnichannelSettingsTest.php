<?php

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;

it('filters tickets by communication channel and shows admin settings sections', function () {
    $user = User::factory()->create();

    $customer = Customer::create([
        'customer_id' => 'C-9001',
        'name' => 'Rafi Aditya',
        'phone' => '0812349001',
        'address' => 'Yogyakarta',
        'package' => 'Business',
    ]);

    Ticket::create([
        'ticket_number' => 'TKT-9001',
        'customer_id' => $customer->id,
        'created_by' => $user->id,
        'title' => 'Email ticket',
        'description' => 'One email ticket.',
        'category' => 'Email',
        'priority' => 'High',
        'status' => 'Open',
    ]);

    Ticket::create([
        'ticket_number' => 'TKT-9002',
        'customer_id' => $customer->id,
        'created_by' => $user->id,
        'title' => 'WhatsApp ticket',
        'description' => 'One WhatsApp ticket.',
        'category' => 'WhatsApp',
        'priority' => 'Medium',
        'status' => 'Open',
    ]);

    $response = $this->actingAs($user)
        ->get('/dashboard?channel=Email');

    $response->assertOk()
        ->assertSee('Email ticket')
        ->assertDontSee('WhatsApp ticket');

    $this->actingAs($user)
        ->get('/settings')
        ->assertOk()
        ->assertSee('Tim & Agen')
        ->assertSee('Hak Akses')
        ->assertSee('Konfigurasi SLA')
        ->assertSee('Data Customer')
        ->assertSee('Jam Operasional')
        ->assertSee('Saluran (Omnichannel)');
});
