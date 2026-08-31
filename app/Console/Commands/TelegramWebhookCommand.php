<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TelegramWebhookCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:webhook {action=info : Action: info, set, or delete} {url? : Webhook URL (required for set)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage Telegram Bot webhook settings';

    /**
     * Execute the console command.
     */
    public function handle(TelegramService $telegram): int
    {
        if (!$telegram->isConfigured()) {
            $this->error('TELEGRAM_BOT_TOKEN is not configured in your .env file.');
            return self::FAILURE;
        }

        $action = strtolower($this->argument('action'));
        $token = config('telegram.bot_token');
        $apiUrl = "https://api.telegram.org/bot{$token}";

        if ($action === 'set') {
            $url = $this->argument('url') ?: url('/telegram/webhook');
            $secret = config('telegram.webhook_secret');
            
            $this->info("Setting webhook to: {$url}");
            $res = $telegram->setWebhook($url, $secret);
            $this->line(json_encode($res, JSON_PRETTY_PRINT));
            return self::SUCCESS;
        }

        if ($action === 'delete') {
            $this->info('Deleting webhook...');
            $res = Http::post("{$apiUrl}/deleteWebhook")->json();
            $this->line(json_encode($res, JSON_PRETTY_PRINT));
            return self::SUCCESS;
        }

        // Info / Status
        $this->info('Checking webhook info...');
        $res = Http::get("{$apiUrl}/getWebhookInfo")->json();
        $this->line(json_encode($res, JSON_PRETTY_PRINT));

        return self::SUCCESS;
    }
}
