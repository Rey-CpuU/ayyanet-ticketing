<?php

namespace App\Console\Commands;

use App\Services\TelegramConversationManager;
use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramPollCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:poll {--timeout=20 : Polling timeout in seconds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run long-polling loop to process Telegram Bot updates locally';

    /**
     * Execute the console command.
     */
    public function handle(TelegramService $telegram, TelegramConversationManager $conversationManager): int
    {
        if (!$telegram->isConfigured()) {
            $this->error('TELEGRAM_BOT_TOKEN is not configured in your .env file.');
            return self::FAILURE;
        }

        $this->info('🚀 Starting Telegram Bot polling...');
        $this->info('Press Ctrl+C to stop.');

        $offset = 0;
        $timeout = (int) $this->option('timeout');

        while (true) {
            try {
                $updates = $telegram->getUpdates($offset, $timeout);

                foreach ($updates as $update) {
                    $offset = $update['update_id'] + 1;

                    $sender = $update['message']['from']['username'] 
                        ?? $update['message']['from']['first_name'] 
                        ?? $update['callback_query']['from']['first_name'] 
                        ?? 'Unknown';

                    $text = $update['message']['text'] 
                        ?? ($update['callback_query']['data'] ?? '[Event]');

                    $this->line("<fg=cyan>[Update #{$update['update_id']}]</> <fg=yellow>{$sender}:</> {$text}");

                    $conversationManager->handleUpdate($update);
                }
            } catch (\Exception $e) {
                $this->error('Polling error: ' . $e->getMessage());
                sleep(2);
            }
        }

        return self::SUCCESS;
    }
}
