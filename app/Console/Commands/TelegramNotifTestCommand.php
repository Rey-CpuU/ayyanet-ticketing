<?php

namespace App\Console\Commands;

use App\Services\TelegramNotificationService;
use Illuminate\Console\Command;

class TelegramNotifTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:notif-test {chat_id? : Target Group or Channel Chat ID (optional, defaults to TELEGRAM_NOTIF_GROUP_ID)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test notification alert to Telegram staff group';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $token = TelegramNotificationService::getToken();
        $targetChatId = $this->argument('chat_id') ?: TelegramNotificationService::getGroupId();

        $this->info("🤖 Testing Telegram Notification Bot...");
        $this->line("Token: " . ($token ? substr($token, 0, 10) . '...' : '<fg=red>Not Set</>'));
        $this->line("Target Group ID: " . ($targetChatId ?: '<fg=red>Not Set</>'));

        if (empty($token)) {
            $this->error('Error: TELEGRAM_NOTIF_BOT_TOKEN (or TELEGRAM_BOT_TOKEN) is not set in .env!');
            return self::FAILURE;
        }

        if (empty($targetChatId)) {
            $this->error('Error: TELEGRAM_NOTIF_GROUP_ID is not set in .env!');
            $this->line('Hint: Add your notification bot to your Telegram group, send a message, or pass the group ID: php artisan telegram:notif-test -100xxxxxxxxxx');
            return self::FAILURE;
        }

        $this->info("Sending test notification message to {$targetChatId}...");
        $result = TelegramNotificationService::sendTestNotification($targetChatId);

        if ($result && ($result['ok'] ?? false)) {
            $this->info('✅ Test notification sent successfully to Telegram group!');
            $this->line(json_encode($result, JSON_PRETTY_PRINT));
            return self::SUCCESS;
        }

        $this->error('❌ Failed to send test notification. Response:');
        $this->line(json_encode($result, JSON_PRETTY_PRINT));
        return self::FAILURE;
    }
}
