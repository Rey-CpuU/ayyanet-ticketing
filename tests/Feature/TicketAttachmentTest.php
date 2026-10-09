<?php

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Mail::fake();
    Storage::fake('local');
    Storage::fake('public');

    $this->cs = User::factory()->create(['role' => 'cs']);
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->lapangan = User::factory()->create(['role' => 'lapangan']);

    $this->customer = Customer::create([
        'customer_id' => 'C-ATT',
        'name' => 'Rudi',
        'phone' => '0812-2222-3333',
        'address' => 'Jl. Dahlia',
        'package' => 'Home 30 Mbps',
    ]);
});

function attachmentTicketPayload(int $customerId, array $overrides = []): array
{
    return array_merge([
        'customer_id' => $customerId,
        'title' => 'Tiket dengan lampiran',
        'description' => 'Lihat lampiran',
    ], $overrides);
}

function ticketWithAttachment(int $customerId, int $createdBy, string $path): Ticket
{
    return Ticket::create([
        'ticket_number' => 'TKT-ATT-'.uniqid(),
        'customer_id' => $customerId,
        'created_by' => $createdBy,
        'title' => 'Tiket lampiran',
        'description' => 'x',
        'status' => 'Open',
        'attachment_path' => $path,
    ]);
}

test('attachments with a disallowed type are rejected', function (string $name, string $mime) {
    $this->actingAs($this->cs)
        ->post('/tickets', attachmentTicketPayload($this->customer->id, [
            'attachment' => UploadedFile::fake()->create($name, 10, $mime),
        ]))
        ->assertSessionHasErrors('attachment');

    expect(Ticket::count())->toBe(0);
})->with([
    ['malware.exe', 'application/x-msdownload'],
    ['archive.zip', 'application/zip'],
    ['page.html', 'text/html'],
    ['script.php', 'application/x-php'],
]);

test('attachments larger than 5 MB are rejected', function () {
    $this->actingAs($this->cs)
        ->post('/tickets', attachmentTicketPayload($this->customer->id, [
            'attachment' => UploadedFile::fake()->create('besar.pdf', 5121, 'application/pdf'),
        ]))
        ->assertSessionHasErrors('attachment');
});

test('allowed attachments are stored on the private disk', function () {
    $this->actingAs($this->cs)
        ->post('/tickets', attachmentTicketPayload($this->customer->id, [
            'attachment' => UploadedFile::fake()->create('laporan.pdf', 100, 'application/pdf'),
        ]))
        ->assertRedirect();

    $ticket = Ticket::firstOrFail();

    Storage::disk('local')->assertExists($ticket->attachment_path);
    Storage::disk('public')->assertMissing($ticket->attachment_path);

    $this->actingAs($this->cs)->get("/tickets/{$ticket->id}")
        ->assertOk()
        ->assertSee(route('tickets.attachment', $ticket), false)
        ->assertDontSee('/storage/'.$ticket->attachment_path, false);
});

test('authorized users can download an attachment', function () {
    Storage::disk('local')->put('attachments/bukti.txt', 'isi lampiran');
    $ticket = ticketWithAttachment($this->customer->id, $this->cs->id, 'attachments/bukti.txt');

    $response = $this->actingAs($this->cs)->get("/tickets/{$ticket->id}/attachment");

    $response->assertOk()->assertDownload('bukti.txt');
    expect($response->streamedContent())->toBe('isi lampiran');
});

test('attachment download requires permission to view the ticket', function () {
    Storage::disk('local')->put('attachments/bukti.txt', 'isi lampiran');
    $ticket = ticketWithAttachment($this->customer->id, $this->cs->id, 'attachments/bukti.txt');

    $this->get("/tickets/{$ticket->id}/attachment")->assertRedirect('/login');
    $this->actingAs($this->lapangan)->get("/tickets/{$ticket->id}/attachment")->assertForbidden();

    $ticket->update(['assigned_to' => $this->lapangan->id]);
    $this->actingAs($this->lapangan)->get("/tickets/{$ticket->id}/attachment")->assertOk();
});

test('legacy attachments on the public disk are still served through the authorized route', function () {
    Storage::disk('public')->put('attachments/lama.jpg', 'gambar lama');
    $ticket = ticketWithAttachment($this->customer->id, $this->cs->id, 'attachments/lama.jpg');

    $this->actingAs($this->cs)->get("/tickets/{$ticket->id}/attachment")
        ->assertOk()
        ->assertDownload('lama.jpg');
});

test('a missing attachment file does not break the show page', function () {
    $ticket = ticketWithAttachment($this->customer->id, $this->cs->id, 'attachments/hilang.pdf');

    $this->actingAs($this->cs)->get("/tickets/{$ticket->id}")
        ->assertOk()
        ->assertSee('Lampiran tidak tersedia');

    $this->actingAs($this->cs)->get("/tickets/{$ticket->id}/attachment")->assertNotFound();
});

test('replacing an attachment removes the old file', function () {
    Storage::disk('local')->put('attachments/lama.txt', 'lama');
    $ticket = ticketWithAttachment($this->customer->id, $this->cs->id, 'attachments/lama.txt');

    $this->actingAs($this->cs)->put("/tickets/{$ticket->id}", [
        'customer_id' => $ticket->customer_id,
        'title' => $ticket->title,
        'description' => $ticket->description,
        'attachment' => UploadedFile::fake()->create('baru.xlsx', 10, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
    ])->assertRedirect();

    Storage::disk('local')->assertMissing('attachments/lama.txt');
    Storage::disk('local')->assertExists($ticket->fresh()->attachment_path);
});

test('force deleting a ticket deletes its attachment file', function () {
    Storage::disk('local')->put('attachments/hapus.pdf', 'pdf');
    Storage::disk('public')->put('attachments/lama-publik.pdf', 'pdf');
    $ticket = ticketWithAttachment($this->customer->id, $this->cs->id, 'attachments/hapus.pdf');
    $legacy = ticketWithAttachment($this->customer->id, $this->cs->id, 'attachments/lama-publik.pdf');

    foreach ([$ticket, $legacy] as $t) {
        $t->delete();
        $this->actingAs($this->admin)->delete("/tickets/{$t->id}/force-delete")->assertRedirect();
    }

    Storage::disk('local')->assertMissing('attachments/hapus.pdf');
    Storage::disk('public')->assertMissing('attachments/lama-publik.pdf');
    expect(Ticket::withTrashed()->count())->toBe(0);
});

test('the create form renders the attachment dropzone with the allowed types', function () {
    $this->actingAs($this->cs)->get('/tickets/create')
        ->assertOk()
        ->assertSee('data-dropzone', false)
        ->assertSee('x-data="fileDropzone(', false)
        ->assertSee('type="file"', false)
        ->assertSee('name="attachment"', false)
        ->assertSee('accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.txt"', false)
        ->assertSee('Seret &amp; lepas file di sini', false)
        ->assertSee('Maks. 5 MB: jpg, jpeg, png, pdf, doc, docx, xls, xlsx, txt.')
        ->assertSee('aria-live="polite"', false)
        ->assertDontSee('data-dropzone-current', false);
});

test('the edit form renders the dropzone with the current attachment and a download link', function () {
    Storage::disk('local')->put('attachments/bukti.pdf', 'pdf');
    $ticket = ticketWithAttachment($this->customer->id, $this->cs->id, 'attachments/bukti.pdf');

    $this->actingAs($this->cs)->get("/tickets/{$ticket->id}/edit")
        ->assertOk()
        ->assertSee('data-dropzone', false)
        ->assertSee('accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.txt"', false)
        ->assertSee('data-dropzone-current', false)
        ->assertSee('File saat ini')
        ->assertSee('bukti.pdf')
        ->assertSee('href="'.route('tickets.attachment', $ticket).'"', false)
        ->assertSee('Ganti file');
});

test('the edit form dropzone omits the current file block when there is no attachment', function () {
    $ticket = ticketWithAttachment($this->customer->id, $this->cs->id, 'attachments/tidak-ada.pdf');
    $ticket->update(['attachment_path' => null]);

    $this->actingAs($this->cs)->get("/tickets/{$ticket->id}/edit")
        ->assertOk()
        ->assertSee('data-dropzone', false)
        ->assertDontSee('data-dropzone-current', false);
});

test('server validation errors are shown inside the dropzone after a rejected upload', function () {
    $this->actingAs($this->cs)
        ->from('/tickets/create')
        ->followingRedirects()
        ->post('/tickets', attachmentTicketPayload($this->customer->id, [
            'attachment' => UploadedFile::fake()->create('virus.exe', 10, 'application/x-msdownload'),
        ]))
        ->assertOk()
        ->assertSee('Lampiran harus berupa file: jpg, jpeg, png, pdf, doc, docx, xls, xlsx, txt.')
        ->assertSee('is-invalid', false);

    expect(Ticket::count())->toBe(0);
});

test('an image uploaded through the normal multipart form is stored privately', function () {
    $this->actingAs($this->cs)
        ->post('/tickets', attachmentTicketPayload($this->customer->id, [
            'attachment' => UploadedFile::fake()->image('foto-modem.png', 320, 240),
        ]))
        ->assertRedirect();

    $ticket = Ticket::firstOrFail();
    expect($ticket->attachment_path)->toStartWith('attachments/')->toEndWith('.png');
    Storage::disk('local')->assertExists($ticket->attachment_path);
});
