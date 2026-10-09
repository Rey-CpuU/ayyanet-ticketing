<?php

namespace App\Models;

use Closure;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable([
    'name',
    'email',
    'role',
    'password',
])]

#[Hidden([
    'password',
    'remember_token',
])]

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLES = ['admin', 'cs', 'lapangan'];

    public function hasRole(string ...$roles): bool
    {
        return $this->role !== null && in_array($this->role, $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isStaff(): bool
    {
        return $this->hasRole(...self::ROLES);
    }

    public function isDeactivated(): bool
    {
        return $this->deactivated_at !== null;
    }

    public function isActive(): bool
    {
        return ! $this->isDeactivated();
    }

    /**
     * Accounts that may sign in, be assigned tickets and receive staff notifications.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('deactivated_at');
    }

    /**
     * Emails are stored and compared lowercase.
     */
    public static function normalizeEmail(mixed $email): mixed
    {
        return is_string($email) ? Str::lower(trim($email)) : $email;
    }

    /**
     * Case-insensitive "is this email already used by another account" check.
     */
    public static function emailTaken(string $email, ?int $ignoreId = null): bool
    {
        return static::query()
            ->whereRaw('LOWER(email) = ?', [static::normalizeEmail($email)])
            ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }

    /**
     * Validation rule rejecting an email that belongs to another account, ignoring case.
     */
    public static function uniqueEmailRule(?int $ignoreId = null): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($ignoreId) {
            if (is_string($value) && static::emailTaken($value, $ignoreId)) {
                $fail('Email ini sudah digunakan oleh akun lain.');
            }
        };
    }

    /**
     * Validate a role change: nobody may change their own role, and the last active admin cannot be demoted.
     * Returns an error message, or null when the change is allowed.
     */
    public static function roleChangeError(User $actor, User $target, string $newRole): ?string
    {
        if ($newRole === $target->role) {
            return null;
        }

        if ($actor->is($target)) {
            return 'Anda tidak dapat mengubah role akun Anda sendiri.';
        }

        if ($target->isAdmin() && $target->isActive() && static::lastActiveAdmin()) {
            return 'Role admin terakhir tidak dapat diturunkan.';
        }

        return null;
    }

    /**
     * Validate a deactivation: nobody may deactivate their own account, and the last active admin
     * must stay active. Returns an error message, or null when the deactivation is allowed.
     */
    public static function deactivationError(User $actor, User $target): ?string
    {
        if ($actor->is($target)) {
            return 'Anda tidak dapat menonaktifkan akun Anda sendiri.';
        }

        if ($target->isAdmin() && $target->isActive() && static::lastActiveAdmin()) {
            return 'Admin aktif terakhir tidak dapat dinonaktifkan.';
        }

        return null;
    }

    private static function lastActiveAdmin(): bool
    {
        return static::active()->where('role', 'admin')->count() <= 1;
    }

    protected function email(): Attribute
    {
        return Attribute::make(set: fn (?string $value) => static::normalizeEmail($value));
    }

    public function createdTickets()
    {
        return $this->hasMany(Ticket::class, 'created_by');
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'deactivated_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
