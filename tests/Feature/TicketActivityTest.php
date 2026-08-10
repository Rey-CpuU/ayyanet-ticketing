<?php

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'cs']);
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

test('reply records an audit entry with the acting user', function () {
    $this->actingAs($this->user)->post("/tickets/{$this->ticket->id}/messages", [
        'message' => 'Kami sedang mengecek.',
    ])->assertRedirect();

    $this->assertDatabaseHas('ticket_activities', [
        'ticket_id' => $this->ticket->id,
        'user_id'   => $this->user->id,
        'action'    => 'reply',
        'new_value' => 'Kami sedang mengecek.',
    ]);
});

test('internal note is flagged and records an internal_note audit entry', function () {
    $this->actingAs($this->user)->post("/tickets/{$this->ticket->id}/messages", [
        'message'     => 'Kemungkinan kabel ODP putus.',
        'is_internal' => '1',
    ])->assertRedirect();

    $this->assertDatabaseHas('ticket_messages', [
        'ticket_id'   => $this->ticket->id,
        'is_internal' => true,
    ]);
    $this->assertDatabaseHas('ticket_activities', [
        'ticket_id' => $this->ticket->id,
        'user_id'   => $this->user->id,
        'action'    => 'internal_note',
        'new_value' => 'Kemungkinan kabel ODP putus.',
    ]);
});

test('status change updates ticket and records old to new', function () {
    $this->actingAs($this->user)->patch("/tickets/{$this->ticket->id}/status", [
        'status' => 'Solved',
    ])->assertRedirect();

    expect($this->ticket->fresh()->status)->toBe('Solved');

    $this->assertDatabaseHas('ticket_activities', [
        'ticket_id' => $this->ticket->id,
        'user_id'   => $this->user->id,
        'action'    => 'status_change',
        'old_value' => 'Open',
        'new_value' => 'Solved',
    ]);
});

test('status change rejects invalid status', function () {
    $this->actingAs($this->user)->from("/tickets/{$this->ticket->id}")
        ->patch("/tickets/{$this->ticket->id}/status", ['status' => 'Nonsense'])
        ->assertSessionHasErrors('status');

    expect($this->ticket->fresh()->status)->toBe('Open');
});

test('status change requires authentication', function () {
    $this->patch("/tickets/{$this->ticket->id}/status", ['status' => 'Solved'])
        ->assertRedirect('/login');
});

test('internal notes are hidden from guests on the show page', function () {
    $this->actingAs($this->user)->post("/tickets/{$this->ticket->id}/messages", [
        'message'     => 'RAHASIA-INTERNAL',
        'is_internal' => '1',
    ]);

    $this->app['auth']->forgetGuards();

    $this->get("/tickets/{$this->ticket->id}")
        ->assertDontSee('RAHASIA-INTERNAL');

    $this->actingAs($this->user)->get("/tickets/{$this->ticket->id}")
        ->assertSee('RAHASIA-INTERNAL')
        ->assertSee('Internal');
});

test('activity log renders on show page for authenticated users', function () {
    $this->actingAs($this->user)->patch("/tickets/{$this->ticket->id}/status", [
        'status' => 'Checking',
    ]);

    $this->actingAs($this->user)->get("/tickets/{$this->ticket->id}")
        ->assertSee('Activity Log')
        ->assertSee('Open')
        ->assertSee('Checking');
});

test('message store requires authentication', function () {
    $this->post("/tickets/{$this->ticket->id}/messages", ['message' => 'x'])
        ->assertRedirect('/login');

    expect(TicketActivity::count())->toBe(0);
});
