<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'phone',
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

    /**
     * Validate a role change: nobody may change their own role, and the last admin cannot be demoted.
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

        if ($target->isAdmin() && static::where('role', 'admin')->count() <= 1) {
            return 'Role admin terakhir tidak dapat diturunkan.';
        }

        return null;
    }

    public function createdTickets()
    {
        return $this->hasMany(Ticket::class, 'created_by');
    }

    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
