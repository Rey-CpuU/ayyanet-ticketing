<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Timestamps are stored and compared in the application timezone (UTC); everything shown to people
 * (views, exports, emails, notifications, Telegram) is converted to the display timezone
 * (config app.display_timezone, default Asia/Jakarta). Datetime inputs typed by users are read in
 * the display timezone and converted back to the storage timezone.
 */
final class DisplayTime
{
    public const INPUT_FORMAT = 'Y-m-d\TH:i';

    public static function zone(): string
    {
        return (string) (config('app.display_timezone') ?: 'Asia/Jakarta');
    }

    public static function storageZone(): string
    {
        return (string) (config('app.timezone') ?: 'UTC');
    }

    /**
     * A copy of the instant in the display timezone.
     */
    public static function convert(?CarbonInterface $value): ?CarbonInterface
    {
        return $value?->copy()->setTimezone(self::zone());
    }

    public static function format(?CarbonInterface $value, string $format = 'd M Y, H:i', string $fallback = ''): string
    {
        return $value ? self::convert($value)->format($format) : $fallback;
    }

    /**
     * The current moment in the display timezone.
     */
    public static function now(): Carbon
    {
        return Carbon::now(self::zone());
    }

    /**
     * Start of the current week (Monday 00:00) in the display timezone, as an instant in the storage
     * timezone so it can be bound to queries directly.
     */
    public static function startOfWeek(): Carbon
    {
        return self::now()->startOfWeek(CarbonInterface::MONDAY)->setTimezone(self::storageZone());
    }

    /**
     * Read a wall-clock value typed by a user (e.g. a datetime-local input) in the display timezone
     * and return it in the storage timezone. Empty values give null.
     */
    public static function parseInput(mixed $value): ?Carbon
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return Carbon::parse($value, self::zone())->setTimezone(self::storageZone());
    }

    /**
     * Value for a datetime-local input, in the display timezone.
     */
    public static function toInput(?CarbonInterface $value): string
    {
        return self::format($value, self::INPUT_FORMAT);
    }

    /**
     * Bounds (storage timezone) of a calendar day picked in the display timezone ("Y-m-d").
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function dayBounds(string $date): array
    {
        $day = Carbon::createFromFormat('!Y-m-d', $date, self::zone());

        return [
            $day->copy()->startOfDay()->setTimezone(self::storageZone()),
            $day->copy()->endOfDay()->setTimezone(self::storageZone()),
        ];
    }
}
