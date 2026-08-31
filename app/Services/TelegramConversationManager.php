<?php

namespace App\Services;

use App\Mail\TicketCreated;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketCreatedNotification;
use App\Support\TicketClassifier;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class TelegramConversationManager
{
    protected TelegramService $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    /**
     * Process incoming update payload from Telegram.
     */
    public function handleUpdate(array $update): void
    {
        if (isset($update['callback_query'])) {
            $this->handleCallbackQuery($update['callback_query']);
            return;
        }

        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
            return;
        }
    }

    /**
     * Handle incoming text messages.
     */
    protected function handleMessage(array $message): void
    {
        $chatId = $message['chat']['id'] ?? null;
        $messageId = $message['message_id'] ?? null;
        $username = $message['from']['username'] ?? null;
        $text = trim($message['text'] ?? '');

        if (!$chatId || $text === '') {
            return;
        }

        $lowerText = mb_strtolower($text);

        // Global commands
        if (in_array($lowerText, ['/cancel', '/batal', 'batal', 'cancel'])) {
            $this->resetState($chatId);
            $this->telegram->sendMessage(
                $chatId,
                '❌ <i>Proses telah dibatalkan.</i>',
                TelegramService::buildInlineKeyboard([
                    [['text' => '🏠 Kembali ke Menu Utama', 'callback_data' => 'menu_main']]
                ])
            );
            return;
        }

        if (in_array($lowerText, ['/clean', 'clean', '/bersihkan', 'bersihkan'])) {
            $this->cleanChat($chatId, $messageId);
            return;
        }

        if ($lowerText === '/logout' || $lowerText === 'logout') {
            $this->logoutStaff($chatId);
            return;
        }

        $state = $this->getState($chatId);
        $currentStep = $state['step'] ?? null;

        // Login Wizard Steps (Accessible without staff auth)
        if ($currentStep === 'LOGIN_EMAIL') {
            $this->processLoginEmail($chatId, $state, $text);
            return;
        }

        if ($currentStep === 'LOGIN_PASSWORD') {
            // Delete password message for privacy
            if ($messageId) {
                try {
                    $token = config('telegram.bot_token');
                    \Illuminate\Support\Facades\Http::post("https://api.telegram.org/bot{$token}/deleteMessage", [
                        'chat_id' => $chatId,
                        'message_id' => $messageId,
                    ]);
                } catch (\Exception $e) {}
            }
            $this->processLoginPassword($chatId, $state, $text, $username);
            return;
        }

        // Check if user is authenticated staff
        $staff = $this->getAuthenticatedStaff($chatId);
        if (!$staff) {
            $this->sendStaffLoginPrompt($chatId);
            return;
        }

        if (in_array($lowerText, ['/start', '/menu', 'menu', 'start'])) {
            $this->resetState($chatId);
            $this->sendMainMenu($chatId, null, $staff);
            return;
        }

        if (!$currentStep) {
            $this->sendMainMenu($chatId, "Perintah tidak dikenali. Silakan gunakan menu di bawah:", $staff);
            return;
        }

        // Process step based on state
        switch ($currentStep) {
            // Customer Wizard Steps
            case 'CUST_NAME':
                $this->processCustomerName($chatId, $state, $text);
                break;
            case 'CUST_PHONE':
                $this->processCustomerPhone($chatId, $state, $text);
                break;
            case 'CUST_ADDRESS':
                $this->processCustomerAddress($chatId, $state, $text);
                break;
            case 'CUST_PACKAGE':
                $this->processCustomerPackage($chatId, $state, $text);
                break;

            // Ticket Wizard Steps
            case 'TICK_SEARCH_CUSTOMER':
                $this->processTicketCustomerSearch($chatId, $state, $text);
                break;
            case 'TICK_TITLE':
                $this->processTicketTitle($chatId, $state, $text);
                break;
            case 'TICK_DESC':
                $this->processTicketDescription($chatId, $state, $text);
                break;
            case 'TICK_OLT':
                $this->processTicketOlt($chatId, $state, $text);
                break;
            case 'TICK_LOCATION':
                $this->processTicketLocation($chatId, $state, $text, $staff);
                break;

            default:
                $this->resetState($chatId);
                $this->sendMainMenu($chatId, null, $staff);
                break;
        }
    }

    /**
     * Handle callback queries (inline buttons).
     */
    protected function handleCallbackQuery(array $callbackQuery): void
    {
        $callbackId = $callbackQuery['id'] ?? '';
        $chatId = $callbackQuery['message']['chat']['id'] ?? null;
        $data = $callbackQuery['data'] ?? '';

        if (!$chatId) {
            return;
        }

        $this->telegram->answerCallbackQuery($callbackId);

        if ($data === 'menu_login') {
            $this->startStaffLoginWizard($chatId);
            return;
        }

        if ($data === 'menu_logout') {
            $this->logoutStaff($chatId);
            return;
        }

        if ($data === 'menu_clean') {
            $messageId = $callbackQuery['message']['message_id'] ?? null;
            $this->cleanChat($chatId, $messageId);
            return;
        }

        // Must be authenticated staff for any other action
        $staff = $this->getAuthenticatedStaff($chatId);
        if (!$staff) {
            $this->sendStaffLoginPrompt($chatId);
            return;
        }

        if ($data === 'menu_main') {
            $this->resetState($chatId);
            $this->sendMainMenu($chatId, null, $staff);
            return;
        }

        if ($data === 'menu_add_customer') {
            $this->startAddCustomerWizard($chatId);
            return;
        }

        if ($data === 'menu_create_ticket') {
            $this->startCreateTicketWizard($chatId);
            return;
        }

        if ($data === 'cancel_flow') {
            $this->resetState($chatId);
            $this->telegram->sendMessage(
                $chatId,
                '❌ <i>Proses dibatalkan.</i>',
                TelegramService::buildInlineKeyboard([
                    [['text' => '🏠 Kembali ke Menu Utama', 'callback_data' => 'menu_main']]
                ])
            );
            return;
        }

        // Handle selecting customer for ticket
        if (str_starts_with($data, 'select_cust_')) {
            $customerId = (int) substr($data, 12);
            $this->selectCustomerForTicket($chatId, $customerId);
            return;
        }

        // Handle selecting category
        if (str_starts_with($data, 'cat_')) {
            $category = substr($data, 4);
            $this->processTicketCategory($chatId, $category);
            return;
        }

        // Handle skip description
        if ($data === 'skip_desc') {
            $state = $this->getState($chatId);
            $this->processTicketDescription($chatId, $state, '-');
            return;
        }

        // Handle selecting priority
        if (str_starts_with($data, 'prio_')) {
            $priority = substr($data, 5);
            $this->processTicketPriority($chatId, $priority);
            return;
        }

        // Handle skip OLT
        if ($data === 'skip_olt') {
            $state = $this->getState($chatId);
            $state['data']['olt'] = null;
            $state['step'] = 'TICK_LOCATION';
            $this->setState($chatId, $state);

            $this->telegram->sendMessage(
                $chatId,
                "📍 <b>Bikin Ticket Baru (6/6)</b>\n\nMasukkan <b>Lokasi / Port</b> (contoh: <code>Port 12 / ODP-A01</code>) atau klik Lewati:",
                TelegramService::buildInlineKeyboard([
                    [['text' => '⏭️ Lewati Lokasi/Port', 'callback_data' => 'skip_location']],
                    [['text' => '❌ Batal', 'callback_data' => 'cancel_flow']],
                ])
            );
            return;
        }

        // Handle skip location
        if ($data === 'skip_location') {
            $state = $this->getState($chatId);
            $state['data']['location'] = null;
            $this->finalizeTicketCreation($chatId, $state['data'], $staff);
            return;
        }

        // Handle skip both OLT & Location
        if ($data === 'skip_olt_loc') {
            $state = $this->getState($chatId);
            $state['data']['olt'] = null;
            $state['data']['location'] = null;
            $this->finalizeTicketCreation($chatId, $state['data'], $staff);
            return;
        }
    }

    // ==========================================
    // STAFF AUTHENTICATION WIZARD
    // ==========================================

    protected function sendStaffLoginPrompt(int|string $chatId): void
    {
        $text = "🔐 <b>Akses Terbatas — Khusus Staf Ayyanet</b>\n\n" .
            "Bot ini hanya dapat digunakan oleh staf/admin resmi <b>Ayyanet Ticketing</b>.\n\n" .
            "Silakan hubungkan akun staf Anda untuk memulai:";

        $keyboard = TelegramService::buildInlineKeyboard([
            [['text' => '🔑 Login Akun Staf', 'callback_data' => 'menu_login']]
        ]);

        $this->telegram->sendMessage($chatId, $text, $keyboard);
    }

    protected function startStaffLoginWizard(int|string $chatId): void
    {
        $this->setState($chatId, [
            'step' => 'LOGIN_EMAIL',
            'data' => [],
        ]);

        $text = "🔑 <b>Login Akun Staf Ayyanet (1/2)</b>\n\n" .
            "Masukkan <b>Email</b> akun staf Anda yang terdaftar di web Ayyanet:\n\n" .
            "<i>(Ketik /cancel untuk membatalkan)</i>";

        $keyboard = TelegramService::buildInlineKeyboard([
            [['text' => '❌ Batal', 'callback_data' => 'cancel_flow']]
        ]);

        $this->telegram->sendMessage($chatId, $text, $keyboard);
    }

    protected function processLoginEmail(int|string $chatId, array $state, string $email): void
    {
        $state['data']['email'] = trim($email);
        $state['step'] = 'LOGIN_PASSWORD';
        $this->setState($chatId, $state);

        $text = "🔒 <b>Login Akun Staf Ayyanet (2/2)</b>\n\n" .
            "Email: <b>" . e($email) . "</b>\n\n" .
            "Masukkan <b>Password</b> akun Anda:\n" .
            "<i>(Pesan password Anda akan otomatis dihapus oleh bot setelah dikirim)</i>";

        $keyboard = TelegramService::buildInlineKeyboard([
            [['text' => '❌ Batal', 'callback_data' => 'cancel_flow']]
        ]);

        $this->telegram->sendMessage($chatId, $text, $keyboard);
    }

    protected function processLoginPassword(int|string $chatId, array $state, string $password, ?string $username): void
    {
        $email = $state['data']['email'] ?? '';
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            $this->resetState($chatId);
            $text = "❌ <b>Login Gagal!</b>\n\nEmail atau password salah. Silakan coba lagi:";
            $keyboard = TelegramService::buildInlineKeyboard([
                [['text' => '🔑 Coba Login Lagi', 'callback_data' => 'menu_login']]
            ]);
            $this->telegram->sendMessage($chatId, $text, $keyboard);
            return;
        }

        // Link telegram account
        $this->resetState($chatId);
        Cache::forever("telegram_auth_{$chatId}", $user->id);

        try {
            if (Schema::hasColumn('users', 'telegram_chat_id')) {
                $user->telegram_chat_id = (string) $chatId;
                if ($username) {
                    $user->telegram_username = $username;
                }
                $user->save();
            }
        } catch (\Exception $e) {
            Log::warning('Failed saving telegram_chat_id to user model: ' . $e->getMessage());
        }

        $roleText = strtoupper($user->role ?? 'STAFF');
        $msg = "✅ <b>Login Berhasil!</b>\n\n" .
            "Selamat datang, <b>" . e($user->name) . "</b> [{$roleText}]\n" .
            "Akun Telegram Anda telah terverifikasi sebagai staf Ayyanet.";

        $this->telegram->sendMessage($chatId, $msg);
        $this->sendMainMenu($chatId, null, $user);
    }

    protected function logoutStaff(int|string $chatId): void
    {
        Cache::forget("telegram_auth_{$chatId}");
        $this->resetState($chatId);

        try {
            if (Schema::hasColumn('users', 'telegram_chat_id')) {
                User::where('telegram_chat_id', (string) $chatId)->update([
                    'telegram_chat_id' => null,
                    'telegram_username' => null,
                ]);
            }
        } catch (\Exception $e) {}

        $msg = "👋 <i>Akun staf Anda telah berhasil logout dari bot Telegram ini.</i>";
        $keyboard = TelegramService::buildInlineKeyboard([
            [['text' => '🔑 Login Kembali', 'callback_data' => 'menu_login']]
        ]);

        $this->telegram->sendMessage($chatId, $msg, $keyboard);
    }

    public function getAuthenticatedStaff(int|string $chatId): ?User
    {
        // 1. Check in persistent cache
        $userId = Cache::get("telegram_auth_{$chatId}");
        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                return $user;
            }
        }

        // 2. Check in database if column exists
        try {
            if (Schema::hasColumn('users', 'telegram_chat_id')) {
                $user = User::where('telegram_chat_id', (string) $chatId)->first();
                if ($user) {
                    Cache::forever("telegram_auth_{$chatId}", $user->id);
                    return $user;
                }
            }
        } catch (\Exception $e) {}

        return null;
    }

    // ==========================================
    // MAIN MENU
    // ==========================================

    public function cleanChat(int|string $chatId, ?int $currentMessageId): void
    {
        $this->resetState($chatId);

        if ($currentMessageId) {
            // Collect up to 100 message IDs backwards
            $idsToDelete = [];
            for ($i = $currentMessageId; $i >= max(1, $currentMessageId - 99); $i--) {
                $idsToDelete[] = $i;
            }

            // Try batch delete first
            $success = $this->telegram->deleteMessages($chatId, $idsToDelete);

            // If batch delete failed (or unsupported), delete one by one
            if (!$success) {
                foreach ($idsToDelete as $msgId) {
                    $this->telegram->deleteMessage($chatId, $msgId);
                }
            }
        }

        $staff = $this->getAuthenticatedStaff($chatId);
        if ($staff) {
            $this->sendMainMenu($chatId, "🧹 <i>Layar chat telah dibersihkan!</i>", $staff);
        } else {
            $this->sendStaffLoginPrompt($chatId);
        }
    }

    public function sendMainMenu(int|string $chatId, ?string $extraText = null, ?User $staff = null): void
    {
        if (!$staff) {
            $staff = $this->getAuthenticatedStaff($chatId);
        }

        $staffName = $staff ? e($staff->name) : 'Staf';
        $staffRole = $staff && $staff->role ? strtoupper($staff->role) : 'STAFF';

        $text = "👋 <b>Ayyanet Ticketing Bot</b>\n" .
            "👤 Petugas: <b>{$staffName}</b> [<code>{$staffRole}</code>]\n\n" .
            ($extraText ? "{$extraText}\n\n" : "") .
            "Silakan pilih aksi yang ingin dilakukan:";

        $keyboard = TelegramService::buildInlineKeyboard([
            [
                ['text' => '👤 Tambah Customer', 'callback_data' => 'menu_add_customer'],
                ['text' => '🎫 Bikin Ticket', 'callback_data' => 'menu_create_ticket'],
            ],
            [
                ['text' => '🧹 Bersihkan Layar', 'callback_data' => 'menu_clean'],
                ['text' => '🚪 Logout', 'callback_data' => 'menu_logout'],
            ]
        ]);

        $this->telegram->sendMessage($chatId, $text, $keyboard);
    }

    // ==========================================
    // CUSTOMER WIZARD
    // ==========================================

    protected function startAddCustomerWizard(int|string $chatId): void
    {
        $this->setState($chatId, [
            'step' => 'CUST_NAME',
            'data' => [],
        ]);

        $text = "👤 <b>Tambah Customer Baru (1/4)</b>\n\n" .
            "Masukkan <b>Nama Lengkap</b> customer:\n\n" .
            "<i>(Ketik /cancel kapan saja untuk membatalkan)</i>";

        $keyboard = TelegramService::buildInlineKeyboard([
            [['text' => '❌ Batal', 'callback_data' => 'cancel_flow']]
        ]);

        $this->telegram->sendMessage($chatId, $text, $keyboard);
    }

    protected function processCustomerName(int|string $chatId, array $state, string $text): void
    {
        $state['data']['name'] = $text;
        $state['step'] = 'CUST_PHONE';
        $this->setState($chatId, $state);

        $msg = "📞 <b>Tambah Customer Baru (2/4)</b>\n\n" .
            "Nama: <b>" . e($text) . "</b>\n\n" .
            "Masukkan <b>Nomor Telepon / WhatsApp</b> (contoh: <code>081234567890</code>):";

        $keyboard = TelegramService::buildInlineKeyboard([
            [['text' => '❌ Batal', 'callback_data' => 'cancel_flow']]
        ]);

        $this->telegram->sendMessage($chatId, $msg, $keyboard);
    }

    protected function processCustomerPhone(int|string $chatId, array $state, string $text): void
    {
        $state['data']['phone'] = $text;
        $state['step'] = 'CUST_ADDRESS';
        $this->setState($chatId, $state);

        $msg = "📍 <b>Tambah Customer Baru (3/4)</b>\n\n" .
            "Nomor Telepon: <b>" . e($text) . "</b>\n\n" .
            "Masukkan <b>Alamat Lengkap</b> customer:";

        $keyboard = TelegramService::buildInlineKeyboard([
            [['text' => '❌ Batal', 'callback_data' => 'cancel_flow']]
        ]);

        $this->telegram->sendMessage($chatId, $msg, $keyboard);
    }

    protected function processCustomerAddress(int|string $chatId, array $state, string $text): void
    {
        $state['data']['address'] = $text;
        $state['step'] = 'CUST_PACKAGE';
        $this->setState($chatId, $state);

        $msg = "📦 <b>Tambah Customer Baru (4/4)</b>\n\n" .
            "Masukkan <b>Paket Internet</b> (contoh: <code>Home 10Mbps</code> / <code>Office 50Mbps</code>) atau ketik <code>-</code> jika belum ada:";

        $keyboard = TelegramService::buildInlineKeyboard([
            [['text' => '❌ Batal', 'callback_data' => 'cancel_flow']]
        ]);

        $this->telegram->sendMessage($chatId, $msg, $keyboard);
    }

    protected function processCustomerPackage(int|string $chatId, array $state, string $text): void
    {
        $package = trim($text) === '-' ? null : $text;
        $state['data']['package'] = $package;

        // Save customer
        $customer = Customer::create([
            'name' => $state['data']['name'],
            'phone' => $state['data']['phone'],
            'address' => $state['data']['address'],
            'package' => $state['data']['package'],
        ]);

        $this->resetState($chatId);

        $msg = "✅ <b>Customer Berhasil Ditambahkan!</b>\n\n" .
            "🆔 <b>Customer ID:</b> <code>{$customer->customer_id}</code>\n" .
            "👤 <b>Nama:</b> " . e($customer->name) . "\n" .
            "📞 <b>Telepon:</b> " . e($customer->phone) . "\n" .
            "📍 <b>Alamat:</b> " . e($customer->address) . "\n" .
            "📦 <b>Paket:</b> " . e($customer->package ?? '—') . "\n\n" .
            "Apa yang ingin Anda lakukan selanjutnya?";

        $keyboard = TelegramService::buildInlineKeyboard([
            [['text' => '🎫 Buat Tiket untuk Customer Ini', 'callback_data' => "select_cust_{$customer->id}"]],
            [['text' => '👤 Tambah Customer Lain', 'callback_data' => 'menu_add_customer']],
            [['text' => '🏠 Menu Utama', 'callback_data' => 'menu_main']],
        ]);

        $this->telegram->sendMessage($chatId, $msg, $keyboard);
    }

    // ==========================================
    // TICKET WIZARD
    // ==========================================

    protected function startCreateTicketWizard(int|string $chatId): void
    {
        $this->setState($chatId, [
            'step' => 'TICK_SEARCH_CUSTOMER',
            'data' => [],
        ]);

        $recentCustomers = Customer::latest()->limit(5)->get();
        $buttons = [];

        foreach ($recentCustomers as $cust) {
            $buttons[] = [
                ['text' => "👤 {$cust->name} ({$cust->phone})", 'callback_data' => "select_cust_{$cust->id}"]
            ];
        }

        $buttons[] = [['text' => '➕ Daftarkan Customer Baru Dulu', 'callback_data' => 'menu_add_customer']];
        $buttons[] = [['text' => '❌ Batal', 'callback_data' => 'cancel_flow']];

        $msg = "🎫 <b>Bikin Ticket Baru (1/6)</b>\n\n" .
            "Pilih customer dari daftar terbaru di bawah, atau ketik <b>Nama / No Telepon / Customer ID</b> untuk mencari:";

        $this->telegram->sendMessage($chatId, $msg, TelegramService::buildInlineKeyboard($buttons));
    }

    protected function processTicketCustomerSearch(int|string $chatId, array $state, string $query): void
    {
        $query = trim($query);
        $matches = Customer::where('name', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->orWhere('customer_id', 'like', "%{$query}%")
            ->limit(5)
            ->get();

        if ($matches->isEmpty()) {
            $msg = "🔍 Customer dengan kata kunci <b>\"" . e($query) . "\"</b> tidak ditemukan.\n\n" .
                "Silakan ketik kata kunci lain untuk mencari lagi, atau daftarkan customer baru:";

            $keyboard = TelegramService::buildInlineKeyboard([
                [['text' => '➕ Tambah Customer Baru', 'callback_data' => 'menu_add_customer']],
                [['text' => '❌ Batal', 'callback_data' => 'cancel_flow']],
            ]);

            $this->telegram->sendMessage($chatId, $msg, $keyboard);
            return;
        }

        $buttons = [];
        foreach ($matches as $cust) {
            $buttons[] = [
                ['text' => "👤 {$cust->name} ({$cust->phone})", 'callback_data' => "select_cust_{$cust->id}"]
            ];
        }
        $buttons[] = [['text' => '❌ Batal', 'callback_data' => 'cancel_flow']];

        $msg = "🔍 Ditemukan " . count($matches) . " customer untuk <b>\"" . e($query) . "\"</b>:\nSilakan pilih customer:";

        $this->telegram->sendMessage($chatId, $msg, TelegramService::buildInlineKeyboard($buttons));
    }

    protected function selectCustomerForTicket(int|string $chatId, int $customerId): void
    {
        $customer = Customer::find($customerId);
        if (!$customer) {
            $this->telegram->sendMessage($chatId, '❌ Customer tidak ditemukan.');
            $this->startCreateTicketWizard($chatId);
            return;
        }

        $state = $this->getState($chatId);
        $state['data']['customer_id'] = $customer->id;
        $state['data']['customer_name'] = $customer->name;
        $state['data']['customer_phone'] = $customer->phone;
        $state['step'] = 'TICK_TITLE';
        $this->setState($chatId, $state);

        $msg = "📝 <b>Bikin Ticket Baru (2/6)</b>\n\n" .
            "Customer: <b>" . e($customer->name) . " (" . e($customer->phone) . ")</b>\n" .
            "Alamat: " . e($customer->address) . "\n\n" .
            "Masukkan <b>Judul Kendala</b> (contoh: <code>Internet mati total</code> / <code>Lampu LOS merah</code>):";

        $keyboard = TelegramService::buildInlineKeyboard([
            [['text' => '❌ Batal', 'callback_data' => 'cancel_flow']]
        ]);

        $this->telegram->sendMessage($chatId, $msg, $keyboard);
    }

    protected function processTicketTitle(int|string $chatId, array $state, string $text): void
    {
        $state['data']['title'] = $text;
        $state['step'] = 'TICK_DESC';
        $this->setState($chatId, $state);

        $msg = "📄 <b>Bikin Ticket Baru (3/6)</b>\n\n" .
            "Judul: <b>" . e($text) . "</b>\n\n" .
            "Masukkan <b>Deskripsi Kendala</b> (kronologi/detail keluhan), atau ketik <code>-</code> / klik tombol <b>Lewati</b> di bawah:";

        $keyboard = TelegramService::buildInlineKeyboard([
            [['text' => '⏭️ Lewati Deskripsi', 'callback_data' => 'skip_desc']],
            [['text' => '❌ Batal', 'callback_data' => 'cancel_flow']],
        ]);

        $this->telegram->sendMessage($chatId, $msg, $keyboard);
    }

    protected function processTicketDescription(int|string $chatId, array $state, string $text): void
    {
        $textTrim = trim($text);
        // If skipped or empty, fallback to title
        $description = ($textTrim === '-' || $textTrim === '') ? $state['data']['title'] : $textTrim;
        $state['data']['description'] = $description;

        // Auto-detect category & priority
        $auto = TicketClassifier::classify($state['data']['title'], $description);
        $state['data']['auto_category'] = $auto['category'];
        $state['data']['auto_priority'] = $auto['priority'];

        $state['step'] = 'TICK_CATEGORY';
        $this->setState($chatId, $state);

        $msg = "🏷️ <b>Bikin Ticket Baru (4/6)</b>\n\n" .
            "Judul: <b>" . e($state['data']['title']) . "</b>\n" .
            "💡 <i>Sistem merekomendasikan kategori: <b>{$auto['category']}</b></i>\n\n" .
            "Pilih <b>Kategori</b> tiket:";

        $keyboard = TelegramService::buildInlineKeyboard([
            [
                ['text' => ($auto['category'] === 'Internet' ? '⭐ Internet' : 'Internet'), 'callback_data' => 'cat_Internet'],
                ['text' => ($auto['category'] === 'Hardware' ? '⭐ Hardware' : 'Hardware'), 'callback_data' => 'cat_Hardware'],
            ],
            [
                ['text' => ($auto['category'] === 'Billing' ? '⭐ Billing' : 'Billing'), 'callback_data' => 'cat_Billing'],
                ['text' => ($auto['category'] === 'Layanan' ? '⭐ Layanan' : 'Layanan'), 'callback_data' => 'cat_Layanan'],
            ],
            [
                ['text' => ($auto['category'] === 'Other' ? '⭐ Other' : 'Other'), 'callback_data' => 'cat_Other'],
            ],
            [
                ['text' => '❌ Batal', 'callback_data' => 'cancel_flow'],
            ]
        ]);

        $this->telegram->sendMessage($chatId, $msg, $keyboard);
    }

    protected function processTicketCategory(int|string $chatId, string $category): void
    {
        $state = $this->getState($chatId);
        $state['data']['category'] = $category;
        $autoPrio = $state['data']['auto_priority'] ?? 'Medium';

        $state['step'] = 'TICK_PRIORITY';
        $this->setState($chatId, $state);

        $msg = "⚡ <b>Bikin Ticket Baru (5/6)</b>\n\n" .
            "Kategori terpilih: <b>" . e($category) . "</b>\n" .
            "💡 <i>Sistem merekomendasikan prioritas: <b>{$autoPrio}</b></i>\n\n" .
            "Pilih <b>Tingkat Prioritas</b>:";

        $keyboard = TelegramService::buildInlineKeyboard([
            [
                ['text' => ($autoPrio === 'Low' ? '⭐ 🟢 Low' : '🟢 Low'), 'callback_data' => 'prio_Low'],
                ['text' => ($autoPrio === 'Medium' ? '⭐ 🟡 Medium' : '🟡 Medium'), 'callback_data' => 'prio_Medium'],
                ['text' => ($autoPrio === 'High' ? '⭐ 🔴 High' : '🔴 High'), 'callback_data' => 'prio_High'],
            ],
            [
                ['text' => '❌ Batal', 'callback_data' => 'cancel_flow'],
            ]
        ]);

        $this->telegram->sendMessage($chatId, $msg, $keyboard);
    }

    protected function processTicketPriority(int|string $chatId, string $priority): void
    {
        $state = $this->getState($chatId);
        $state['data']['priority'] = $priority;
        $state['step'] = 'TICK_OLT';
        $this->setState($chatId, $state);

        $msg = "📍 <b>Bikin Ticket Baru (6/6)</b>\n\n" .
            "Prioritas: <b>" . e($priority) . "</b>\n\n" .
            "Masukkan nama <b>OLT</b> (contoh: <code>OLT-01</code>) atau klik tombol Lewati:";

        $keyboard = TelegramService::buildInlineKeyboard([
            [['text' => '⏭️ Lewati OLT & Lokasi', 'callback_data' => 'skip_olt_loc']],
            [['text' => '⏭️ Lewati OLT saja', 'callback_data' => 'skip_olt']],
            [['text' => '❌ Batal', 'callback_data' => 'cancel_flow']],
        ]);

        $this->telegram->sendMessage($chatId, $msg, $keyboard);
    }

    protected function processTicketOlt(int|string $chatId, array $state, string $text): void
    {
        $olt = trim($text) === '-' ? null : $text;
        $state['data']['olt'] = $olt;
        $state['step'] = 'TICK_LOCATION';
        $this->setState($chatId, $state);

        $msg = "📍 <b>Bikin Ticket Baru (6/6 - Lanjutan)</b>\n\n" .
            "OLT: <b>" . e($olt ?? '—') . "</b>\n\n" .
            "Masukkan <b>Lokasi / Port</b> (contoh: <code>Port 12 / ODP-05</code>) atau ketik <code>-</code> / klik tombol Lewati:";

        $keyboard = TelegramService::buildInlineKeyboard([
            [['text' => '⏭️ Lewati Lokasi/Port', 'callback_data' => 'skip_location']],
            [['text' => '❌ Batal', 'callback_data' => 'cancel_flow']],
        ]);

        $this->telegram->sendMessage($chatId, $msg, $keyboard);
    }

    protected function processTicketLocation(int|string $chatId, array $state, string $text, ?User $staff = null): void
    {
        $location = trim($text) === '-' ? null : $text;
        $state['data']['location'] = $location;

        $this->finalizeTicketCreation($chatId, $state['data'], $staff);
    }

    protected function finalizeTicketCreation(int|string $chatId, array $data, ?User $staff = null): void
    {
        $customerId = $data['customer_id'] ?? null;
        $customer = Customer::find($customerId);

        if (!$customer) {
            $this->resetState($chatId);
            $this->telegram->sendMessage($chatId, '❌ Gagal: Data customer tidak ditemukan.');
            $this->sendMainMenu($chatId, null, $staff);
            return;
        }

        // Determine creator user (always prioritize authenticated staff)
        $creator = $staff ?? $this->getAuthenticatedStaff($chatId);
        if (!$creator) {
            $defaultAdminId = config('telegram.default_admin_id');
            if ($defaultAdminId) {
                $creator = User::find($defaultAdminId);
            }
            if (!$creator) {
                $creator = User::whereIn('role', ['admin', 'cs'])->first() ?? User::first();
            }
        }

        // Create ticket
        $ticket = Ticket::create([
            'ticket_number' => 'TCK-' . time(),
            'customer_id'   => $customer->id,
            'created_by'    => $creator?->id,
            'title'         => $data['title'],
            'description'   => $data['description'],
            'category'      => $data['category'] ?? 'Internet',
            'priority'      => $data['priority'] ?? 'Medium',
            'status'        => 'Open',
            'olt'           => $data['olt'] ?? null,
            'location'      => $data['location'] ?? null,
        ]);

        // Send notifications to staff like TicketController
        try {
            $staffUsers = User::whereIn('role', ['admin', 'cs'])->get();
            foreach ($staffUsers as $user) {
                if (!empty($user->email)) {
                    Mail::to($user->email)->queue(new TicketCreated($ticket));
                }
                $user->notify(new TicketCreatedNotification($ticket));
            }
        } catch (\Exception $e) {
            Log::warning('Telegram ticket creation notification error: ' . $e->getMessage());
        }

        $this->resetState($chatId);

        $creatorName = $creator ? e($creator->name) : 'Sistem';

        $msg = "🎉 <b>Ticket Berhasil Dibuat!</b>\n\n" .
            "🎫 <b>Nomor Tiket:</b> <code>{$ticket->ticket_number}</code>\n" .
            "👤 <b>Customer:</b> " . e($customer->name) . " (" . e($customer->phone) . ")\n" .
            "👨‍💼 <b>Dibuat Oleh:</b> {$creatorName}\n" .
            "📝 <b>Judul:</b> " . e($ticket->title) . "\n" .
            "🏷️ <b>Kategori:</b> {$ticket->category}\n" .
            "⚡ <b>Prioritas:</b> {$ticket->priority}\n" .
            "📊 <b>Status:</b> <code>{$ticket->status}</code>\n" .
            "📍 <b>OLT / Port:</b> " . e($ticket->olt ? "{$ticket->olt} / {$ticket->location}" : ($ticket->location ?? '—')) . "\n\n" .
            "Tiket telah didaftarkan ke sistem dan notifikasi telah dikirimkan ke tim teknisi.";

        $keyboard = TelegramService::buildInlineKeyboard([
            [['text' => '🎫 Buat Tiket Lain', 'callback_data' => 'menu_create_ticket']],
            [['text' => '🏠 Menu Utama', 'callback_data' => 'menu_main']],
        ]);

        $this->telegram->sendMessage($chatId, $msg, $keyboard);
    }

    // ==========================================
    // STATE HELPERS
    // ==========================================

    protected function getStateKey(int|string $chatId): string
    {
        return "telegram_state_{$chatId}";
    }

    protected function getState(int|string $chatId): array
    {
        return Cache::get($this->getStateKey($chatId), []);
    }

    protected function setState(int|string $chatId, array $state): void
    {
        Cache::put($this->getStateKey($chatId), $state, now()->addHours(1));
    }

    protected function resetState(int|string $chatId): void
    {
        Cache::forget($this->getStateKey($chatId));
    }
}
