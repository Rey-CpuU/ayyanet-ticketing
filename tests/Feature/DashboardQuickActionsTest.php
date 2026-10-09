<?php

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();

    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->cs = User::factory()->create(['role' => 'cs']);
    $this->lapangan = User::factory()->create(['role' => 'lapangan']);

    $this->customer = Customer::create([
        'customer_id' => 'C-QA',
        'name' => 'Rina Kartika',
        'phone' => '0812-5555-0001',
        'address' => 'Jl. Mawar 1',
        'package' => 'Home 20 Mbps',
    ]);

    $make = fn (array $attributes) => Ticket::create(array_merge([
        'customer_id' => $this->customer->id,
        'created_by' => $this->cs->id,
        'description' => 'Deskripsi',
        'category' => 'Email',
        'priority' => 'Medium',
        'status' => 'Open',
        'sla_deadline' => now()->addHours(4),
        'sla_status' => Ticket::SLA_ACTIVE,
    ], $attributes));

    $this->other = $make(['ticket_number' => 'TKT-QA-OTHER', 'title' => 'Router mati total']);
    $this->own = $make(['ticket_number' => 'TKT-QA-OWN', 'title' => 'Kabel putus', 'assigned_to' => $this->lapangan->id]);
});

test('every main page renders the top-nav layout for each role', function (string $role, array $pages) {
    $user = $this->{$role};

    foreach ($pages as $page) {
        $url = str_replace(['{own}', '{customer}'], [$this->own->id, $this->customer->id], $page);

        $this->actingAs($user)->get($url)
            ->assertOk()
            ->assertSee('Support Desk')
            ->assertSee('nb-trigger', false)
            ->assertDontSee('class="sidebar"', false);
    }
})->with([
    'admin' => ['admin', ['/dashboard', '/tickets', '/my-tickets', '/tickets/create', '/tickets/{own}', '/tickets/{own}/edit', '/tickets/{own}/audit-log', '/customers', '/customers/create', '/customers/{customer}', '/customers/{customer}/edit', '/reports', '/settings']],
    'cs' => ['cs', ['/dashboard', '/tickets', '/tickets/create', '/tickets/{own}', '/customers', '/customers/{customer}', '/reports', '/settings']],
    'lapangan' => ['lapangan', ['/dashboard', '/tickets', '/my-tickets', '/tickets/{own}', '/tickets/{own}/edit', '/customers', '/customers/{customer}']],
]);

test('dashboard shows the queue, stats, recently visited panel and inline assignee for cs', function () {
    $this->actingAs($this->cs)->get('/dashboard')
        ->assertOk()
        ->assertSee('Ticket Queue')
        ->assertSee('Recently Visited')
        ->assertSee('belum ditugaskan')
        ->assertSee('data-current-assignee', false)
        ->assertSee('tickets\/live-search', false);
});

test('lapangan sees a read-only assignee chip and only own tickets on the dashboard', function () {
    $this->actingAs($this->lapangan)->get('/dashboard')
        ->assertOk()
        ->assertSee('Kabel putus')
        ->assertDontSee('Router mati total')
        ->assertDontSee('data-current-assignee', false);
});

test('live search is scoped to visible tickets', function () {
    $this->actingAs($this->cs)->getJson('/tickets/live-search?q=TKT-QA')
        ->assertOk()
        ->assertJsonCount(2);

    $this->actingAs($this->lapangan)->getJson('/tickets/live-search?q=TKT-QA')
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.ticket_number', 'TKT-QA-OWN');

    $this->actingAs($this->cs)->getJson('/tickets/live-search?q=')->assertOk()->assertExactJson([]);
});

test('quick details are authorized and expose allowed transitions and SLA', function () {
    $this->actingAs($this->lapangan)->getJson("/tickets/{$this->other->id}/quick-details")->assertForbidden();

    $this->actingAs($this->cs)->getJson("/tickets/{$this->other->id}/quick-details")
        ->assertOk()
        ->assertJsonPath('ticket_number', 'TKT-QA-OTHER')
        ->assertJsonPath('sla.label', 'Berjalan')
        ->assertJsonPath('can.update', true)
        ->assertJsonMissingPath('allowed_statuses.5');

    expect($this->other->fresh()->last_visited_at)->not->toBeNull();
});

test('quick message stores replies and internal notes with authorization', function () {
    $this->actingAs($this->cs)->postJson("/tickets/{$this->other->id}/quick-message", [
        'message' => 'Catatan rahasia',
        'is_internal' => 1,
    ])->assertOk()->assertJsonPath('message.is_internal', true);

    $this->assertDatabaseHas('ticket_messages', ['ticket_id' => $this->other->id, 'message' => 'Catatan rahasia', 'is_internal' => true]);
    $this->assertDatabaseHas('ticket_activities', ['ticket_id' => $this->other->id, 'action' => 'internal_note']);

    $this->actingAs($this->lapangan)->postJson("/tickets/{$this->other->id}/quick-message", ['message' => 'x'])
        ->assertForbidden();
});

test('inline assignment returns json and stays restricted to admin and cs', function () {
    $this->actingAs($this->cs)->patchJson("/tickets/{$this->other->id}/assignee", ['assigned_to' => $this->lapangan->id])
        ->assertOk()
        ->assertJsonPath('assignee.id', $this->lapangan->id);

    expect($this->other->fresh()->assigned_to)->toBe($this->lapangan->id);

    $this->actingAs($this->lapangan)->patchJson("/tickets/{$this->own->id}/assignee", ['assigned_to' => null])
        ->assertForbidden();
});

test('quick edit via json enforces the status workflow', function () {
    $payload = [
        'customer_id' => $this->other->customer_id,
        'title' => 'Router mati total (update)',
        'description' => 'Deskripsi',
        'category' => 'Email',
        'priority' => 'High',
        'status' => 'Solved',
    ];

    // Solved requires a resolution note.
    $this->actingAs($this->cs)->putJson("/tickets/{$this->other->id}", $payload)
        ->assertStatus(422)
        ->assertJsonPath('errors.resolution_note', fn ($m) => str_contains($m, 'wajib'));

    $this->actingAs($this->cs)->putJson("/tickets/{$this->other->id}", $payload + ['resolution_note' => 'Router diganti.'])
        ->assertOk()
        ->assertJsonPath('ticket.status', 'Solved')
        ->assertJsonPath('ticket.title', 'Router mati total (update)');
});
