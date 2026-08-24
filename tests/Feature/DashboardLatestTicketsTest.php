<?php

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Carbon;

use function Pest\Laravel\{actingAs, get};

test('shows the most recently visited tickets at the top, regardless of pagination', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $customer = Customer::create([
        'name' => 'Dashboard Customer',
        'phone' => '0812-0000-0000',
        'address' => 'Jl. Melati No. 1',
        'customer_id' => 'CUS-DASH-1',
    ]);

    // Create three tickets with distinct titles and explicit created_at
    // timestamps spaced 1 second apart. SQLite DATETIME has 1-second
    // resolution, so we use Carbon::setTestNow() to control Eloquent's
    // timestamp generation (created_at is not in $fillable, so we can't
    // pass it directly — we must freeze time instead).
    // A is created first (oldest), then C, then B — but B is the one we
    // actually visit, so it must surface first in the panel.
    $realNow = now();
    Carbon::setTestNow($realNow->copy()->subMinutes(10)->subSeconds(2));
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

    Carbon::setTestNow($realNow->copy()->subMinutes(10)->subSecond());
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

    Carbon::setTestNow($realNow->copy()->subMinutes(10));
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

    // Restore real time so touchVisited() stamps last_visited_at as "now"
    // — which is more recent than all three created_at values.
    Carbon::setTestNow();


    // Visiting ticket B stamps last_visited_at on it via touchVisited().
    actingAs($admin)->get(route('tickets.show', $ticketB))->assertOk();

    expect($ticketB->fresh()->last_visited_at)->not->toBeNull();

    // The dashboard's "Recently Visited" panel must reflect global visit order,
    // not whatever happens to land on the current pagination page.
    $response = actingAs($admin)->get(route('dashboard'));

    $response->assertStatus(200);

    // The dashboard renders two ticket sections: the main paginated list
    // and the "Recently Visited" panel. We isolate the panel by looking
    // for its x-data=quickChatPopover rows — each panel row has a unique
    // x-data attribute with the ticket ID. We extract ticket IDs in order
    // from the panel to verify B comes before C and A.
    $html = $response->getContent();
    // Match x-data="quickChatPopover(ID, ...)" to extract ticket IDs in render order.
    preg_match_all('/x-data="quickChatPopover\((\d+),/', $html, $matches);
    $panelTicketIds = $matches[1]; // e.g. ['3', '2', '1'] — IDs in panel order

    // B was visited (has last_visited_at) so it must appear first in the panel.
    // A and C were never visited, so they fall back to created_at — C was
    // created after A, so C comes before A. Expected order: B, C, A.
    expect($panelTicketIds[0])->toBe((string) $ticketB->id)
        ->and($panelTicketIds[1])->toBe((string) $ticketC->id)
        ->and($panelTicketIds[2])->toBe((string) $ticketA->id);
});

test('limits the Recently Visited panel to 5 tickets', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $customer = Customer::create([
        'name' => 'Limit Customer',
        'phone' => '0812-0000-0001',
        'address' => 'Jl. Kenanga No. 2',
        'customer_id' => 'CUS-LIMIT-1',
    ]);

    // Create 8 tickets with explicit created_at timestamps spaced 1 second
    // apart (SQLite DATETIME has 1-second resolution; created_at is not in
    // $fillable so we use Carbon::setTestNow()). Visit 6 of them so that 6
    // would be eligible for the Recently Visited panel. The panel must cap
    // at exactly 5 — never 6.
    $tickets = collect();
    $realNow = now();
    for ($i = 1; $i <= 8; $i++) {
        Carbon::setTestNow($realNow->copy()->subMinutes(30)->subSeconds(8 - $i));
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
    }
    Carbon::setTestNow();

    // Visit the first 6 — each GET stamps last_visited_at via touchVisited().
    foreach ($tickets->take(6) as $ticket) {
        actingAs($admin)->get(route('tickets.show', $ticket))->assertOk();
    }

    $response = actingAs($admin)->get(route('dashboard'));

    $response->assertStatus(200);

    // Each row in the Recently Visited panel renders one x-data="quickChatPopover(...)" call.
    // We count x-data= occurrences (not bare quickChatPopover) to avoid matching
    // the <script> function definition at the bottom of the page.
    // The panel must show at most 5 rows even when 6 tickets have been visited.
    $html = $response->getContent();
    expect(substr_count($html, 'x-data="quickChatPopover('))->toBe(5);
});
