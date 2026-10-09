<?php

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'cs']);
    $this->agent = User::factory()->create(['role' => 'cs']);
    $this->customer = Customer::create([
        'name' => 'Budi',
        'phone' => '0812-0000-0000',
        'address' => 'Jl. Melati No. 1',
        'customer_id' => 'CUS-TEST1',
    ]);
    $this->ticket = Ticket::create([
        'ticket_number' => 'TCK-TEST-1',
        'customer_id' => $this->customer->id,
        'created_by' => $this->user->id,
        'title' => 'Internet putus',
        'description' => 'Tidak ada koneksi',
        'category' => 'Internet',
        'priority' => 'Medium',
        'status' => 'Open',
    ]);
});

test('assignment requires authentication', function () {
    $this->patch("/tickets/{$this->ticket->id}/assignee", ['assigned_to' => $this->agent->id])
        ->assertRedirect('/login');
});

test('staff can place a ticket with an agent and records an assignment audit entry', function () {
    $this->actingAs($this->user)->patch("/tickets/{$this->ticket->id}/assignee", [
        'assigned_to' => $this->agent->id,
    ])->assertRedirect();

    expect($this->ticket->fresh()->assigned_to)->toBe($this->agent->id);

    $this->assertDatabaseHas('ticket_activities', [
        'ticket_id' => $this->ticket->id,
        'user_id' => $this->user->id,
        'action' => 'assignment',
        'old_value' => 'Unassigned',
        'new_value' => $this->agent->name,
    ]);
});

test('a ticket can be unassigned', function () {
    $this->ticket->update(['assigned_to' => $this->agent->id]);

    $this->actingAs($this->user)->patch("/tickets/{$this->ticket->id}/assignee", [
        'assigned_to' => '',
    ])->assertRedirect();

    expect($this->ticket->fresh()->assigned_to)->toBeNull();

    $this->assertDatabaseHas('ticket_activities', [
        'ticket_id' => $this->ticket->id,
        'action' => 'assignment',
        'old_value' => $this->agent->name,
        'new_value' => 'Unassigned',
    ]);
});

test('assigning the same agent does not create a duplicate audit entry', function () {
    $this->ticket->update(['assigned_to' => $this->agent->id]);

    $this->actingAs($this->user)->patch("/tickets/{$this->ticket->id}/assignee", [
        'assigned_to' => $this->agent->id,
    ])->assertRedirect();

    expect(TicketActivity::where('ticket_id', $this->ticket->id)->where('action', 'assignment')->count())->toBe(0);
});

test('assigning to a non-existent user is rejected', function () {
    $this->actingAs($this->user)->patch("/tickets/{$this->ticket->id}/assignee", [
        'assigned_to' => 99999,
    ])->assertSessionHasErrors('assigned_to');

    expect($this->ticket->fresh()->assigned_to)->toBeNull();
});
