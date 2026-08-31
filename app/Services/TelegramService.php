<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected ?string $token;
    protected string $apiUrl;

    public function __construct()
    {
        $this->token = config('telegram.bot_token');
        $this->apiUrl = "https://api.telegram.org/bot{$this->token}";
    }

    public function isConfigured(): bool
    {
        return !empty($this->token);
    }

    /**
     * Send text message with optional inline or reply keyboard.
     */
    public function sendMessage(
        int|string $chatId,
        string $text,
        ?array $replyMarkup = null,
        string $parseMode = 'HTML'
    ): ?array {
        if (!$this->isConfigured()) {
            Log::warning('Telegram bot token is not configured.');
            return null;
        }

        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => $parseMode,
        ];

        if ($replyMarkup !== null) {
            $payload['reply_markup'] = $replyMarkup;
        }

        try {
            $response = Http::timeout(10)->post("{$this->apiUrl}/sendMessage", $payload);
            if (!$response->successful()) {
                Log::error('Telegram API error (sendMessage): ' . $response->body());
            }
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Telegram sendMessage exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Answer callback query from inline buttons.
     */
    public function answerCallbackQuery(string $callbackQueryId, ?string $text = null, bool $showAlert = false): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $payload = [
            'callback_query_id' => $callbackQueryId,
            'show_alert' => $showAlert,
        ];

        if ($text) {
            $payload['text'] = $text;
        }

        try {
            $response = Http::timeout(10)->post("{$this->apiUrl}/answerCallbackQuery", $payload);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Telegram answerCallbackQuery exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete a single message.
     */
    public function deleteMessage(int|string $chatId, int $messageId): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        try {
            $response = Http::timeout(5)->post("{$this->apiUrl}/deleteMessage", [
                'chat_id' => $chatId,
                'message_id' => $messageId,
            ]);

            return $response->successful() && $response->json('ok', false);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete multiple messages in batch (up to 100).
     */
    public function deleteMessages(int|string $chatId, array $messageIds): bool
    {
        if (!$this->isConfigured() || empty($messageIds)) {
            return false;
        }

        try {
            $response = Http::timeout(10)->post("{$this->apiUrl}/deleteMessages", [
                'chat_id' => $chatId,
                'message_ids' => array_values($messageIds),
            ]);

            return $response->successful() && $response->json('ok', false);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get updates using long polling.
     */
    public function getUpdates(int $offset = 0, int $timeout = 30): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        try {
            $response = Http::timeout($timeout + 5)->get("{$this->apiUrl}/getUpdates", [
                'offset' => $offset,
                'timeout' => $timeout,
            ]);

            if ($response->successful() && $response->json('ok')) {
                return $response->json('result', []);
            }
        } catch (\Exception $e) {
            Log::error('Telegram getUpdates exception: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Set webhook URL.
     */
    public function setWebhook(string $url, ?string $secretToken = null): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $payload = ['url' => $url];
        if ($secretToken) {
            $payload['secret_token'] = $secretToken;
        }

        try {
            $response = Http::timeout(15)->post("{$this->apiUrl}/setWebhook", $payload);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Telegram setWebhook exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Helper to build inline keyboard.
     */
    public static function buildInlineKeyboard(array $buttons): array
    {
        return [
            'inline_keyboard' => $buttons,
        ];
    }

    /**
     * Helper to build reply keyboard markup.
     */
    public static function buildReplyKeyboard(array $buttons, bool $resize = true, bool $oneTime = false): array
    {
        return [
            'keyboard' => $buttons,
            'resize_keyboard' => $resize,
            'one_time_keyboard' => $oneTime,
        ];
    }

    /**
     * Helper to remove reply keyboard.
     */
    public static function removeKeyboard(): array
    {
        return [
            'remove_keyboard' => true,
        ];
    }
}
