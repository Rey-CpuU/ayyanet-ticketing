<?php

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TelegramConversationManager;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
    config(['telegram.bot_token' => 'dummy_token_for_testing']);
    config(['telegram.allowed_chat_ids' => []]);
    Http::fake([
        'https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => []], 200),
    ]);
});

test('telegram webhook endpoint responds with ok', function () {
    $response = $this->postJson('/telegram/webhook', [
        'update_id' => 12345,
        'message' => [
            'message_id' => 1,
            'chat' => ['id' => 99999],
            'text' => '/start',
        ],
    ]);

    $response->assertStatus(200)->assertJson(['ok' => true]);
});

test('telegram staff can login and use /clean command', function () {
    $user = User::factory()->create([
        'email' => 'admin@ayyanet.test',
        'password' => \Illuminate\Support\Facades\Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $manager = app(TelegramConversationManager::class);
    $chatId = 888888;

    // 1. Start login
    $manager->handleUpdate([
        'callback_query' => [
            'id' => 'cb_login',
            'message' => ['chat' => ['id' => $chatId]],
            'data' => 'menu_login',
        ],
    ]);
    $state = Cache::get("telegram_state_{$chatId}");
    expect($state['step'])->toBe('LOGIN_EMAIL');

    // 2. Submit Email
    $manager->handleUpdate([
        'message' => [
            'chat' => ['id' => $chatId],
            'text' => 'admin@ayyanet.test',
        ],
    ]);
    $state = Cache::get("telegram_state_{$chatId}");
    expect($state['step'])->toBe('LOGIN_PASSWORD');

    // 3. Submit Password
    $manager->handleUpdate([
        'message' => [
            'chat' => ['id' => $chatId],
            'message_id' => 10,
            'text' => 'secret123',
        ],
    ]);

    // Should now be authenticated
    $authenticatedUser = $manager->getAuthenticatedStaff($chatId);
    expect($authenticatedUser)->not->toBeNull();
    expect($authenticatedUser->id)->toBe($user->id);

    // 4. Send /clean command
    $manager->handleUpdate([
        'message' => [
            'chat' => ['id' => $chatId],
            'message_id' => 20,
            'text' => '/clean',
        ],
    ]);

    // State should be reset and user remains authenticated
    $state = Cache::get("telegram_state_{$chatId}");
    expect($state)->toBeNull();
    expect($manager->getAuthenticatedStaff($chatId))->not->toBeNull();
});

test('telegram user can go through add customer wizard step-by-step', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $manager = app(TelegramConversationManager::class);
    $chatId = 123456;
    Cache::forever("telegram_auth_{$chatId}", $user->id);

    // 1. Click Add Customer
    $manager->handleUpdate([
        'callback_query' => [
            'id' => 'cb1',
            'message' => ['chat' => ['id' => $chatId]],
            'data' => 'menu_add_customer',
        ],
    ]);

    $state = Cache::get("telegram_state_{$chatId}");
    expect($state['step'])->toBe('CUST_NAME');

    // 2. Send Name
    $manager->handleUpdate([
        'message' => [
            'chat' => ['id' => $chatId],
            'text' => 'Budi Santoso',
        ],
    ]);
    $state = Cache::get("telegram_state_{$chatId}");
    expect($state['step'])->toBe('CUST_PHONE');
    expect($state['data']['name'])->toBe('Budi Santoso');

    // 3. Send Phone
    $manager->handleUpdate([
        'message' => [
            'chat' => ['id' => $chatId],
            'text' => '081234567890',
        ],
    ]);
    $state = Cache::get("telegram_state_{$chatId}");
    expect($state['step'])->toBe('CUST_ADDRESS');
    expect($state['data']['phone'])->toBe('081234567890');

    // 4. Send Address
    $manager->handleUpdate([
        'message' => [
            'chat' => ['id' => $chatId],
            'text' => 'Jl. Merdeka No. 10, Jakarta',
        ],
    ]);
    $state = Cache::get("telegram_state_{$chatId}");
    expect($state['step'])->toBe('CUST_PACKAGE');

    // 5. Send Package
    $manager->handleUpdate([
        'message' => [
            'chat' => ['id' => $chatId],
            'text' => 'Home 20Mbps',
        ],
    ]);

    // Customer should be in database and state cleared
    $state = Cache::get("telegram_state_{$chatId}");
    expect($state)->toBeNull();

    $customer = Customer::where('phone', '081234567890')->first();
    expect($customer)->not->toBeNull();
    expect($customer->name)->toBe('Budi Santoso');
    expect($customer->package)->toBe('Home 20Mbps');
    expect($customer->customer_id)->toStartWith('CUS-');
});

test('telegram user can create ticket step-by-step', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $customer = Customer::create([
        'name' => 'Siti Rahma',
        'phone' => '08987654321',
        'address' => 'Komplek A Blok B',
        'package' => 'Office 50Mbps',
    ]);

    $manager = app(TelegramConversationManager::class);
    $chatId = 777777;
    Cache::forever("telegram_auth_{$chatId}", $user->id);

    // 1. Select Customer directly
    $manager->handleUpdate([
        'callback_query' => [
            'id' => 'cb_cust',
            'message' => ['chat' => ['id' => $chatId]],
            'data' => "select_cust_{$customer->id}",
        ],
    ]);

    $state = Cache::get("telegram_state_{$chatId}");
    expect($state['step'])->toBe('TICK_TITLE');
    expect($state['data']['customer_id'])->toBe($customer->id);

    // 2. Send Title
    $manager->handleUpdate([
        'message' => [
            'chat' => ['id' => $chatId],
            'text' => 'Internet putus lampu LOS merah',
        ],
    ]);
    $state = Cache::get("telegram_state_{$chatId}");
    expect($state['step'])->toBe('TICK_DESC');

    // 3. Skip Description via button
    $manager->handleUpdate([
        'callback_query' => [
            'id' => 'cb_skip_desc',
            'message' => ['chat' => ['id' => $chatId]],
            'data' => 'skip_desc',
        ],
    ]);
    $state = Cache::get("telegram_state_{$chatId}");
    expect($state['step'])->toBe('TICK_CATEGORY');
    expect($state['data']['description'])->toBe('Internet putus lampu LOS merah');

    // 4. Select Category
    $manager->handleUpdate([
        'callback_query' => [
            'id' => 'cb_cat',
            'message' => ['chat' => ['id' => $chatId]],
            'data' => 'cat_Internet',
        ],
    ]);
    $state = Cache::get("telegram_state_{$chatId}");
    expect($state['step'])->toBe('TICK_PRIORITY');

    // 5. Select Priority
    $manager->handleUpdate([
        'callback_query' => [
            'id' => 'cb_prio',
            'message' => ['chat' => ['id' => $chatId]],
            'data' => 'prio_High',
        ],
    ]);
    $state = Cache::get("telegram_state_{$chatId}");
    expect($state['step'])->toBe('TICK_OLT');

    // 6. Send OLT text
    $manager->handleUpdate([
        'message' => [
            'chat' => ['id' => $chatId],
            'text' => 'OLT-JKT-01',
        ],
    ]);
    $state = Cache::get("telegram_state_{$chatId}");
    expect($state['step'])->toBe('TICK_LOCATION');

    // 7. Send Location / Port
    $manager->handleUpdate([
        'message' => [
            'chat' => ['id' => $chatId],
            'text' => 'Port 04 / ODP-08',
        ],
    ]);

    // Ticket should be in database and state cleared
    $state = Cache::get("telegram_state_{$chatId}");
    expect($state)->toBeNull();

    $ticket = Ticket::where('customer_id', $customer->id)->latest()->first();
    expect($ticket)->not->toBeNull();
    expect($ticket->title)->toBe('Internet putus lampu LOS merah');
    expect($ticket->category)->toBe('Internet');
    expect($ticket->priority)->toBe('High');
    expect($ticket->olt)->toBe('OLT-JKT-01');
    expect($ticket->location)->toBe('Port 04 / ODP-08');
    expect($ticket->status)->toBe('Open');
    expect($ticket->ticket_number)->toStartWith('TCK-');
});
