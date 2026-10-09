<?php

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// Item 20: a field technician (lapangan) may only change the status (workflow rules), OLT/location
// and add notes on their own tickets; core fields are ignored server-side and hidden in the forms.

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->tech = User::factory()->create(['role' => 'lapangan']);
    $this->customer = Customer::factory()->create();
    $this->otherCustomer = Customer::factory()->create();

    $this->ticket = Ticket::factory()->for($this->customer)->assignedTo($this->tech)->create([
        'title' => 'Judul asli',
        'description' => 'Deskripsi asli',
        'category' => 'Email',
        'priority' => 'High',
        'status' => 'Open',
    ]);
});

test('lapangan cannot change title, customer, priority, category or description', function () {
    $this->actingAs($this->tech)->put("/tickets/{$this->ticket->id}", [
        'customer_id' => $this->otherCustomer->id,
        'title' => 'Diubah teknisi',
        'description' => 'Diubah teknisi',
        'category' => 'WhatsApp',
        'priority' => 'Low',
        'assigned_to' => $this->admin->id,
        'status' => 'Checking',
        'olt' => 'OLT-07',
        'location' => 'Port 3',
    ])->assertRedirect(route('tickets.show', $this->ticket->id))
        ->assertSessionHasNoErrors();

    $ticket = $this->ticket->fresh();

    expect($ticket->title)->toBe('Judul asli')
        ->and($ticket->description)->toBe('Deskripsi asli')
        ->and($ticket->customer_id)->toBe($this->customer->id)
        ->and($ticket->category)->toBe('Email')
        ->and($ticket->priority)->toBe('High')
        ->and($ticket->assigned_to)->toBe($this->tech->id)
        // Allowed fields do change.
        ->and($ticket->status)->toBe('Checking')
        ->and($ticket->olt)->toBe('OLT-07')
        ->and($ticket->location)->toBe('Port 3');

    $this->assertDatabaseMissing('ticket_activities', ['ticket_id' => $ticket->id, 'action' => 'priority_change']);
});

test('lapangan lowering the priority does not lift an SLA breach', function () {
    $deadline = now()->subHour()->startOfSecond();
    $this->ticket->update(['sla_deadline' => $deadline, 'sla_status' => Ticket::SLA_BREACHED]);

    $this->actingAs($this->tech)->putJson("/tickets/{$this->ticket->id}", [
        'priority' => 'Low',
        'status' => 'Open',
    ])->assertOk();

    $ticket = $this->ticket->fresh();

    expect($ticket->priority)->toBe('High')
        ->and($ticket->sla_status)->toBe(Ticket::SLA_BREACHED)
        ->and($ticket->sla_deadline->equalTo($deadline))->toBeTrue();
});

test('lapangan cannot replace the attachment', function () {
    Storage::fake('local');

    $this->actingAs($this->tech)->put("/tickets/{$this->ticket->id}", [
        'status' => 'Open',
        'attachment' => UploadedFile::fake()->create('foto.pdf', 10, 'application/pdf'),
    ])->assertRedirect();

    expect($this->ticket->fresh()->attachment_path)->toBeNull();
});

test('lapangan status changes still follow the workflow rules', function () {
    $this->actingAs($this->tech)->putJson("/tickets/{$this->ticket->id}", ['status' => 'Solved'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['resolution_note']);

    expect($this->ticket->fresh()->status)->toBe('Open');
});

test('the edit form and quick view hide the core fields for lapangan only', function () {
    $this->actingAs($this->tech)->get("/tickets/{$this->ticket->id}/edit")
        ->assertOk()
        ->assertDontSee('name="title"', false)
        ->assertDontSee('name="customer_id"', false)
        ->assertDontSee('name="priority"', false)
        ->assertSee('name="olt"', false)
        ->assertSee('name="location"', false);

    $this->actingAs($this->admin)->get("/tickets/{$this->ticket->id}/edit")
        ->assertOk()
        ->assertSee('name="title"', false)
        ->assertSee('name="customer_id"', false)
        ->assertSee('name="priority"', false);

    $this->actingAs($this->tech)->getJson("/tickets/{$this->ticket->id}/quick-details")
        ->assertOk()
        ->assertJsonPath('can.update', true)
        ->assertJsonPath('can.update_details', false);

    $this->actingAs($this->admin)->getJson("/tickets/{$this->ticket->id}/quick-details")
        ->assertJsonPath('can.update_details', true);
});

test('admin can still edit every field', function () {
    $this->actingAs($this->admin)->put("/tickets/{$this->ticket->id}", [
        'customer_id' => $this->otherCustomer->id,
        'title' => 'Diubah admin',
        'description' => 'Baru',
        'category' => 'WhatsApp',
        'priority' => 'Low',
        'status' => 'Open',
    ])->assertSessionHasNoErrors();

    $ticket = $this->ticket->fresh();

    expect($ticket->title)->toBe('Diubah admin')
        ->and($ticket->customer_id)->toBe($this->otherCustomer->id)
        ->and($ticket->category)->toBe('WhatsApp')
        ->and($ticket->priority)->toBe('Low');
});
