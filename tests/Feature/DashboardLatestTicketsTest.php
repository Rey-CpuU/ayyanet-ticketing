<?php

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;

use function Pest\Laravel\{actingAs, get};

test('shows the most recently visited tickets at the top, regardless of pagination', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $customer = Customer::create([
        'name' => 'Dashboard Customer',
        'phone' => '0812-0000-0000',
        'address' => 'Jl. Melati No. 1',
        'customer_id' => 'CUS-DASH-1',
    ]);

    // Create three tickets with distinct titles. A is created first, then C, then B
    // is created last — but B is the one we actually visit, so it must surface first.
    $ticketA = Ticket::create([
        'ticket_number' => 'TCK-DASH-A',
        'customer_id' => $customer->id,
        'created_by' => $admin->id,
        'title' => 'Ticket A title',
        'description' => 'Oldest ticket, never visited',
        'category' => 'Internet',
        'priority' => 'Medium',
        'status' => 'Open',
    ]);

    // A small gap so created_at ordering is deterministic across the three.
    usleep(1100); // ~1.1ms — above MySQL DATETIME second-resolution granularity within a query

    $ticketC = Ticket::create([
        'ticket_number' => 'TCK-DASH-C',
        'customer_id' => $customer->id,
        'created_by' => $admin->id,
        'title' => 'Ticket C title',
        'description' => 'Created after A, never visited',
        'category' => 'Internet',
        'priority' => 'Medium',
        'status' => 'Open',
    ]);

    usleep(1100);

    $ticketB = Ticket::create([
        'ticket_number' => 'TCK-DASH-B',
        'customer_id' => $customer->id,
        'created_by' => $admin->id,
        'title' => 'Ticket B title',
        'description' => 'Visited ticket, should appear first',
        'category' => 'Internet',
        'priority' => 'Medium',
        'status' => 'Open',
    ]);

    // Visiting ticket B stamps last_visited_at on it via touchVisited().
    actingAs($admin)->get(route('tickets.show', $ticketB))->assertOk();

    expect($ticketB->fresh()->last_visited_at)->not->toBeNull();

    // The dashboard's "Recently Visited" panel must reflect global visit order,
    // not whatever happens to land on the current pagination page.
    $response = actingAs($admin)->get(route('dashboard'));

    $response->assertStatus(200);

    // B was visited (has last_visited_at) so it leads. A and C were never visited,
    // so they fall back to created_at — and C was created after A. Thus the
    // expected order across the whole panel is B, C, A.
    $response->assertSeeInOrder(['Ticket B title', 'Ticket C title', 'Ticket A title']);
});

test('limits the Recently Visited panel to 5 tickets', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $customer = Customer::create([
        'name' => 'Limit Customer',
        'phone' => '0812-0000-0001',
        'address' => 'Jl. Kenanga No. 2',
        'customer_id' => 'CUS-LIMIT-1',
    ]);

    // Create 8 tickets, then visit 6 of them so that 6 would be eligible for the
    // Recently Visited panel. The panel must cap at exactly 5 — never 6.
    $tickets = collect();
    for ($i = 1; $i <= 8; $i++) {
        $tickets[] = Ticket::create([
            'ticket_number' => 'TCK-LIMIT-' . $i,
            'customer_id' => $customer->id,
            'created_by' => $admin->id,
            'title' => "Limit ticket {$i}",
            'description' => "Limit ticket description {$i}",
            'category' => 'Internet',
            'priority' => 'Medium',
            'status' => 'Open',
        ]);
        usleep(1100);
    }

    // Visit the first 6 — each GET stamps last_visited_at via touchVisited().
    foreach ($tickets->take(6) as $ticket) {
        actingAs($admin)->get(route('tickets.show', $ticket))->assertOk();
    }

    $response = actingAs($admin)->get(route('dashboard'));

    $response->assertStatus(200);

    // Each row in the Recently Visited panel renders one quickChatPopover(...) call.
    // The panel must show at most 5 rows even when 6 tickets have been visited.
    $html = $response->getContent();
    expect(substr_count($html, 'quickChatPopover('))->toBe(5);
});
