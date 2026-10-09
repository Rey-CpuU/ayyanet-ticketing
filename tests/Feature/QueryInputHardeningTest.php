<?php

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;

// Item 3: array/garbage query parameters never cause a 500, LIKE wildcards are escaped and
// searches are case-insensitive; non-numeric ids are a 404.

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->customer = Customer::factory()->create(['name' => 'Budi Santoso', 'phone' => '0812-0000-1111']);

    $this->discount = Ticket::factory()->for($this->customer)->create([
        'ticket_number' => 'TKT-5001', 'title' => 'Diskon 100% tidak masuk', 'category' => 'Email',
    ]);
    $this->underscore = Ticket::factory()->for($this->customer)->create([
        'ticket_number' => 'TKT-5002', 'title' => 'Port_A rusak', 'category' => 'Email',
    ]);
    $this->plain = Ticket::factory()->for($this->customer)->create([
        'ticket_number' => 'TKT-5003', 'title' => 'Gangguan Fiber', 'category' => 'Email',
    ]);
});

test('array query parameters are ignored instead of failing', function (string $url) {
    $this->actingAs($this->admin)->get($url)->assertOk();
})->with([
    'ticket list' => ['/tickets?q[]=x&status[]=Open&priority[]=High&category[]=Email&assigned_to[]=1&sort[]=oldest&trashed[]=with'],
    'my tickets' => ['/my-tickets?q[]=x&category[]=Email'],
    'dashboard' => ['/dashboard?search[]=x&status[]=Open&priority[]=High&channel[]=Email'],
    'customer list' => ['/customers?q[]=x&trashed[]=with'],
    'customer search' => ['/customers/search?q[]=x'],
    'live search' => ['/tickets/live-search?q[]=x'],
    'create form' => ['/tickets/create?customer_id[]=1'],
    'reports' => ['/reports?from[]=2026-01-01&to[]=2026-12-31'],
    'csv export' => ['/tickets/export/csv?q[]=x&status[]=Open'],
    'pdf export' => ['/tickets/export/pdf?q[]=x&category[]=Email'],
]);

test('the create form ignores a non-numeric customer id', function () {
    $this->actingAs($this->admin)->get('/tickets/create?customer_id=abc')
        ->assertOk()
        ->assertViewHas('selectedCustomer', null);

    $this->actingAs($this->admin)->get('/tickets/create?customer_id='.$this->customer->id)
        ->assertOk()
        ->assertViewHas('selectedCustomer', fn ($c) => $c?->is($this->customer));
});

test('invalid report dates are ignored with a message', function () {
    $this->actingAs($this->admin)->get('/reports?from=bukan-tanggal&to=2026-02-31')
        ->assertOk()
        ->assertViewHas('from', null)
        ->assertViewHas('to', null)
        ->assertSee('Format tanggal tidak valid');

    $this->actingAs($this->admin)->get('/reports?from=2020-01-01&to=2099-12-31')
        ->assertOk()
        ->assertViewHas('from', '2020-01-01')
        ->assertViewHas('metrics', fn ($m) => $m['total'] === 3);
});

test('LIKE wildcards in a search are matched literally', function () {
    $numbers = fn (string $q) => $this->actingAs($this->admin)->get('/tickets?q='.urlencode($q))
        ->assertOk()->viewData('tickets')->pluck('ticket_number')->all();

    expect($numbers('%'))->toBe(['TKT-5001'])
        ->and($numbers('_'))->toBe(['TKT-5002'])
        ->and($numbers('100%'))->toBe(['TKT-5001'])
        ->and($numbers('Port_A'))->toBe(['TKT-5002']);

    $this->actingAs($this->admin)->getJson('/tickets/live-search?q='.urlencode('%'))
        ->assertOk()->assertJsonCount(1)->assertJsonPath('0.ticket_number', 'TKT-5001');

    $this->actingAs($this->admin)->getJson('/customers/search?q='.urlencode('%'))
        ->assertOk()->assertJsonCount(0);
});

test('searches are case-insensitive', function () {
    $this->actingAs($this->admin)->get('/tickets?q=GANGGUAN')
        ->assertOk()
        ->assertViewHas('tickets', fn ($t) => $t->pluck('ticket_number')->all() === ['TKT-5003']);

    $this->actingAs($this->admin)->get('/dashboard?search=budi santoso')
        ->assertOk()
        ->assertViewHas('tickets', fn ($t) => $t->total() === 3);

    $this->actingAs($this->admin)->getJson('/customers/search?q=BUDI')
        ->assertOk()->assertJsonCount(1)->assertJsonPath('0.name', 'Budi Santoso');

    $this->actingAs($this->admin)->getJson('/tickets/live-search?q=fiber')
        ->assertOk()->assertJsonCount(1)->assertJsonPath('0.ticket_number', 'TKT-5003');
});

test('non-numeric ticket and customer ids are a 404', function (string $url) {
    $this->actingAs($this->admin)->get($url)->assertNotFound();
})->with([
    ['/tickets/abc'],
    ['/tickets/1abc/edit'],
    ['/tickets/abc/quick-details'],
    ['/tickets/abc/audit-log'],
    ['/customers/abc'],
    ['/customers/abc/edit'],
]);
