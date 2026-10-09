<?php

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;

// Item 5: fetch()/XHR callers (Accept: application/json) get JSON 422/403 responses instead of a
// redirect or an HTML error page.

beforeEach(function () {
    $this->cs = User::factory()->create(['role' => 'cs']);
    $this->lapangan = User::factory()->create(['role' => 'lapangan']);
    $this->ticket = Ticket::factory()->for(Customer::factory())->create(['category' => 'Email']);
});

test('validation errors of the quick edit come back as 422 JSON with field errors', function () {
    $this->actingAs($this->cs)
        ->putJson("/tickets/{$this->ticket->id}", ['customer_id' => $this->ticket->customer_id, 'title' => '', 'description' => ''])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['title', 'description']);
});

test('validation errors of the quick message come back as 422 JSON', function () {
    $this->actingAs($this->cs)
        ->postJson("/tickets/{$this->ticket->id}/quick-message", ['message' => ''])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['message']);

    $this->actingAs($this->cs)
        ->postJson("/tickets/{$this->ticket->id}/quick-message", ['message' => str_repeat('a', 2001)])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['message']);
});

test('forbidden fetch requests get a 403 JSON response', function () {
    $this->actingAs($this->lapangan)
        ->putJson("/tickets/{$this->ticket->id}", ['status' => 'Checking'])
        ->assertForbidden()
        ->assertJsonStructure(['message']);

    $this->actingAs($this->lapangan)
        ->postJson("/tickets/{$this->ticket->id}/quick-message", ['message' => 'Halo'])
        ->assertForbidden()
        ->assertJsonStructure(['message']);
});

test('requests that only send Accept: application/json are treated as JSON', function () {
    $this->actingAs($this->cs)
        ->withHeaders(['Accept' => 'application/json'])
        ->patch("/tickets/{$this->ticket->id}/status", ['status' => 'Nonsense'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

test('regular form posts still redirect back with errors', function () {
    $this->actingAs($this->cs)
        ->from("/tickets/{$this->ticket->id}/edit")
        ->put("/tickets/{$this->ticket->id}", ['customer_id' => $this->ticket->customer_id, 'title' => ''])
        ->assertRedirect("/tickets/{$this->ticket->id}/edit")
        ->assertSessionHasErrors(['title']);
});
