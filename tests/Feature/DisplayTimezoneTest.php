<?php

use App\Models\Customer;
use App\Models\StatusBanner;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketSlaBreached;
use App\Support\DisplayTime;
use Illuminate\Support\Carbon;

// Item 7: storage stays UTC, everything shown to people is in the display timezone (Asia/Jakarta).

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->customer = Customer::factory()->create();
});

afterEach(function () {
    Carbon::setTestNow();
});

test('the display timezone defaults to Asia/Jakarta while storage stays UTC', function () {
    expect(config('app.display_timezone'))->toBe('Asia/Jakarta')
        ->and(config('app.timezone'))->toBe('UTC')
        ->and(Carbon::parse('2026-10-09 20:30:00', 'UTC')->inDisplayTz()->format('d M Y H:i'))->toBe('10 Oct 2026 03:30');
});

test('ticket pages, quick view and exports show times in the display timezone', function () {
    Carbon::setTestNow(Carbon::parse('2026-10-09 21:00:00', 'UTC'));

    $ticket = Ticket::factory()->for($this->customer)->create([
        'title' => 'Uji zona waktu',
        'created_at' => Carbon::parse('2026-10-09 20:30:00', 'UTC'),
        'sla_deadline' => Carbon::parse('2026-10-10 01:30:00', 'UTC'),
    ]);

    expect($ticket->fresh()->getRawOriginal('created_at'))->toBe('2026-10-09 20:30:00');

    $this->actingAs($this->admin)->get("/tickets/{$ticket->id}")
        ->assertOk()
        ->assertSee('10 Oct 2026 03:30')
        ->assertSee('10 Oct 2026, 08:30'); // SLA deadline

    $this->actingAs($this->admin)->get('/tickets')
        ->assertOk()
        ->assertSee('10 Oct 2026')
        ->assertSee('03:30');

    $this->actingAs($this->admin)->getJson("/tickets/{$ticket->id}/quick-details")
        ->assertOk()
        ->assertJsonPath('created_at', '10 Oct 2026, 03:30')
        ->assertJsonPath('sla.deadline_label', '10 Oct 2026, 08:30');

    $csv = $this->actingAs($this->admin)->get('/tickets/export/csv')->assertOk()->streamedContent();
    expect($csv)->toContain('10 Oct 2026, 03:30');
});

test('the SLA breach notification shows the deadline in the display timezone', function () {
    $ticket = Ticket::factory()->for($this->customer)->create([
        'sla_deadline' => Carbon::parse('2026-10-09 10:00:00', 'UTC'),
    ]);

    $data = (new TicketSlaBreached($ticket))->toArray($this->admin);

    expect($data['message'])->toContain('09 Oct 2026, 17:00');
});

test('status banner datetime inputs are read in the display timezone and stored in UTC', function () {
    $this->actingAs($this->admin)->post('/status-banners', [
        'title' => 'Maintenance',
        'message' => 'Pemeliharaan jaringan',
        'type' => 'maintenance',
        'is_active' => 1,
        'starts_at' => '2026-10-10T08:00',
        'ends_at' => '2026-10-10T10:30',
    ])->assertRedirect(route('status-banners.index'));

    $banner = StatusBanner::firstOrFail();

    expect($banner->getRawOriginal('starts_at'))->toBe('2026-10-10 01:00:00')
        ->and($banner->getRawOriginal('ends_at'))->toBe('2026-10-10 03:30:00');

    $this->actingAs($this->admin)->get("/status-banners/{$banner->id}/edit")
        ->assertOk()
        ->assertSee('value="2026-10-10T08:00"', false)
        ->assertSee('value="2026-10-10T10:30"', false);

    $this->actingAs($this->admin)->get('/status-banners')
        ->assertOk()
        ->assertSee('10 Oct 08:00');

    // Active from 08:00 WIB = 01:00 UTC.
    Carbon::setTestNow(Carbon::parse('2026-10-10 00:59:00', 'UTC'));
    expect(StatusBanner::active()->count())->toBe(0);
    Carbon::setTestNow(Carbon::parse('2026-10-10 01:01:00', 'UTC'));
    expect(StatusBanner::active()->count())->toBe(1);
});

test('"solved this week" starts on Monday 00:00 in the display timezone', function () {
    // Monday 12 Oct 2026 02:00 WIB = Sunday 11 Oct 19:00 UTC.
    Carbon::setTestNow(Carbon::parse('2026-10-11 19:00:00', 'UTC'));

    expect(DisplayTime::startOfWeek()->toDateTimeString())->toBe('2026-10-11 17:00:00');

    // Resolved Monday 01:00 WIB (this week) and Sunday 23:00 WIB (last week).
    Ticket::factory()->for($this->customer)->create(['status' => 'Solved', 'resolved_at' => Carbon::parse('2026-10-11 18:00:00', 'UTC')]);
    Ticket::factory()->for($this->customer)->create(['status' => 'Closed', 'resolved_at' => Carbon::parse('2026-10-11 16:00:00', 'UTC')]);

    $this->actingAs($this->admin)->get('/dashboard')
        ->assertOk()
        ->assertViewHas('stats', fn ($stats) => $stats['solved_week'] === 1);
});

test('the reports date range is a calendar day range in the display timezone', function () {
    // 10 Oct 2026 01:00 WIB is still 9 Oct in UTC.
    Ticket::factory()->for($this->customer)->create(['created_at' => Carbon::parse('2026-10-09 18:00:00', 'UTC')]);
    Ticket::factory()->for($this->customer)->create(['created_at' => Carbon::parse('2026-10-09 16:00:00', 'UTC')]);

    $this->actingAs($this->admin)->get('/reports?from=2026-10-10&to=2026-10-10')
        ->assertOk()
        ->assertViewHas('metrics', fn ($m) => $m['total'] === 1);
});
