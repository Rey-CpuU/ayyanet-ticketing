<?php

use App\Mail\TicketUpdatedMail;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

// Item 4: the customer is emailed (queued) only when the status really changes, through either
// the edit form or PATCH /status, and never when no email address is on file.

beforeEach(function () {
    Mail::fake();

    $this->cs = User::factory()->create(['role' => 'cs']);
    $this->customer = Customer::factory()->create(['email' => 'pelanggan@example.test']);
    $this->ticket = Ticket::factory()->for($this->customer)->create(['status' => 'Open', 'category' => 'Email']);
});

function statusEmailPayload(Ticket $ticket, array $overrides = []): array
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

test('editing a ticket without changing the status sends no customer email', function () {
    $this->actingAs($this->cs)
        ->put("/tickets/{$this->ticket->id}", statusEmailPayload($this->ticket, ['title' => 'Judul baru']))
        ->assertRedirect();

    expect($this->ticket->fresh()->title)->toBe('Judul baru');
    Mail::assertNothingQueued();
    Mail::assertNothingSent();
});

test('a status change through the edit form queues one customer email', function () {
    $this->actingAs($this->cs)
        ->put("/tickets/{$this->ticket->id}", statusEmailPayload($this->ticket, ['status' => 'Checking']))
        ->assertRedirect();

    Mail::assertQueued(TicketUpdatedMail::class, 1);
    Mail::assertQueued(TicketUpdatedMail::class, fn ($mail) => $mail->hasTo('pelanggan@example.test')
        && $mail->action === 'Status ticket diperbarui ke Checking');
});

test('a status change through PATCH /status queues the customer email', function () {
    $this->actingAs($this->cs)
        ->patch("/tickets/{$this->ticket->id}/status", ['status' => 'Checking'])
        ->assertRedirect();

    Mail::assertQueued(TicketUpdatedMail::class, fn ($mail) => $mail->hasTo('pelanggan@example.test')
        && $mail->action === 'Status ticket diperbarui ke Checking');
});

test('PATCH /status with the current status sends nothing', function () {
    $this->actingAs($this->cs)
        ->patch("/tickets/{$this->ticket->id}/status", ['status' => 'Open'])
        ->assertRedirect();

    Mail::assertNothingQueued();
});

test('no customer email is sent when the customer has no address', function () {
    $this->customer->update(['email' => null]);

    $this->actingAs($this->cs)
        ->patch("/tickets/{$this->ticket->id}/status", ['status' => 'Checking'])
        ->assertRedirect();

    expect($this->ticket->fresh()->status)->toBe('Checking');
    Mail::assertNotQueued(TicketUpdatedMail::class);
});
