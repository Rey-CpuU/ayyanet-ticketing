<?php

use App\Models\StatusBanner;

beforeEach(function () {
    $this->user = \App\Models\User::factory()->create();
});

test('only returns active banners within date window', function () {
    StatusBanner::create(['title' => 'Always on', 'message' => 'm', 'type' => 'info', 'is_active' => true]);
    StatusBanner::create(['title' => 'Future window', 'message' => 'm', 'type' => 'maintenance', 'is_active' => true, 'starts_at' => now()->addDay()]);
    StatusBanner::create(['title' => 'Expired', 'message' => 'm', 'type' => 'info', 'is_active' => true, 'ends_at' => now()->subDay()]);
    StatusBanner::create(['title' => 'Disabled', 'message' => 'm', 'type' => 'info', 'is_active' => false]);

    $titles = StatusBanner::active()->pluck('title')->all();

    expect($titles)->toBe(['Always on']);
});

test('store creates a banner', function () {
    $this->actingAs($this->user)->post('/status-banners', [
        'title'     => 'Gangguan Area',
        'message'   => 'Tim sedang menangani',
        'type'      => 'outage',
        'is_active' => 1,
    ])->assertRedirect('/status-banners');

    $this->assertDatabaseHas('status_banners', [
        'title'     => 'Gangguan Area',
        'type'      => 'outage',
        'is_active' => true,
    ]);
});

test('store defaults is_active to false when checkbox absent', function () {
    $this->actingAs($this->user)->post('/status-banners', [
        'title'   => 'Draft',
        'message' => 'd',
        'type'    => 'info',
    ])->assertRedirect('/status-banners');

    expect(StatusBanner::where('title', 'Draft')->first()->is_active)->toBeFalse();
});

test('store validates required fields', function () {
    $this->actingAs($this->user)
        ->post('/status-banners', [])
        ->assertSessionHasErrors(['title', 'message', 'type']);
});

test('store requires authentication', function () {
    $this->post('/status-banners', ['title' => 'x', 'message' => 'y', 'type' => 'info'])
        ->assertRedirect('/login');
});

test('update modifies a banner', function () {
    $banner = StatusBanner::create(['title' => 'Old', 'message' => 'm', 'type' => 'info']);

    $this->actingAs($this->user)->put("/status-banners/{$banner->id}", [
        'title'     => 'New',
        'message'   => 'updated',
        'type'      => 'maintenance',
        'is_active' => false,
    ])->assertRedirect('/status-banners');

    expect($banner->fresh())
        ->title->toBe('New')
        ->type->toBe('maintenance')
        ->is_active->toBeFalse();
});

test('destroy deletes a banner', function () {
    $banner = StatusBanner::create(['title' => 'Old', 'message' => 'm', 'type' => 'info']);

    $this->actingAs($this->user)->delete("/status-banners/{$banner->id}")
        ->assertRedirect('/status-banners');

    expect(StatusBanner::find($banner->id))->toBeNull();
});

test('active endpoint returns active banners as json', function () {
    StatusBanner::create(['title' => 'Live', 'message' => 'hello', 'type' => 'outage', 'is_active' => true]);
    StatusBanner::create(['title' => 'Dead', 'message' => 'x', 'type' => 'info', 'is_active' => false]);

    $this->get('/status-banners/active')
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['title' => 'Live', 'type' => 'outage']);
});

test('classify endpoint returns category and priority', function () {
    $this->post('/tickets/classify', ['title' => 'Internet sering putus di malam hari'])
        ->assertOk()
        ->assertJson(['category' => 'Internet', 'priority' => 'Medium']);
});
