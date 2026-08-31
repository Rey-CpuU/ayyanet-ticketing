<?php

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;

function createTicketAssignmentContext(): array
{
    $user = User::factory()->create(['role' => 'cs']);
    $agent = User::factory()->create(['role' => 'cs']);
    $customer = Customer::create([
        'name' => 'Budi',
        'phone' => '0812-0000-0000',
        'address' => 'Jl. Melati No. 1',
        'customer_id' => 'CUS-TEST1',
    ]);
    $ticket = Ticket::create([
        'ticket_number' => 'TCK-TEST-1',
        'customer_id' => $customer->id,
        'created_by' => $user->id,
        'title' => 'Internet putus',
        'description' => 'Tidak ada koneksi',
        'category' => 'Internet',
        'priority' => 'Medium',
        'status' => 'Open',
    ]);

    return compact('user', 'agent', 'customer', 'ticket');
}

test('assignment requires authentication', function () {
    ['agent' => $agent, 'ticket' => $ticket] = createTicketAssignmentContext();

    $this->patch("/tickets/{$ticket->id}/assignee", ['assigned_to' => $agent->id])
        ->assertRedirect('/login');
});

test('staff can place a ticket with an agent and records an assignment audit entry', function () {
    ['user' => $user, 'agent' => $agent, 'ticket' => $ticket] = createTicketAssignmentContext();

    $this->actingAs($user)->patch("/tickets/{$ticket->id}/assignee", [
        'assigned_to' => $agent->id,
    ])->assertRedirect();

    expect($ticket->fresh()->assigned_to)->toBe($agent->id);

    $this->assertDatabaseHas('ticket_activities', [
        'ticket_id' => $ticket->id,
        'user_id'   => $user->id,
        'action'    => 'assignment',
        'old_value' => 'Unassigned',
        'new_value' => $agent->name,
    ]);
});

test('a ticket can be unassigned', function () {
    ['user' => $user, 'agent' => $agent, 'ticket' => $ticket] = createTicketAssignmentContext();
    $ticket->update(['assigned_to' => $agent->id]);

    $this->actingAs($user)->patch("/tickets/{$ticket->id}/assignee", [
        'assigned_to' => '',
    ])->assertRedirect();

    expect($ticket->fresh()->assigned_to)->toBeNull();

    $this->assertDatabaseHas('ticket_activities', [
        'ticket_id' => $ticket->id,
        'action'    => 'assignment',
        'old_value' => $agent->name,
        'new_value' => 'Unassigned',
    ]);
});

test('assigning the same agent does not create a duplicate audit entry', function () {
    ['user' => $user, 'agent' => $agent, 'ticket' => $ticket] = createTicketAssignmentContext();
    $ticket->update(['assigned_to' => $agent->id]);

    $this->actingAs($user)->patch("/tickets/{$ticket->id}/assignee", [
        'assigned_to' => $agent->id,
    ])->assertRedirect();

    expect(TicketActivity::where('ticket_id', $ticket->id)->where('action', 'assignment')->count())->toBe(0);
});

test('assigning to a non-existent user is rejected', function () {
    ['user' => $user, 'ticket' => $ticket] = createTicketAssignmentContext();

    $this->actingAs($user)->patch("/tickets/{$ticket->id}/assignee", [
        'assigned_to' => 99999,
    ])->assertSessionHasErrors('assigned_to');

    expect($ticket->fresh()->assigned_to)->toBeNull();
});

test('creating a ticket auto-detects the priority and category', function () {
    ['user' => $user, 'customer' => $customer] = createTicketAssignmentContext();

    $this->actingAs($user)->post('/tickets', [
        'customer_id' => $customer->id,
        'title' => 'Gangguan total, seluruh area tidak ada koneksi',
        'description' => 'Outage di area Perumnas',
    ])->assertRedirect('/tickets');

    $this->assertDatabaseHas('tickets', [
        'priority' => 'High',
        'category' => 'Internet',
    ]);
});

test('a manual priority override wins over the classifier', function () {
    ['user' => $user, 'customer' => $customer] = createTicketAssignmentContext();

    $this->actingAs($user)->post('/tickets', [
        'customer_id' => $customer->id,
        'title' => 'Gangguan total, seluruh area tidak ada koneksi',
        'description' => 'Outage di area Perumnas',
        'priority' => 'Low',
    ])->assertRedirect('/tickets');

    $this->assertDatabaseHas('tickets', [
        'priority' => 'Low',
    ]);
});

test('classify endpoint returns category and priority', function () {
    $this->post('/tickets/classify', ['title' => 'Internet lambat saat jam kantor'])
        ->assertOk()
        ->assertJson(['priority' => 'Medium', 'category' => 'Internet']);
});

test('assignment endpoint responds with JSON when requested', function () {
    ['user' => $user, 'agent' => $agent, 'ticket' => $ticket] = createTicketAssignmentContext();

    $this->actingAs($user)->patchJson("/tickets/{$ticket->id}/assignee", [
        'assigned_to' => $agent->id,
    ])->assertOk()->assertJson(['success' => true]);

    expect($ticket->fresh()->assigned_to)->toBe($agent->id);
});
