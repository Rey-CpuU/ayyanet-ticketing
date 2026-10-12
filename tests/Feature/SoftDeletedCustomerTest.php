<?php

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;

// Item 9: tickets keep their soft-deleted customer (shown with "(dihapus)"), pickers exclude
// deleted customers and a customer with unresolved tickets cannot be deleted.

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->customer = Customer::factory()->create(['name' => 'Dewi Lestari']);
    $this->ticket = Ticket::factory()->for($this->customer)->create(['status' => 'Solved', 'category' => 'Email']);
});

test('a ticket still shows its soft-deleted customer with a marker', function () {
    $this->customer->delete();
    $ticket = $this->ticket->fresh();

    expect($ticket->customer)->not->toBeNull()
        ->and($ticket->customer->trashed())->toBeTrue()
        ->and(Ticket::with('customer')->find($ticket->id)->customer?->name)->toBe('Dewi Lestari');

    $this->actingAs($this->admin)->get('/tickets')
        ->assertOk()
        ->assertSee('Dewi Lestari')
        ->assertSee('(dihapus)');

    $this->actingAs($this->admin)->get("/tickets/{$ticket->id}")
        ->assertOk()
        ->assertSee('Dewi Lestari')
        ->assertSee('(dihapus)')
        ->assertDontSee(route('customers.show', $this->customer));

    $this->actingAs($this->admin)->get('/dashboard')
        ->assertOk()
        ->assertSee('(dihapus)');

    $this->actingAs($this->admin)->getJson("/tickets/{$ticket->id}/quick-details")
        ->assertOk()
        ->assertJsonPath('customer.name', 'Dewi Lestari')
        ->assertJsonPath('customer.deleted', true);

    $this->actingAs($this->admin)->getJson('/tickets/live-search?q=Dewi')
        ->assertOk()
        ->assertJsonPath('0.customer_name', 'Dewi Lestari')
        ->assertJsonPath('0.customer_deleted', true);
});

test('customer pickers and new tickets exclude soft-deleted customers', function () {
    $this->customer->delete();

    $this->actingAs($this->admin)->getJson('/customers/search?q=Dewi')
        ->assertOk()
        ->assertJsonCount(0);

    $this->actingAs($this->admin)->post('/tickets', [
        'customer_id' => $this->customer->id,
        'title' => 'Tiket baru',
        'description' => 'x',
    ])->assertSessionHasErrors('customer_id');
});

test('editing a ticket may keep its soft-deleted customer', function () {
    $this->customer->delete();

    $this->actingAs($this->admin)->put("/tickets/{$this->ticket->id}", [
        'customer_id' => $this->customer->id,
        'title' => 'Judul diperbarui',
        'description' => 'x',
        'status' => 'Solved',
        'category' => 'Email',
    ])->assertSessionHasNoErrors();

    expect($this->ticket->fresh()->title)->toBe('Judul diperbarui');
});

test('a customer with unresolved tickets cannot be deleted', function () {
    $open = Ticket::factory()->for($this->customer)->create(['status' => 'Checking']);

    $this->actingAs($this->admin)->delete("/customers/{$this->customer->id}")
        ->assertRedirect(route('customers.index'))
        ->assertSessionHas('error', fn ($message) => str_contains($message, 'belum selesai'));

    expect($this->customer->fresh()->trashed())->toBeFalse();

    $open->update(['status' => 'Closed']);

    $this->actingAs($this->admin)->delete("/customers/{$this->customer->id}")
        ->assertRedirect(route('customers.index'))
        ->assertSessionHas('success');

    expect($this->customer->fresh()->trashed())->toBeTrue();
});
