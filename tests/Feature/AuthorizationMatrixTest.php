<?php

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();

    $this->users = [
        'admin' => User::factory()->create(['role' => 'admin']),
        'cs' => User::factory()->create(['role' => 'cs']),
        'lapangan' => User::factory()->create(['role' => 'lapangan']),
    ];

    $this->customer = Customer::create([
        'customer_id' => 'C-AUTH',
        'name' => 'Dewi Lestari',
        'email' => 'dewi@example.test',
        'phone' => '0812-0000-1111',
        'address' => 'Jl. Anggrek No. 3',
        'package' => 'Home 50 Mbps',
    ]);

    $makeTicket = fn (array $attributes) => Ticket::create(array_merge([
        'customer_id' => $this->customer->id,
        'created_by' => $this->users['cs']->id,
        'description' => 'Deskripsi',
        'category' => 'Email',
        'priority' => 'Medium',
        'status' => 'Open',
    ], $attributes));

    // Not assigned to / created by the lapangan user.
    $this->otherTicket = $makeTicket(['ticket_number' => 'TKT-OTHER', 'title' => 'Tiket orang lain']);
    // Assigned to the lapangan user.
    $this->assignedTicket = $makeTicket([
        'ticket_number' => 'TKT-ASSIGNED',
        'title' => 'Tiket tugas lapangan',
        'assigned_to' => $this->users['lapangan']->id,
    ]);
});

function matrixTicketPayload(Ticket $ticket, array $overrides = []): array
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

function matrixCustomerPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Pelanggan Baru',
        'email' => 'baru@example.test',
        'phone' => '0812-9999-0000',
        'address' => 'Jl. Baru',
        'package' => 'Home 20 Mbps',
    ], $overrides);
}

// ---------------------------------------------------------------- tickets

test('ticket actions follow the role matrix', function (string $role, string $action, bool $allowed) {
    $user = $this->users[$role];
    $ticket = $this->otherTicket;

    $response = match ($action) {
        'index' => $this->actingAs($user)->get('/tickets'),
        'show' => $this->actingAs($user)->get("/tickets/{$ticket->id}"),
        'create' => $this->actingAs($user)->get('/tickets/create'),
        'store' => $this->actingAs($user)->post('/tickets', [
            'customer_id' => $this->customer->id,
            'title' => 'Tiket baru',
            'description' => 'Deskripsi',
        ]),
        'edit' => $this->actingAs($user)->get("/tickets/{$ticket->id}/edit"),
        'update' => $this->actingAs($user)->put("/tickets/{$ticket->id}", matrixTicketPayload($ticket, ['title' => 'Diubah'])),
        'assign' => $this->actingAs($user)->patch("/tickets/{$ticket->id}/assignee", ['assigned_to' => $this->users['cs']->id]),
        'audit-log' => $this->actingAs($user)->get("/tickets/{$ticket->id}/audit-log"),
        'reply' => $this->actingAs($user)->post("/tickets/{$ticket->id}/messages", ['message' => 'Halo']),
        'destroy' => $this->actingAs($user)->delete("/tickets/{$ticket->id}"),
        'restore' => tap($this, fn () => $ticket->delete())->actingAs($user)->patch("/tickets/{$ticket->id}/restore"),
        'force-delete' => tap($this, fn () => $ticket->delete())->actingAs($user)->delete("/tickets/{$ticket->id}/force-delete"),
        'export' => $this->actingAs($user)->get('/tickets/export/csv'),
    };

    if ($allowed) {
        expect($response->getStatusCode())->toBeIn([200, 302]);
    } else {
        $response->assertForbidden();
    }
})->with([
    ['admin', 'index', true], ['cs', 'index', true], ['lapangan', 'index', true],
    ['admin', 'show', true], ['cs', 'show', true], ['lapangan', 'show', false],
    ['admin', 'create', true], ['cs', 'create', true], ['lapangan', 'create', false],
    ['admin', 'store', true], ['cs', 'store', true], ['lapangan', 'store', false],
    ['admin', 'edit', true], ['cs', 'edit', true], ['lapangan', 'edit', false],
    ['admin', 'update', true], ['cs', 'update', true], ['lapangan', 'update', false],
    ['admin', 'assign', true], ['cs', 'assign', true], ['lapangan', 'assign', false],
    ['admin', 'audit-log', true], ['cs', 'audit-log', true], ['lapangan', 'audit-log', false],
    ['admin', 'reply', true], ['cs', 'reply', true], ['lapangan', 'reply', false],
    ['admin', 'destroy', true], ['cs', 'destroy', false], ['lapangan', 'destroy', false],
    ['admin', 'restore', true], ['cs', 'restore', false], ['lapangan', 'restore', false],
    ['admin', 'force-delete', true], ['cs', 'force-delete', false], ['lapangan', 'force-delete', false],
    ['admin', 'export', true], ['cs', 'export', true], ['lapangan', 'export', true],
]);

test('lapangan can view, update and reply on tickets assigned to them', function () {
    $user = $this->users['lapangan'];
    $ticket = $this->assignedTicket;

    $this->actingAs($user)->get("/tickets/{$ticket->id}")->assertOk();
    $this->actingAs($user)->get("/tickets/{$ticket->id}/edit")->assertOk();
    $this->actingAs($user)
        ->put("/tickets/{$ticket->id}", matrixTicketPayload($ticket, ['status' => 'Checking']))
        ->assertRedirect(route('tickets.show', $ticket->id));
    $this->actingAs($user)
        ->post("/tickets/{$ticket->id}/messages", ['message' => 'Sudah dicek di lokasi'])
        ->assertRedirect();

    expect($ticket->fresh()->status)->toBe('Checking');
});

test('lapangan can view tickets they created', function () {
    $own = Ticket::create([
        'ticket_number' => 'TKT-OWN',
        'customer_id' => $this->customer->id,
        'created_by' => $this->users['lapangan']->id,
        'title' => 'Dibuat teknisi',
        'description' => 'x',
        'status' => 'Open',
    ]);

    $this->actingAs($this->users['lapangan'])->get("/tickets/{$own->id}")->assertOk();
});

test('ticket index, my tickets and dashboard are scoped to own tickets for lapangan', function () {
    $user = $this->users['lapangan'];

    $this->actingAs($user)->get('/tickets')
        ->assertOk()
        ->assertSee('Tiket tugas lapangan')
        ->assertDontSee('Tiket orang lain');

    $this->actingAs($user)->get('/my-tickets')
        ->assertOk()
        ->assertSee('Tiket tugas lapangan')
        ->assertDontSee('Tiket orang lain');

    $this->actingAs($user)->get('/dashboard')
        ->assertOk()
        ->assertDontSee('Tiket orang lain');

    $this->actingAs($this->users['cs'])->get('/tickets')
        ->assertSee('Tiket tugas lapangan')
        ->assertSee('Tiket orang lain');
});

test('ticket exports are scoped to own tickets for lapangan', function () {
    $csv = $this->actingAs($this->users['lapangan'])->get('/tickets/export/csv')->streamedContent();

    expect($csv)->toContain('TKT-ASSIGNED')->not->toContain('TKT-OTHER');

    $this->actingAs($this->users['lapangan'])->get('/tickets/export/pdf')
        ->assertOk()
        ->assertSee('TKT-ASSIGNED')
        ->assertDontSee('TKT-OTHER');

    $csv = $this->actingAs($this->users['admin'])->get('/tickets/export/csv')->streamedContent();

    expect($csv)->toContain('TKT-ASSIGNED')->toContain('TKT-OTHER');
});

test('exports are rate limited', function () {
    $user = $this->users['cs'];

    for ($i = 0; $i < 5; $i++) {
        $this->actingAs($user)->get('/tickets/export/pdf')->assertOk();
    }

    $this->actingAs($user)->get('/tickets/export/pdf')->assertStatus(429);
});

test('new tickets always start as Open', function () {
    $this->actingAs($this->users['cs'])->post('/tickets', [
        'customer_id' => $this->customer->id,
        'title' => 'Langsung ditutup',
        'description' => 'x',
        'status' => 'Closed',
    ])->assertSessionHasErrors('status');

    expect(Ticket::where('title', 'Langsung ditutup')->exists())->toBeFalse();

    $this->actingAs($this->users['cs'])->post('/tickets', [
        'customer_id' => $this->customer->id,
        'title' => 'Tiket tanpa status',
        'description' => 'x',
    ])->assertRedirect();

    expect(Ticket::where('title', 'Tiket tanpa status')->value('status'))->toBe('Open');
});

// ---------------------------------------------------------------- customers

test('customer actions follow the role matrix', function (string $role, string $action, bool $allowed) {
    $user = $this->users[$role];
    $customer = Customer::create(matrixCustomerPayload(['customer_id' => 'C-MATRIX', 'name' => 'Target']));

    $response = match ($action) {
        'index' => $this->actingAs($user)->get('/customers'),
        'show' => $this->actingAs($user)->get("/customers/{$customer->id}"),
        'create' => $this->actingAs($user)->get('/customers/create'),
        'store' => $this->actingAs($user)->post('/customers', matrixCustomerPayload()),
        'edit' => $this->actingAs($user)->get("/customers/{$customer->id}/edit"),
        'update' => $this->actingAs($user)->put("/customers/{$customer->id}", matrixCustomerPayload(['name' => 'Diubah'])),
        'destroy' => $this->actingAs($user)->delete("/customers/{$customer->id}"),
        'restore' => tap($this, fn () => $customer->delete())->actingAs($user)->patch("/customers/{$customer->id}/restore"),
        'force-delete' => tap($this, fn () => $customer->delete())->actingAs($user)->delete("/customers/{$customer->id}/force-delete"),
    };

    if ($allowed) {
        expect($response->getStatusCode())->toBeIn([200, 302]);
    } else {
        $response->assertForbidden();
    }
})->with([
    ['admin', 'index', true], ['cs', 'index', true], ['lapangan', 'index', true],
    ['admin', 'show', true], ['cs', 'show', true], ['lapangan', 'show', true],
    ['admin', 'create', true], ['cs', 'create', true], ['lapangan', 'create', false],
    ['admin', 'store', true], ['cs', 'store', true], ['lapangan', 'store', false],
    ['admin', 'edit', true], ['cs', 'edit', true], ['lapangan', 'edit', false],
    ['admin', 'update', true], ['cs', 'update', true], ['lapangan', 'update', false],
    ['admin', 'destroy', true], ['cs', 'destroy', false], ['lapangan', 'destroy', false],
    ['admin', 'restore', true], ['cs', 'restore', false], ['lapangan', 'restore', false],
    ['admin', 'force-delete', true], ['cs', 'force-delete', false], ['lapangan', 'force-delete', false],
]);

test('a customer that still has tickets cannot be force deleted', function () {
    $this->customer->delete();

    $this->actingAs($this->users['admin'])
        ->delete("/customers/{$this->customer->id}/force-delete")
        ->assertRedirect(route('customers.index'))
        ->assertSessionHas('error');

    expect(Customer::withTrashed()->find($this->customer->id))->not->toBeNull()
        ->and(Ticket::count())->toBe(2);
});

test('hard-deleting a customer with tickets is rejected by the database', function () {
    expect(fn () => $this->customer->forceDelete())
        ->toThrow(QueryException::class);

    expect(Ticket::count())->toBe(2);
});

// ---------------------------------------------------------------- internal notes

test('internal notes are stored as internal and never emailed', function () {
    $this->actingAs($this->users['cs'])->post("/tickets/{$this->otherTicket->id}/messages", [
        'message' => 'Catatan rahasia',
        'is_internal' => '1',
    ])->assertRedirect();

    $this->assertDatabaseHas('ticket_messages', [
        'ticket_id' => $this->otherTicket->id,
        'message' => 'Catatan rahasia',
        'is_internal' => true,
        'type' => 'internal',
    ]);

    Mail::assertNothingSent();
    Mail::assertNothingQueued();
});

test('message validation rejects invalid input', function () {
    $this->actingAs($this->users['cs'])
        ->from("/tickets/{$this->otherTicket->id}")
        ->post("/tickets/{$this->otherTicket->id}/messages", [
            'message' => str_repeat('a', 2001),
            'type' => 'broadcast',
            'is_internal' => 'maybe',
        ])
        ->assertSessionHasErrors(['message', 'type', 'is_internal']);

    expect($this->otherTicket->messages()->count())->toBe(0);
});

test('a user without a valid role cannot add internal notes', function () {
    $user = $this->users['cs'];
    $user->role = null;

    $this->actingAs($user)->post("/tickets/{$this->otherTicket->id}/messages", [
        'message' => 'x',
        'is_internal' => '1',
    ])->assertForbidden();
});
