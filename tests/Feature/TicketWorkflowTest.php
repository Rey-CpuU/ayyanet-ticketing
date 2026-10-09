<?php

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
    $this->cs = User::factory()->create(['role' => 'cs']);
});

function patchStatus(User $user, Ticket $ticket, string $status, ?string $note = null)
{
    return test()->actingAs($user)
        ->from("/tickets/{$ticket->id}")
        ->patch("/tickets/{$ticket->id}/status", array_filter([
            'status' => $status,
            'resolution_note' => $note,
        ]));
}

test('every active status can move to every other status, resolved ones only close or reopen', function () {
    foreach (TicketStatus::active() as $status) {
        expect($status->canTransitionTo(TicketStatus::Solved))->toBeTrue()
            ->and($status->canTransitionTo(TicketStatus::Closed))->toBeTrue()
            ->and($status->canTransitionTo($status))->toBeFalse();
    }

    expect(TicketStatus::Solved->allowedTransitions())->toBe([TicketStatus::Closed, TicketStatus::Open])
        ->and(TicketStatus::Closed->allowedTransitions())->toBe([TicketStatus::Open]);
});

test('an escalated ticket can be solved with a resolution note', function () {
    $ticket = Ticket::factory()->status('Escalated')->create();

    patchStatus($this->cs, $ticket, 'Solved', 'Kabel ODP diganti oleh tim NOC.')->assertRedirect("/tickets/{$ticket->id}");

    expect($ticket->fresh())
        ->status->toBe('Solved')
        ->resolution_note->toBe('Kabel ODP diganti oleh tim NOC.')
        ->resolved_at->not->toBeNull();

    $this->assertDatabaseHas('ticket_activities', [
        'ticket_id' => $ticket->id,
        'action' => 'resolution_note',
        'new_value' => 'Kabel ODP diganti oleh tim NOC.',
    ]);
});

test('solving without a resolution note is rejected', function () {
    $ticket = Ticket::factory()->status('Checking')->create();

    patchStatus($this->cs, $ticket, 'Solved')->assertSessionHasErrors('resolution_note');

    expect($ticket->fresh()->status)->toBe('Checking');
    expect(TicketActivity::count())->toBe(0);
});

test('closing an unresolved ticket requires a note, closing a solved one does not', function () {
    $checking = Ticket::factory()->status('Checking')->create();

    patchStatus($this->cs, $checking, 'Closed')->assertSessionHasErrors('resolution_note');
    patchStatus($this->cs, $checking, 'Closed', 'Duplikat dari TKT-0001.')->assertSessionHasNoErrors();
    expect($checking->fresh()->status)->toBe('Closed');

    $solved = Ticket::factory()->status('Solved')->create(['resolution_note' => 'Sudah normal.']);

    patchStatus($this->cs, $solved, 'Closed')->assertSessionHasNoErrors();
    expect($solved->fresh())->status->toBe('Closed')->resolution_note->toBe('Sudah normal.');
});

test('disallowed transitions are rejected', function () {
    $ticket = Ticket::factory()->status('Solved')->create(['resolution_note' => 'x']);

    patchStatus($this->cs, $ticket, 'Checking')->assertSessionHasErrors('status');

    expect($ticket->fresh()->status)->toBe('Solved');
});

test('reopening clears the resolution and starts a fresh SLA window', function () {
    $this->freezeTime();
    $ticket = Ticket::factory()->status('Closed')->priority('High')->create([
        'resolution_note' => 'Selesai',
        'resolved_at' => now()->subDay(),
        'sla_deadline' => now()->subDays(2),
        'sla_status' => Ticket::SLA_MET,
    ]);

    patchStatus($this->cs, $ticket, 'Open')->assertSessionHasNoErrors();

    $fresh = $ticket->fresh();
    expect($fresh->status)->toBe('Open')
        ->and($fresh->resolution_note)->toBeNull()
        ->and($fresh->resolved_at)->toBeNull()
        ->and($fresh->sla_status)->toBe(Ticket::SLA_ACTIVE)
        ->and($fresh->sla_deadline->toDateTimeString())->toBe(now()->addHours(5)->toDateTimeString());
});

test('field technicians can change the status of their own tickets only', function () {
    $lapangan = User::factory()->create(['role' => 'lapangan']);
    $own = Ticket::factory()->assignedTo($lapangan)->create();
    $other = Ticket::factory()->create();

    patchStatus($lapangan, $own, 'Checking')->assertSessionHasNoErrors();
    expect($own->fresh()->status)->toBe('Checking');

    patchStatus($lapangan, $other, 'Checking')->assertForbidden();
    expect($other->fresh()->status)->toBe('Open');
});

test('the edit form enforces the same workflow rules', function () {
    $ticket = Ticket::factory()->create();
    $payload = [
        'customer_id' => $ticket->customer_id,
        'title' => $ticket->title,
        'description' => $ticket->description,
        'priority' => $ticket->priority,
        'category' => $ticket->category,
        'status' => 'Solved',
    ];

    $this->actingAs($this->cs)->from("/tickets/{$ticket->id}/edit")
        ->put("/tickets/{$ticket->id}", $payload)
        ->assertSessionHasErrors('resolution_note');
    expect($ticket->fresh()->status)->toBe('Open');

    $this->actingAs($this->cs)
        ->put("/tickets/{$ticket->id}", $payload + ['resolution_note' => 'Konfigurasi ulang ONT.'])
        ->assertRedirect(route('tickets.show', $ticket->id));

    expect($ticket->fresh())->status->toBe('Solved')->resolution_note->toBe('Konfigurasi ulang ONT.');
    $this->assertDatabaseHas('ticket_activities', [
        'ticket_id' => $ticket->id,
        'action' => 'status_change',
        'old_value' => 'Open',
        'new_value' => 'Solved',
    ]);
});

test('show page offers only allowed status transitions and the activity stream', function () {
    $ticket = Ticket::factory()->status('Solved')->create(['resolution_note' => 'Modem diganti.']);
    TicketActivity::create([
        'ticket_id' => $ticket->id,
        'user_id' => null,
        'action' => 'sla_breached',
        'new_value' => '09 Oct 2026, 10:00',
    ]);

    $response = $this->actingAs($this->cs)->get("/tickets/{$ticket->id}")->assertOk();

    $response->assertSee('Activity Log')
        ->assertSee('SLA terlampaui')
        ->assertSee('Sistem')
        ->assertSee('Modem diganti.')
        ->assertSee('<option value="Closed"', false)
        ->assertDontSee('<option value="Checking"', false);
});
