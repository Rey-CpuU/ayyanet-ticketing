<?php

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;

function createTicketActivityContext(): array
{
    $user = User::factory()->create(['role' => 'cs']);
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

    return compact('user', 'customer', 'ticket');
}

test('reply records an audit entry with the acting user', function () {
    ['user' => $user, 'ticket' => $ticket] = createTicketActivityContext();

    $this->actingAs($user)->post("/tickets/{$ticket->id}/messages", [
        'message' => 'Kami sedang mengecek.',
    ])->assertRedirect();

    $this->assertDatabaseHas('ticket_activities', [
        'ticket_id' => $ticket->id,
        'user_id'   => $user->id,
        'action'    => 'reply',
        'new_value' => 'Kami sedang mengecek.',
    ]);
});

test('internal note is flagged and records an internal_note audit entry', function () {
    ['user' => $user, 'ticket' => $ticket] = createTicketActivityContext();

    $this->actingAs($user)->post("/tickets/{$ticket->id}/messages", [
        'message'     => 'Kemungkinan kabel ODP putus.',
        'is_internal' => '1',
    ])->assertRedirect();

    $this->assertDatabaseHas('ticket_messages', [
        'ticket_id'   => $ticket->id,
        'is_internal' => true,
    ]);
    $this->assertDatabaseHas('ticket_activities', [
        'ticket_id' => $ticket->id,
        'user_id'   => $user->id,
        'action'    => 'internal_note',
        'new_value' => 'Kemungkinan kabel ODP putus.',
    ]);
});

test('status change updates ticket and records old to new', function () {
    ['user' => $user, 'ticket' => $ticket] = createTicketActivityContext();

    $this->actingAs($user)->patch("/tickets/{$ticket->id}/status", [
        'status' => 'Solved',
    ])->assertRedirect();

    expect($ticket->fresh()->status)->toBe('Solved');

    $this->assertDatabaseHas('ticket_activities', [
        'ticket_id' => $ticket->id,
        'user_id'   => $user->id,
        'action'    => 'status_change',
        'old_value' => 'Open',
        'new_value' => 'Solved',
    ]);
});

test('status change rejects invalid status', function () {
    ['user' => $user, 'ticket' => $ticket] = createTicketActivityContext();

    $this->actingAs($user)->from("/tickets/{$ticket->id}")
        ->patch("/tickets/{$ticket->id}/status", ['status' => 'Nonsense'])
        ->assertSessionHasErrors('status');

    expect($ticket->fresh()->status)->toBe('Open');
});

test('status change requires authentication', function () {
    ['ticket' => $ticket] = createTicketActivityContext();

    $this->patch("/tickets/{$ticket->id}/status", ['status' => 'Solved'])
        ->assertRedirect('/login');
});

test('internal notes are hidden from guests on the show page', function () {
    ['user' => $user, 'ticket' => $ticket] = createTicketActivityContext();

    $this->actingAs($user)->post("/tickets/{$ticket->id}/messages", [
        'message'     => 'RAHASIA-INTERNAL',
        'is_internal' => '1',
    ]);

    $this->app['auth']->forgetGuards();

    $this->get("/tickets/{$ticket->id}")
        ->assertDontSee('RAHASIA-INTERNAL');

    $this->actingAs($user)->get("/tickets/{$ticket->id}")
        ->assertSee('RAHASIA-INTERNAL')
        ->assertSee('Internal');
});

test('activity log renders on show page for authenticated users', function () {
    ['user' => $user, 'ticket' => $ticket] = createTicketActivityContext();

    $this->actingAs($user)->patch("/tickets/{$ticket->id}/status", [
        'status' => 'Checking',
    ]);

    $this->actingAs($user)->get("/tickets/{$ticket->id}")
        ->assertSee('Activity Log')
        ->assertSee('Open')
        ->assertSee('Checking');
});

test('message store requires authentication', function () {
    ['ticket' => $ticket] = createTicketActivityContext();

    $this->post("/tickets/{$ticket->id}/messages", ['message' => 'x'])
        ->assertRedirect('/login');

    expect(TicketActivity::count())->toBe(0);
});
