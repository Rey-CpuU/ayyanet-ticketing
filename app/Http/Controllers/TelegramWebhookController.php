<?php

namespace App\Http\Controllers;

use App\Services\TelegramConversationManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    protected TelegramConversationManager $conversationManager;

    public function __construct(TelegramConversationManager $conversationManager)
    {
        $this->conversationManager = $conversationManager;
    }

    /**
     * Handle incoming webhook updates from Telegram.
     */
    public function handle(Request $request): JsonResponse
    {
        $secret = config('telegram.webhook_secret');
        if (!empty($secret)) {
            $incomingSecret = $request->header('X-Telegram-Bot-Api-Secret-Token');
            if ($incomingSecret !== $secret) {
                Log::warning('Telegram webhook invalid secret token received.');
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }

        $update = $request->all();
        if (!empty($update)) {
            try {
                $this->conversationManager->handleUpdate($update);
            } catch (\Exception $e) {
                Log::error('Telegram webhook handling exception: ' . $e->getMessage(), [
                    'exception' => $e,
                    'update' => $update,
                ]);
            }
        }

        return response()->json(['ok' => true]);
    }
}
