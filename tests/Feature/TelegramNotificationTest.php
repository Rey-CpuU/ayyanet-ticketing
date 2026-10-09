<?php

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TelegramNotificationService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'telegram.notif_bot_token' => '111111:AAABBBCCCDDDEEEFFF',
        'telegram.notif_group_id' => '-100987654321',
        'telegram.notif_enabled' => true,
    ]);

    Http::fake([
        'https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => []], 200),
    ]);
});

test('telegram notification sends alert when new ticket is created', function () {
    $user = User::factory()->create(['role' => 'admin', 'name' => 'Admin Ayyanet']);
    $customer = Customer::create([
        'name' => 'Ahmad Santoso',
        'phone' => '08123456789',
        'address' => 'Jl. Pahlawan No. 45',
        'customer_id' => 'CUS-TEST',
    ]);

    $ticket = Ticket::create([
        'ticket_number' => 'TCK-NOTIF-01',
        'customer_id' => $customer->id,
        'created_by' => $user->id,
        'title' => 'Indikator LOS Merah',
        'description' => 'Lampu router LOS merah sejak jam 10 pagi',
        'category' => 'Internet',
        'priority' => 'High',
        'status' => 'Open',
        'olt' => 'OLT-01',
        'location' => 'Port 05',
    ]);

    $result = TelegramNotificationService::sendTicketCreated($ticket);

    expect($result)->not->toBeNull();
    expect($result['ok'])->toBeTrue();

    Http::assertSent(function ($request) {
        $body = $request->data();

        return str_contains($request->url(), 'sendMessage')
            && $body['chat_id'] === '-100987654321'
            && str_contains($body['text'], 'TIKET BARU MASUK!')
            && str_contains($body['text'], 'TCK-NOTIF-01')
            && str_contains($body['text'], 'Ahmad Santoso')
            && str_contains($body['text'], 'Indikator LOS Merah')
            && str_contains($body['text'], 'High');
    });
});

test('telegram notification sends alert when ticket is solved or closed', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $agent = User::factory()->create(['role' => 'cs', 'name' => 'Bambang Teknisi']);
    $customer = Customer::create([
        'name' => 'Siti Aminah',
        'phone' => '08987654321',
        'address' => 'Perumahan Bunga Blok C',
        'customer_id' => 'CUS-TEST-2',
    ]);

    $ticket = Ticket::create([
        'ticket_number' => 'TCK-NOTIF-02',
        'customer_id' => $customer->id,
        'created_by' => $user->id,
        'assigned_to' => $agent->id,
        'title' => 'Modem restart terus',
        'description' => 'Kendala adaptor',
        'category' => 'Hardware',
        'priority' => 'Medium',
        'status' => 'Solved',
    ]);

    $result = TelegramNotificationService::sendTicketStatusChanged($ticket, 'Checking', 'Solved', 'Admin Joko');

    expect($result)->not->toBeNull();
    expect($result['ok'])->toBeTrue();

    Http::assertSent(function ($request) {
        $body = $request->data();

        return str_contains($request->url(), 'sendMessage')
            && $body['chat_id'] === '-100987654321'
            && str_contains($body['text'], 'SELESAI (SOLVED)!')
            && str_contains($body['text'], 'TCK-NOTIF-02')
            && str_contains($body['text'], 'Siti Aminah')
            && str_contains($body['text'], 'Admin Joko')
            && str_contains($body['text'], 'Bambang Teknisi');
    });
});

test('telegram notification sends alert for in-progress status changes like checking', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $customer = Customer::create([
        'name' => 'Test Cust',
        'phone' => '0800000000',
        'address' => 'Test Address',
        'customer_id' => 'CUS-TEST-3',
    ]);

    $ticket = Ticket::create([
        'ticket_number' => 'TCK-NOTIF-03',
        'customer_id' => $customer->id,
        'created_by' => $user->id,
        'title' => 'Gangguan Sinyal',
        'description' => 'Sinyal lemah',
        'category' => 'Internet',
        'priority' => 'Low',
        'status' => 'Checking',
    ]);

    $result = TelegramNotificationService::sendTicketStatusChanged($ticket, 'Open', 'Checking', 'Budi CS');

    expect($result)->not->toBeNull();
    expect($result['ok'])->toBeTrue();

    Http::assertSent(function ($request) {
        $body = $request->data();

        return str_contains($request->url(), 'sendMessage')
            && $body['chat_id'] === '-100987654321'
            && str_contains($body['text'], 'CHECKING')
            && str_contains($body['text'], 'Budi CS');
    });
});

test('telegram notification only uses the dedicated notification bot token', function () {
    config(['telegram.notif_bot_token' => null, 'telegram.bot_token' => 'old-interactive-bot-token']);

    expect(TelegramNotificationService::getToken())->toBeNull()
        ->and(TelegramNotificationService::isConfigured())->toBeFalse()
        ->and(TelegramNotificationService::sendTestNotification())->toBeNull();

    Http::assertNothingSent();
});
