<?php

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;
use App\Notifications\TicketAssigned;
use App\Notifications\TicketSlaBreached;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Mail::fake();
    $this->freezeTime();

    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->cs = User::factory()->create(['role' => 'cs']);
    $this->tech = User::factory()->create(['role' => 'lapangan']);
});

function slaUpdatePayload(Ticket $ticket, array $overrides = []): array
{
    return array_merge([
        'customer_id' => $ticket->customer_id,
        'title' => $ticket->title,
        'description' => $ticket->description,
        'priority' => $ticket->priority,
        'status' => $ticket->status,
        'category' => $ticket->category,
    ], $overrides);
}

// ---------------------------------------------------------------- SLA clock

test('new tickets get an SLA deadline from their priority', function () {
    $customer = Customer::factory()->create();

    $this->actingAs($this->cs)->post('/tickets', [
        'customer_id' => $customer->id,
        'title' => 'Internet mati',
        'description' => 'x',
        'priority' => 'High',
    ])->assertRedirect();

    $ticket = Ticket::latest('id')->first();

    expect($ticket->sla_status)->toBe(Ticket::SLA_ACTIVE)
        ->and($ticket->sla_deadline->toDateTimeString())->toBe(now()->addHours(5)->toDateTimeString());
    $this->assertDatabaseHas('ticket_activities', ['ticket_id' => $ticket->id, 'action' => 'created']);
});

test('changing the priority recalculates the SLA deadline', function () {
    $ticket = Ticket::factory()->priority('Medium')->create(['sla_deadline' => now()->addHours(8)]);

    $this->actingAs($this->cs)
        ->put("/tickets/{$ticket->id}", slaUpdatePayload($ticket, ['priority' => 'High']))
        ->assertRedirect();

    expect($ticket->fresh()->sla_deadline->toDateTimeString())->toBe(now()->addHours(5)->toDateTimeString());
    $this->assertDatabaseHas('ticket_activities', [
        'ticket_id' => $ticket->id,
        'action' => 'priority_change',
        'old_value' => 'Medium',
        'new_value' => 'High',
    ]);

    $this->actingAs($this->cs)
        ->put("/tickets/{$ticket->id}", slaUpdatePayload($ticket->fresh(), ['priority' => 'Low']))
        ->assertRedirect();

    expect($ticket->fresh()->sla_deadline->toDateTimeString())->toBe(now()->addHours(24)->toDateTimeString());
});

test('a breach is lifted when a lower priority extends the deadline past now', function () {
    $ticket = Ticket::factory()->priority('High')->create([
        'sla_deadline' => now()->subHour(),
        'sla_status' => Ticket::SLA_BREACHED,
    ]);

    $this->actingAs($this->cs)->put("/tickets/{$ticket->id}", slaUpdatePayload($ticket, ['priority' => 'Low']));

    expect($ticket->fresh()->sla_status)->toBe(Ticket::SLA_ACTIVE);
});

test('waiting customer pauses the SLA and resuming extends the deadline by the paused time', function () {
    $ticket = Ticket::factory()->status('Checking')->create(['sla_deadline' => now()->addHours(2)]);
    $deadline = now()->addHours(2);

    $this->actingAs($this->cs)->patch("/tickets/{$ticket->id}/status", ['status' => 'Waiting Customer']);
    expect($ticket->fresh()->sla_paused_at?->toDateTimeString())->toBe(now()->toDateTimeString());

    $this->travel(3)->hours();

    // Paused tickets are never flagged, even though the original deadline has passed.
    $this->artisan('tickets:check-sla')->assertSuccessful();
    expect($ticket->fresh()->sla_status)->toBe(Ticket::SLA_ACTIVE);

    $this->actingAs($this->cs)->patch("/tickets/{$ticket->id}/status", ['status' => 'Checking']);

    $fresh = $ticket->fresh();
    expect($fresh->sla_paused_at)->toBeNull()
        ->and($fresh->sla_deadline->toDateTimeString())->toBe($deadline->addHours(3)->toDateTimeString());
});

test('resolving on time marks the SLA as met, resolving late as breached', function () {
    $onTime = Ticket::factory()->create(['sla_deadline' => now()->addHour()]);
    $late = Ticket::factory()->create(['sla_deadline' => now()->subHour()]);

    foreach ([$onTime, $late] as $ticket) {
        $this->actingAs($this->cs)->patch("/tickets/{$ticket->id}/status", [
            'status' => 'Solved',
            'resolution_note' => 'Selesai',
        ])->assertSessionHasNoErrors();
    }

    expect($onTime->fresh()->sla_status)->toBe(Ticket::SLA_MET)
        ->and($late->fresh()->sla_status)->toBe(Ticket::SLA_BREACHED);
});

test('show page renders the SLA countdown', function () {
    $ticket = Ticket::factory()->create(['sla_deadline' => now()->addHours(3)]);

    $this->actingAs($this->cs)->get("/tickets/{$ticket->id}")
        ->assertOk()
        ->assertSee('id="sla-countdown"', false)
        ->assertSee('data-deadline="'.now()->addHours(3)->toIso8601String().'"', false)
        ->assertSee('Berjalan');
});

// ---------------------------------------------------------------- breach check

test('the SLA check flags overdue tickets and notifies the assignee and admins once', function () {
    Notification::fake();
    $otherAdmin = User::factory()->create(['role' => 'admin']);

    $overdue = Ticket::factory()->overdue()->assignedTo($this->tech)->create();
    $onTrack = Ticket::factory()->create(['sla_deadline' => now()->addHour()]);
    $solved = Ticket::factory()->overdue()->status('Solved')->create();

    $this->artisan('tickets:check-sla')->expectsOutputToContain('1 tiket')->assertSuccessful();

    expect($overdue->fresh()->sla_status)->toBe(Ticket::SLA_BREACHED)
        ->and($onTrack->fresh()->sla_status)->toBe(Ticket::SLA_ACTIVE)
        ->and($solved->fresh()->sla_status)->toBe(Ticket::SLA_ACTIVE);

    $this->assertDatabaseHas('ticket_activities', [
        'ticket_id' => $overdue->id,
        'user_id' => null,
        'action' => 'sla_breached',
    ]);

    Notification::assertSentTo([$this->tech, $this->admin, $otherAdmin], TicketSlaBreached::class,
        fn ($notification) => $notification->ticket->is($overdue));
    Notification::assertNotSentTo($this->cs, TicketSlaBreached::class);
    Notification::assertCount(3);

    // A second run does not notify again.
    $this->artisan('tickets:check-sla')->assertSuccessful();
    Notification::assertCount(3);
    expect(TicketActivity::where('action', 'sla_breached')->count())->toBe(1);
});

test('breach notifications are stored in the database and queued', function () {
    $ticket = Ticket::factory()->overdue()->create(['ticket_number' => 'TKT-SLA-1']);

    expect(new TicketSlaBreached($ticket))->toBeInstanceOf(ShouldQueue::class);

    $this->artisan('tickets:check-sla');

    expect($this->admin->unreadNotifications()->count())->toBe(1)
        ->and($this->admin->notifications->first()->data['message'])->toContain('TKT-SLA-1');
});

test('the SLA check is scheduled every five minutes', function () {
    $event = collect(app(Schedule::class)->events())
        ->first(fn ($event) => str_contains($event->command ?? '', 'tickets:check-sla'));

    expect($event)->not->toBeNull()
        ->and($event->expression)->toBe('*/5 * * * *');
});

// ---------------------------------------------------------------- assignment + bell

test('assigning a ticket notifies the new assignee but not a self-assignment', function () {
    Notification::fake();
    $ticket = Ticket::factory()->create();

    $this->actingAs($this->cs)->patch("/tickets/{$ticket->id}/assignee", ['assigned_to' => $this->tech->id]);
    Notification::assertSentTo($this->tech, TicketAssigned::class, fn ($n) => $n->ticket->is($ticket));

    $this->actingAs($this->cs)->patch("/tickets/{$ticket->id}/assignee", ['assigned_to' => $this->cs->id]);
    Notification::assertNotSentTo($this->cs, TicketAssigned::class);
});

test('the notification bell shows unread notifications and marks them read', function () {
    $ticket = Ticket::factory()->assignedTo($this->tech)->create(['ticket_number' => 'TKT-BELL']);
    $this->tech->notify(new TicketAssigned($ticket, $this->cs));
    $notification = $this->tech->notifications()->first();

    $this->actingAs($this->tech)->get('/tickets')
        ->assertOk()
        ->assertSee('nb-count', false)
        ->assertSee('Tiket TKT-BELL ditugaskan kepada Anda oleh '.$this->cs->name);

    $this->actingAs($this->tech)->patch("/notifications/{$notification->id}/read")
        ->assertRedirect(route('tickets.show', $ticket));

    expect($notification->fresh()->read_at)->not->toBeNull();
});

test('all notifications can be marked read and other users notifications are off limits', function () {
    $ticket = Ticket::factory()->create();
    $this->cs->notify(new TicketAssigned($ticket));
    $this->cs->notify(new TicketAssigned($ticket));
    $this->admin->notify(new TicketAssigned($ticket));

    $this->actingAs($this->cs)->post('/notifications/read-all')->assertRedirect();
    expect($this->cs->unreadNotifications()->count())->toBe(0)
        ->and($this->admin->unreadNotifications()->count())->toBe(1);

    $foreign = $this->admin->notifications()->first();
    $this->actingAs($this->cs)->patch("/notifications/{$foreign->id}/read")->assertNotFound();
    expect($foreign->fresh()->read_at)->toBeNull();
});
