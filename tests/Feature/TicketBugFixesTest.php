<?php

use App\Mail\TicketUpdatedMail;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'cs']);
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->customer = Customer::create([
        'customer_id' => 'C-TEST',
        'name' => 'Siti Aminah',
        'email' => 'siti@example.test',
        'phone' => '0812-1111-2222',
        'address' => 'Jl. Kenanga No. 5',
        'package' => 'Home 30 Mbps',
    ]);
    $this->ticket = Ticket::create([
        'ticket_number' => 'TKT-TEST-1',
        'customer_id' => $this->customer->id,
        'created_by' => $this->user->id,
        'title' => 'Internet lambat',
        'description' => 'Kecepatan turun sejak pagi',
        'category' => 'Email',
        'priority' => 'Medium',
        'status' => 'Open',
    ]);
});

function ticketPayload(Ticket $ticket, array $overrides = []): array
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

// Item 1: a status change is recorded in the activity stream with the real old status.
// (The legacy ticket_progress table was dropped; ticket_activities is the single history.)
test('updating the status records a progress entry from the old to the new status', function () {
    Mail::fake();

    $this->actingAs($this->user)
        ->put("/tickets/{$this->ticket->id}", ticketPayload($this->ticket, ['status' => 'Checking']))
        ->assertRedirect(route('tickets.show', $this->ticket->id));

    expect($this->ticket->fresh()->status)->toBe('Checking');

    $this->assertDatabaseHas('ticket_activities', [
        'ticket_id' => $this->ticket->id,
        'action' => 'status_change',
        'old_value' => 'Open',
        'new_value' => 'Checking',
        'user_id' => $this->user->id,
    ]);
    $this->assertDatabaseHas('ticket_audit_logs', [
        'ticket_id' => $this->ticket->id,
        'action' => 'updated',
    ]);

    Mail::assertQueued(TicketUpdatedMail::class, fn ($mail) => $mail->hasTo('siti@example.test'));
});

test('updating without a status change does not record a progress entry', function () {
    Mail::fake();

    $this->actingAs($this->user)
        ->put("/tickets/{$this->ticket->id}", ticketPayload($this->ticket, ['title' => 'Internet sangat lambat']))
        ->assertRedirect();

    $this->assertDatabaseMissing('ticket_activities', [
        'ticket_id' => $this->ticket->id,
        'action' => 'status_change',
    ]);
});

test('no email is sent when the customer has no email address', function () {
    Mail::fake();
    $this->customer->update(['email' => null]);

    $this->actingAs($this->user)
        ->put("/tickets/{$this->ticket->id}", ticketPayload($this->ticket, ['status' => 'Checking']))
        ->assertRedirect();

    Mail::assertNothingQueued();
    Mail::assertNothingSent();
});

// Item 2: restore / force-delete must resolve soft-deleted models.
test('a soft-deleted ticket can be restored', function () {
    $this->ticket->delete();

    $this->actingAs($this->admin)
        ->patch("/tickets/{$this->ticket->id}/restore")
        ->assertRedirect(route('tickets.index'));

    expect(Ticket::find($this->ticket->id))->not->toBeNull();
});

test('a soft-deleted ticket can be force deleted', function () {
    $this->ticket->delete();

    $this->actingAs($this->admin)
        ->delete("/tickets/{$this->ticket->id}/force-delete")
        ->assertRedirect(route('tickets.index'));

    expect(Ticket::withTrashed()->find($this->ticket->id))->toBeNull();
});

test('a soft-deleted customer can be restored and force deleted', function () {
    $other = Customer::create([
        'customer_id' => 'C-OTHER',
        'name' => 'Andi',
        'phone' => '0812-3333-4444',
        'address' => 'Jl. Mawar',
        'package' => 'Home 20 Mbps',
    ]);
    $other->delete();

    $this->actingAs($this->admin)
        ->patch("/customers/{$other->id}/restore")
        ->assertRedirect(route('customers.index'));

    expect(Customer::find($other->id))->not->toBeNull();

    $other->delete();

    $this->actingAs($this->admin)
        ->delete("/customers/{$other->id}/force-delete")
        ->assertRedirect(route('customers.index'));

    expect(Customer::withTrashed()->find($other->id))->toBeNull();
});

// Item 3: identifiers are derived from the inserted row id.
test('ticket numbers are derived from the new row id and stay unique', function () {
    Mail::fake();

    foreach (['Gangguan pertama', 'Gangguan kedua'] as $title) {
        $this->actingAs($this->user)->post('/tickets', [
            'customer_id' => $this->customer->id,
            'title' => $title,
            'description' => 'Tidak ada koneksi',
        ])->assertRedirect();
    }

    $created = Ticket::where('title', 'like', 'Gangguan%')->orderBy('id')->get();

    expect($created)->toHaveCount(2);
    foreach ($created as $ticket) {
        expect($ticket->ticket_number)->toBe('TKT-'.str_pad((string) $ticket->id, 4, '0', STR_PAD_LEFT));
    }
    expect($created->pluck('ticket_number')->unique())->toHaveCount(2);
});

test('customer ids are derived from the new row id', function () {
    $this->actingAs($this->user)->post('/customers', [
        'name' => 'Rina',
        'phone' => '0812-5555-6666',
        'address' => 'Jl. Anggrek',
        'package' => 'Home 50 Mbps',
    ])->assertRedirect(route('customers.index'));

    $customer = Customer::where('name', 'Rina')->firstOrFail();

    expect($customer->customer_id)->toBe('C-'.str_pad((string) $customer->id, 4, '0', STR_PAD_LEFT));
});

test('customer update ignores fields outside the validated set', function () {
    $this->actingAs($this->user)->put("/customers/{$this->customer->id}", [
        'customer_id' => 'C-HACKED',
        'name' => 'Siti A.',
        'phone' => '0812-1111-2222',
        'address' => 'Jl. Kenanga No. 5',
        'package' => 'Home 30 Mbps',
    ])->assertRedirect(route('customers.index'));

    $fresh = $this->customer->fresh();
    expect($fresh->name)->toBe('Siti A.');
    expect($fresh->customer_id)->toBe('C-TEST');
});

// Item 9: dashboard filters run in SQL so recent tickets still returns five rows.
test('dashboard recent tickets returns five filtered tickets', function () {
    foreach (range(1, 6) as $i) {
        Ticket::create([
            'ticket_number' => "TKT-OPEN-{$i}",
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'title' => "Open {$i}",
            'description' => 'x',
            'status' => 'Open',
        ]);
    }
    foreach (range(1, 5) as $i) {
        Ticket::create([
            'ticket_number' => "TKT-SOLVED-{$i}",
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'title' => "Solved {$i}",
            'description' => 'x',
            'status' => 'Solved',
        ]);
    }

    $response = $this->actingAs($this->user)->get('/dashboard?status=open')->assertOk();

    expect($response->viewData('recentTickets'))->toHaveCount(5);
    expect($response->viewData('recentTickets')->pluck('status')->unique()->all())->toBe(['Open']);
    expect($response->viewData('tickets'))->toHaveCount(7);
    expect($response->viewData('stats')['total'])->toBe(12);
});
