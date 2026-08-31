<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotificationService
{
    /**
     * Get the bot token for notifications (falls back to main bot token if not set).
     */
    public static function getToken(): ?string
    {
        return config('telegram.notif_bot_token') ?: config('telegram.bot_token');
    }

    /**
     * Get the default group / channel ID for notifications.
     */
    public static function getGroupId(): ?string
    {
        return config('telegram.notif_group_id');
    }

    /**
     * Check if notification service is enabled and configured.
     */
    public static function isConfigured(): bool
    {
        return config('telegram.notif_enabled', true) 
            && !empty(self::getToken()) 
            && !empty(self::getGroupId());
    }

    /**
     * Send raw HTML message to Telegram group / chat.
     */
    public static function sendMessage(string $text, ?string $chatId = null, ?array $replyMarkup = null): ?array
    {
        $token = self::getToken();
        $targetChatId = $chatId ?: self::getGroupId();

        if (empty($token) || empty($targetChatId)) {
            Log::info('Telegram notification skipped: Token or Group ID is not configured.');
            return null;
        }

        if (!config('telegram.notif_enabled', true)) {
            return null;
        }

        $apiUrl = "https://api.telegram.org/bot{$token}/sendMessage";

        $payload = [
            'chat_id'    => $targetChatId,
            'text'       => $text,
            'parse_mode' => 'HTML',
        ];

        if ($replyMarkup) {
            $payload['reply_markup'] = $replyMarkup;
        }

        try {
            $response = Http::timeout(10)->post($apiUrl, $payload);
            if (!$response->successful()) {
                Log::error('Telegram notification error: ' . $response->body());
            }
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Telegram notification exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send notification for a newly created ticket.
     */
    public static function sendTicketCreated(Ticket $ticket): ?array
    {
        $ticket->loadMissing(['customer', 'creator']);

        $customerName = e($ticket->customer->name ?? 'Unknown');
        $customerPhone = e($ticket->customer->phone ?? '—');
        $creatorName = e($ticket->creator->name ?? 'System');
        $oltInfo = $ticket->olt 
            ? e($ticket->olt . ($ticket->location ? " / {$ticket->location}" : ''))
            : (e($ticket->location ?? '—'));

        $prioEmoji = match ($ticket->priority) {
            'High'   => '🔴',
            'Medium' => '🟡',
            default  => '🟢',
        };

        $timeStr = $ticket->created_at 
            ? $ticket->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') . ' WIB'
            : now()->timezone('Asia/Jakarta')->format('d M Y H:i') . ' WIB';

        $text = "🆕 <b>TIKET BARU MASUK!</b>\n" .
            "━━━━━━━━━━━━━━━━━━━\n" .
            "🎫 <b>No. Tiket:</b> <code>{$ticket->ticket_number}</code>\n" .
            "👤 <b>Customer:</b> {$customerName} (<code>{$customerPhone}</code>)\n" .
            "📝 <b>Judul:</b> " . e($ticket->title) . "\n" .
            "🏷️ <b>Kategori:</b> " . e($ticket->category ?? 'Internet') . "\n" .
            "⚡ <b>Prioritas:</b> {$prioEmoji} <b>" . e($ticket->priority) . "</b>\n" .
            "📍 <b>OLT / Port:</b> {$oltInfo}\n" .
            "👨‍💼 <b>Dibuat Oleh:</b> {$creatorName}\n" .
            "⏱️ <b>Waktu:</b> {$timeStr}\n" .
            "━━━━━━━━━━━━━━━━━━━\n" .
            "<i>Mohon tim teknisi/CS untuk segera melakukan pengecekan.</i>";

        return self::sendMessage($text);
    }

    /**
     * Send notification when a ticket status is changed.
     */
    public static function sendTicketStatusChanged(Ticket $ticket, string $oldStatus, string $newStatus, ?string $updatedByName = null): ?array
    {
        $ticket->loadMissing(['customer', 'assignee']);

        $customerName = e($ticket->customer->name ?? 'Unknown');
        $assigneeName = e($ticket->assignee->name ?? 'Belum Ditugaskan');
        $updaterName = e($updatedByName ?: 'Staff / Admin');

        $headerText = match ($newStatus) {
            'Solved'           => '✅ <b>STATUS TIKET: SELESAI (SOLVED)!</b>',
            'Closed'           => '🔒 <b>STATUS TIKET: DITUTUP (CLOSED)!</b>',
            'Checking'         => '🔍 <b>STATUS TIKET: PROSES PENGECEKAN (CHECKING)!</b>',
            'Waiting Customer' => '⏳ <b>STATUS TIKET: MENUNGGU CUSTOMER!</b>',
            'Escalated'        => '⚠️ <b>STATUS TIKET: DITINGKATKAN (ESCALATED)!</b>',
            default            => '🔄 <b>STATUS TIKET DIPERBARUI!</b>',
        };

        $statusEmoji = match ($newStatus) {
            'Solved'           => '🟢',
            'Closed'           => '⚪',
            'Checking'         => '🟡',
            'Waiting Customer' => '🟠',
            'Escalated'        => '🔴',
            default            => '🔵',
        };

        $timeStr = now()->timezone('Asia/Jakarta')->format('d M Y H:i') . ' WIB';

        $text = "{$headerText}\n" .
            "━━━━━━━━━━━━━━━━━━━\n" .
            "🎫 <b>No. Tiket:</b> <code>{$ticket->ticket_number}</code>\n" .
            "👤 <b>Customer:</b> {$customerName}\n" .
            "📝 <b>Judul:</b> " . e($ticket->title) . "\n" .
            "📊 <b>Perubahan:</b> <s>" . e($oldStatus) . "</s> ➔ {$statusEmoji} <b>" . e($newStatus) . "</b>\n" .
            "👨‍💻 <b>Diubah Oleh:</b> {$updaterName}\n" .
            "🛠️ <b>Petugas / Teknisi:</b> {$assigneeName}\n" .
            "⏱️ <b>Waktu Update:</b> {$timeStr}\n" .
            "━━━━━━━━━━━━━━━━━━━";

        return self::sendMessage($text);
    }

    /**
     * Send notification when a ticket is assigned / reassigned.
     */
    public static function sendTicketAssigneeChanged(Ticket $ticket, string $oldAssignee, string $newAssignee, ?string $updatedByName = null): ?array
    {
        $ticket->loadMissing(['customer']);

        $customerName = e($ticket->customer->name ?? 'Unknown');
        $updaterName = e($updatedByName ?: 'Staff / Admin');
        $timeStr = now()->timezone('Asia/Jakarta')->format('d M Y H:i') . ' WIB';

        $text = "👤 <b>PENUGASAN TIKET (ASSIGNMENT)!</b>\n" .
            "━━━━━━━━━━━━━━━━━━━\n" .
            "🎫 <b>No. Tiket:</b> <code>{$ticket->ticket_number}</code>\n" .
            "👤 <b>Customer:</b> {$customerName}\n" .
            "📝 <b>Judul:</b> " . e($ticket->title) . "\n" .
            "🛠️ <b>Petugas:</b> <s>" . e($oldAssignee) . "</s> ➔ <b>" . e($newAssignee) . "</b>\n" .
            "👨‍💻 <b>Ditugaskan Oleh:</b> {$updaterName}\n" .
            "⏱️ <b>Waktu:</b> {$timeStr}\n" .
            "━━━━━━━━━━━━━━━━━━━";

        return self::sendMessage($text);
    }

    /**
     * Send a test notification.
     */
    public static function sendTestNotification(?string $chatId = null): ?array
    {
        $timeStr = now()->timezone('Asia/Jakarta')->format('d M Y H:i:s') . ' WIB';
        $text = "🤖 <b>Ayyanet Bot Notifikasi — Test Alert</b>\n" .
            "━━━━━━━━━━━━━━━━━━━\n" .
            "✅ Koneksi Bot Notifikasi ke Grup Telegram berhasil!\n" .
            "⏱️ <b>Waktu Server:</b> {$timeStr}\n\n" .
            "<i>Bot ini siap mengirimkan notifikasi setiap kali ada Tiket Baru masuk atau Tiket Selesai (Solved/Closed).</i>";

        return self::sendMessage($text, $chatId);
    }
}
