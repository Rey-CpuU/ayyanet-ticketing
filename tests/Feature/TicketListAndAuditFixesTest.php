<?php

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\TicketAuditLog;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->cs = User::factory()->create(['role' => 'cs']);
    $this->customer = Customer::factory()->create();
});

// Item 15: audit log values are stored as JSON once; old double-encoded rows still render.
test('audit log rows are stored single-encoded and old rows stay readable', function () {
    $ticket = Ticket::factory()->for($this->customer)->create(['title' => 'Lama', 'category' => 'Email']);

    $this->actingAs($this->cs)->put("/tickets/{$ticket->id}", [
        'customer_id' => $ticket->customer_id,
        'title' => 'Baru',
        'description' => $ticket->description,
        'status' => 'Open',
        'category' => 'Email',
    ])->assertRedirect();

    $raw = DB::table('ticket_audit_logs')->where('ticket_id', $ticket->id)->value('new_value');
    expect(json_decode($raw, true))->toBe(['title' => 'Baru'])
        ->and(TicketAuditLog::where('ticket_id', $ticket->id)->first()->new_value)->toBe(['title' => 'Baru']);

    // A legacy row written with a manual json_encode on top of the array cast.
    DB::table('ticket_audit_logs')->insert([
        'ticket_id' => $ticket->id,
        'user_id' => $this->cs->id,
        'action' => 'updated',
        'old_value' => json_encode(json_encode(['priority' => 'Low'])),
        'new_value' => json_encode(json_encode(['priority' => 'High'])),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($this->cs)->get("/tickets/{$ticket->id}/audit-log")
        ->assertOk()
        ->assertSeeInOrder(['Priority:', 'Low', 'High'])
        ->assertSeeInOrder(['Title:', 'Lama', 'Baru']);
});

// Item 16: export links carry the active filters and the export applies them.
test('export links keep the active filters', function () {
    Ticket::factory()->for($this->customer)->create(['ticket_number' => 'TKT-7001', 'status' => 'Open', 'category' => 'Email']);
    Ticket::factory()->for($this->customer)->create(['ticket_number' => 'TKT-7002', 'status' => 'Checking', 'category' => 'Email']);

    $this->actingAs($this->admin)->get('/tickets?status=Checking&q=TKT&sort=oldest')
        ->assertOk()
        ->assertSee(route('tickets.export.csv', ['q' => 'TKT', 'status' => 'Checking']))
        ->assertSee(route('tickets.export.pdf', ['q' => 'TKT', 'status' => 'Checking']));

    $csv = $this->actingAs($this->admin)->get('/tickets/export/csv?status=Checking')->assertOk()->streamedContent();

    expect($csv)->toContain('TKT-7002')->not->toContain('TKT-7001');
});

test('the my-tickets export is limited to the user\'s own tickets', function () {
    Ticket::factory()->for($this->customer)->create(['ticket_number' => 'TKT-7101', 'created_by' => $this->cs->id]);
    Ticket::factory()->for($this->customer)->create(['ticket_number' => 'TKT-7102', 'created_by' => $this->admin->id]);

    $this->actingAs($this->cs)->get('/my-tickets')
        ->assertOk()
        ->assertSee(route('tickets.export.csv', ['mine' => 1]), false);

    $csv = $this->actingAs($this->cs)->get('/tickets/export/csv?mine=1')->assertOk()->streamedContent();

    expect($csv)->toContain('TKT-7101')->not->toContain('TKT-7102');
});

// Item 18: the conversation is ordered by created_at, then id.
test('ticket messages are ordered by time, then id', function () {
    $ticket = Ticket::factory()->for($this->customer)->create();
    $at = Carbon::parse('2026-10-09 08:00:00');

    $later = TicketMessage::create(['ticket_id' => $ticket->id, 'user_id' => $this->cs->id, 'message' => 'Pesan kedua']);
    $later->forceFill(['created_at' => $at->copy()->addMinute()])->save();
    $first = TicketMessage::create(['ticket_id' => $ticket->id, 'user_id' => $this->cs->id, 'message' => 'Pesan pertama']);
    $first->forceFill(['created_at' => $at])->save();
    $tie = TicketMessage::create(['ticket_id' => $ticket->id, 'user_id' => $this->cs->id, 'message' => 'Pesan ketiga']);
    $tie->forceFill(['created_at' => $at->copy()->addMinute()])->save();

    expect($ticket->messages()->pluck('id')->all())->toBe([$first->id, $later->id, $tie->id]);

    $this->actingAs($this->cs)->get("/tickets/{$ticket->id}")
        ->assertSeeInOrder(['Pesan pertama', 'Pesan kedua', 'Pesan ketiga']);

    $this->actingAs($this->cs)->getJson("/tickets/{$ticket->id}/quick-details")
        ->assertJsonPath('messages.0.message', 'Pesan pertama')
        ->assertJsonPath('messages.2.message', 'Pesan ketiga');
});

// Item 19: a replaced attachment is removed only after the update is committed.
test('replacing an attachment deletes the old file after the update', function () {
    Storage::fake('local');
    Storage::disk('local')->put('attachments/lama.pdf', 'lama');

    $ticket = Ticket::factory()->for($this->customer)->create(['attachment_path' => 'attachments/lama.pdf', 'category' => 'Email']);

    $this->actingAs($this->cs)->put("/tickets/{$ticket->id}", [
        'customer_id' => $ticket->customer_id,
        'title' => $ticket->title,
        'description' => $ticket->description,
        'status' => 'Open',
        'category' => 'Email',
        'attachment' => UploadedFile::fake()->create('baru.pdf', 10, 'application/pdf'),
    ])->assertRedirect();

    $path = $ticket->fresh()->attachment_path;

    expect($path)->not->toBe('attachments/lama.pdf');
    Storage::disk('local')->assertExists($path);
    Storage::disk('local')->assertMissing('attachments/lama.pdf');
});

test('a failed update keeps the old attachment and drops the new upload', function () {
    Storage::fake('local');
    Storage::disk('local')->put('attachments/lama.pdf', 'lama');

    $ticket = Ticket::factory()->for($this->customer)->create(['attachment_path' => 'attachments/lama.pdf', 'category' => 'Email']);

    Ticket::updating(function () {
        throw new RuntimeException('DB gagal');
    });

    $this->withoutExceptionHandling();

    expect(fn () => $this->actingAs($this->cs)->put("/tickets/{$ticket->id}", [
        'customer_id' => $ticket->customer_id,
        'title' => 'Judul baru',
        'description' => $ticket->description,
        'status' => 'Open',
        'category' => 'Email',
        'attachment' => UploadedFile::fake()->create('baru.pdf', 10, 'application/pdf'),
    ]))->toThrow(RuntimeException::class);

    expect($ticket->fresh()->attachment_path)->toBe('attachments/lama.pdf');
    Storage::disk('local')->assertExists('attachments/lama.pdf');
    expect(Storage::disk('local')->files('attachments'))->toBe(['attachments/lama.pdf']);
});

// Item 21: queue tab counts cover the open tickets of the whole filtered set.
test('dashboard tab counts cover open tickets of the filtered set and match the header chip', function () {
    Ticket::factory()->count(17)->for($this->customer)->create(['priority' => 'Medium']);
    Ticket::factory()->count(2)->for($this->customer)->create(['priority' => 'High']);
    Ticket::factory()->for($this->customer)->create(['status' => 'Solved']);
    Ticket::factory()->count(3)->for($this->customer)->assignedTo($this->admin)->create(['priority' => 'High']);

    $this->actingAs($this->admin)->get('/dashboard')
        ->assertOk()
        ->assertViewHas('stats', fn ($s) => $s['unassigned'] === 19)
        ->assertViewHas('queueCounts', fn ($c) => $c === ['unassigned' => 19, 'mine' => 3, 'all' => 23]);

    $this->actingAs($this->admin)->get('/dashboard?priority=High')
        ->assertOk()
        ->assertViewHas('queueCounts', fn ($c) => $c === ['unassigned' => 2, 'mine' => 3, 'all' => 5]);
});
