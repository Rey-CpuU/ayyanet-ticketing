<?php

namespace App\Support;

class TicketClassifier
{
    /**
     * Category keyword rules, checked in order. First match wins.
     */
    private const CATEGORY_RULES = [
        'Hardware' => [
            'modem', 'router', 'ont', 'adaptor', 'indikator', 'kabel', 'perangkat',
            'firmware', 'lampu', 'wan', 'lan', 'port',
        ],
        'Billing' => [
            'tagihan', 'bayar', 'pembayaran', 'invoice', 'biaya', 'harga',
            'tunggakan', 'denda', 'kwitansi',
        ],
        'Layanan' => [
            'pindah', 'upgrade', 'downgrade', 'pasang', 'instalasi', 'lupa',
            'password', 'permintaan', 'paket', 'berlangganan', 'info', 'reset',
        ],
        'Internet' => [
            'internet', 'koneksi', 'putus', 'lambat', 'lemot', 'kecepatan',
            'sinyal', 'ping', 'offline', 'download', 'upload', 'browsing',
            'streaming', 'coverage', 'wifi', 'wi-fi', 'wireless',
        ],
    ];

    /**
     * Priority rules, checked high first, then medium, else Low.
     */
    private const HIGH_PRIORITY = [
        'mati total', 'tidak ada koneksi', 'tidak bisa', 'gagal', 'semua',
        'seluruh', 'area', 'outage', 'down', 'kritis', 'urgent', 'putus total',
    ];

    private const MEDIUM_PRIORITY = [
        'lambat', 'lemot', 'putus', 'sering', 'intermittent', 'delay', 'turun',
        'ping', 'lambat sekali',
    ];

    public static function classify(string $title, string $description = ''): array
    {
        $text = mb_strtolower(trim($title . ' ' . $description));

        return [
            'category' => self::detectCategory($text),
            'priority' => self::detectPriority($text),
        ];
    }

    private static function detectCategory(string $text): string
    {
        foreach (self::CATEGORY_RULES as $category => $keywords) {
            if (self::matchesAny($text, $keywords)) {
                return $category;
            }
        }

        return 'Other';
    }

    private static function detectPriority(string $text): string
    {
        if (self::matchesAny($text, self::HIGH_PRIORITY)) {
            return 'High';
        }

        if (self::matchesAny($text, self::MEDIUM_PRIORITY)) {
            return 'Medium';
        }

        return 'Low';
    }

    private static function matchesAny(string $text, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (preg_match('/\b' . preg_quote($keyword, '/') . '\b/u', $text)) {
                return true;
            }
        }

        return false;
    }
}
