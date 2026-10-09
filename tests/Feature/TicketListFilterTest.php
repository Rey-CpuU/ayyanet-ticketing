<?php

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->cs = User::factory()->create(['role' => 'cs']);
    $this->agent = User::factory()->create(['role' => 'cs', 'name' => 'Agen Rina']);

    $budi = Customer::factory()->create(['name' => 'Budi Santoso']);
    $sari = Customer::factory()->create(['name' => 'Sari Dewi']);

    $this->outage = Ticket::factory()->for($budi)->create([
        'ticket_number' => 'TKT-1001', 'title' => 'Gangguan fiber Perumnas',
        'status' => 'Open', 'priority' => 'High', 'category' => 'WhatsApp',
        'assigned_to' => $this->agent->id, 'created_at' => now()->subDays(3),
    ]);
    $this->billing = Ticket::factory()->for($sari)->create([
        'ticket_number' => 'TKT-1002', 'title' => 'Tagihan ganda',
        'status' => 'Checking', 'priority' => 'Low', 'category' => 'Email',
        'created_at' => now()->subDays(2),
    ]);
    $this->slow = Ticket::factory()->for($sari)->create([
        'ticket_number' => 'TKT-1003', 'title' => 'Internet lambat',
        'status' => 'Solved', 'priority' => 'Medium', 'category' => 'Email',
        'created_at' => now()->subDay(),
    ]);
});

function listedNumbers($response): array
{
    return $response->viewData('tickets')->pluck('ticket_number')->all();
}

test('tickets can be searched by number, title and customer name', function () {
    $search = fn (string $q) => listedNumbers($this->actingAs($this->cs)->get('/tickets?q='.urlencode($q))->assertOk());

    expect($search('TKT-1002'))->toBe(['TKT-1002'])
        ->and($search('fiber'))->toBe(['TKT-1001'])
        ->and($search('Sari'))->toEqualCanonicalizing(['TKT-1002', 'TKT-1003']);
});

test('tickets can be filtered by status, priority, category and assignee', function () {
    $get = fn (string $query) => listedNumbers($this->actingAs($this->cs)->get('/tickets?'.$query)->assertOk());

    expect($get('status=Checking'))->toBe(['TKT-1002'])
        ->and($get('priority=High'))->toBe(['TKT-1001'])
        ->and($get('category=Email'))->toEqualCanonicalizing(['TKT-1002', 'TKT-1003'])
        ->and($get('assigned_to='.$this->agent->id))->toBe(['TKT-1001'])
        ->and($get('assigned_to=unassigned'))->toEqualCanonicalizing(['TKT-1002', 'TKT-1003'])
        ->and($get('category=Email&status=Solved'))->toBe(['TKT-1003']);
});

test('invalid filter values are ignored', function () {
    expect(listedNumbers($this->actingAs($this->cs)->get('/tickets?status=Nonsense&sort=drop&assigned_to=abc')->assertOk()))
        ->toHaveCount(3);
});

test('tickets can be sorted', function () {
    $get = fn (string $sort) => listedNumbers($this->actingAs($this->cs)->get('/tickets?sort='.$sort));

    expect($get('newest'))->toBe(['TKT-1003', 'TKT-1002', 'TKT-1001'])
        ->and($get('oldest'))->toBe(['TKT-1001', 'TKT-1002', 'TKT-1003'])
        ->and($get('priority'))->toBe(['TKT-1001', 'TKT-1003', 'TKT-1002']);
});

test('pagination keeps the query string', function () {
    Ticket::factory()->count(20)->create(['category' => 'Portal']);

    $response = $this->actingAs($this->cs)->get('/tickets?category=Portal&sort=oldest')->assertOk();

    expect($response->viewData('tickets')->total())->toBe(20)
        ->and($response->viewData('tickets')->count())->toBe(15);
    $response->assertSee('category=Portal&amp;sort=oldest&amp;page=2', false);
});

test('field technicians stay scoped to their own tickets while searching', function () {
    $lapangan = User::factory()->create(['role' => 'lapangan']);
    $this->billing->update(['assigned_to' => $lapangan->id]);

    expect(listedNumbers($this->actingAs($lapangan)->get('/tickets?q=TKT')))->toBe(['TKT-1002'])
        ->and(listedNumbers($this->actingAs($lapangan)->get('/tickets?assigned_to='.$this->agent->id)))->toBe([]);
});

test('only admins can list deleted tickets, with restore and force delete actions', function () {
    $this->billing->delete();

    $admin = $this->actingAs($this->admin)->get('/tickets?trashed=only')->assertOk();
    expect(listedNumbers($admin))->toBe(['TKT-1002']);
    $admin->assertSee(route('tickets.restore', $this->billing->id))
        ->assertSee(route('tickets.force-delete', $this->billing->id))
        ->assertSee('Terhapus');

    expect(listedNumbers($this->actingAs($this->admin)->get('/tickets?trashed=with')))->toHaveCount(3);

    // For non-admins the filter is ignored: deleted tickets never show up.
    $cs = $this->actingAs($this->cs)->get('/tickets?trashed=only')->assertOk();
    expect(listedNumbers($cs))->toEqualCanonicalizing(['TKT-1001', 'TKT-1003']);
    $cs->assertDontSee(route('tickets.restore', $this->billing->id));
});

test('my tickets supports the same filters', function () {
    Ticket::factory()->create(['created_by' => $this->cs->id, 'ticket_number' => 'TKT-MINE-1', 'priority' => 'High']);
    Ticket::factory()->create(['created_by' => $this->cs->id, 'ticket_number' => 'TKT-MINE-2', 'priority' => 'Low']);

    expect(listedNumbers($this->actingAs($this->cs)->get('/my-tickets?priority=High')))->toBe(['TKT-MINE-1']);
});
