<?php

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->cs = User::factory()->create(['role' => 'cs']);

    $this->budi = Customer::factory()->create([
        'customer_id' => 'C-0101', 'name' => 'Budi Santoso',
        'email' => 'budi@contoh.test', 'phone' => '0812-1111-0001',
    ]);
    $this->sari = Customer::factory()->create([
        'customer_id' => 'C-0102', 'name' => 'Sari Dewi',
        'email' => 'sari@contoh.test', 'phone' => '0813-2222-0002',
    ]);
});

function listedCustomers($response): array
{
    return $response->viewData('customers')->pluck('name')->all();
}

test('customers can be searched by name, email, phone and customer id', function () {
    $search = fn (string $q) => listedCustomers($this->actingAs($this->cs)->get('/customers?q='.urlencode($q))->assertOk());

    expect($search('budi'))->toBe(['Budi Santoso'])
        ->and($search('sari@contoh'))->toBe(['Sari Dewi'])
        ->and($search('2222'))->toBe(['Sari Dewi'])
        ->and($search('C-0101'))->toBe(['Budi Santoso'])
        ->and($search('tidak-ada'))->toBe([]);
});

test('customer list shows ticket counts and paginates with the query string', function () {
    Ticket::factory()->count(3)->for($this->budi)->create();
    Customer::factory()->count(20)->create();

    $response = $this->actingAs($this->cs)->get('/customers?q=example')->assertOk();
    $customers = $response->viewData('customers');

    expect($customers->count())->toBe(15);
    $response->assertSee('q=example&amp;page=2', false);

    $first = $this->actingAs($this->cs)->get('/customers?q=Budi')->viewData('customers')->first();
    expect($first->tickets_count)->toBe(3);
});

test('only admins can list deleted customers', function () {
    $this->sari->delete();

    $admin = $this->actingAs($this->admin)->get('/customers?trashed=only')->assertOk();
    expect(listedCustomers($admin))->toBe(['Sari Dewi']);
    $admin->assertSee(route('customers.restore', $this->sari->id))
        ->assertSee(route('customers.force-delete', $this->sari->id));

    expect(listedCustomers($this->actingAs($this->cs)->get('/customers?trashed=only')))->toBe(['Budi Santoso']);
});

test('customer search endpoint returns a limited json list without deleted customers', function () {
    Customer::factory()->count(25)->create(['name' => 'Pelanggan Massal']);
    $this->sari->delete();

    $this->actingAs($this->cs)->getJson('/customers/search?q=Massal')
        ->assertOk()
        ->assertJsonCount(20);

    $this->actingAs($this->cs)->getJson('/customers/search?q=Sari')
        ->assertOk()
        ->assertJsonCount(0);

    $this->actingAs($this->cs)->getJson('/customers/search?q=0812-1111')
        ->assertOk()
        ->assertJsonFragment(['id' => $this->budi->id, 'name' => 'Budi Santoso', 'customer_id' => 'C-0101']);
});

test('customer search endpoint is staff only', function () {
    $this->get('/customers/search?q=Budi')->assertRedirect('/login');

    $lapangan = User::factory()->create(['role' => 'lapangan']);
    $this->actingAs($lapangan)->getJson('/customers/search?q=Budi')->assertOk();
});

test('ticket forms no longer render every customer', function () {
    $this->actingAs($this->cs)->get('/tickets/create')
        ->assertOk()
        ->assertSee(route('customers.search'))
        ->assertDontSee('Sari Dewi');

    $this->actingAs($this->cs)->get('/tickets/create?customer_id='.$this->budi->id)
        ->assertSee('Budi Santoso')
        ->assertDontSee('Sari Dewi');

    $ticket = Ticket::factory()->for($this->sari)->create();

    $this->actingAs($this->cs)->get("/tickets/{$ticket->id}/edit")
        ->assertOk()
        ->assertSee('Sari Dewi')
        ->assertDontSee('Budi Santoso');
});
