<?php

namespace App\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

/**
 * Strips sensitive / PII values from any log context before it is written,
 * regardless of which channel the message lands on. Keys are matched
 * case-insensitively; values are replaced with '[REDACTED]'.
 */
class RedactSensitiveContext implements ProcessorInterface
{
    /** Keys whose values must never appear in log files. */
    protected array $sensitiveKeys = [
        'password', 'password_confirmation', 'current_password',
        'email', 'name', 'display_name',
        'token', 'access_token', 'refresh_token', 'api_token',
        'remember_token', 'secret', 'authorization',
        'ip', 'ip_address', 'remote_addr',
        'phone', 'phone_number', 'address',
        'user_id', 'platform_user_id',
        'card_number', 'cvv',
    ];

    public function __invoke(LogRecord $record): LogRecord
    {
        $contextRedacted = ! empty($record->context)
            ? $this->redact($record->context)
            : $record->context;

        $extraRedacted = ! empty($record->extra)
            ? $this->redact($record->extra)
            : $record->extra;

        // LogRecord::$context is readonly in Monolog 3.x, so a fresh
        // record must be returned with the redacted values.
        return new LogRecord(
            datetime: $record->datetime,
            channel: $record->channel,
            level: $record->level,
            message: $record->message,
            context: $contextRedacted,
            extra: $extraRedacted,
            formatted: $record->formatted,
        );
    }

    protected function redact(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->redact($value);
                continue;
            }

            if ($this->isSensitive((string) $key)) {
                $data[$key] = '[REDACTED]';
            }
        }

        return $data;
    }

    protected function isSensitive(string $key): bool
    {
        $key = strtolower($key);

        foreach ($this->sensitiveKeys as $sensitive) {
            if ($key === $sensitive || str_contains($key, $sensitive)) {
                return true;
            }
        }

        return false;
    }
}
