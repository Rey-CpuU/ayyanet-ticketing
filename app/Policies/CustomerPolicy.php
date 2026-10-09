<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    /**
     * Field technicians get read-only access (they need address/package details on site).
     */
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Customer $customer): bool
    {
        return $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin', 'cs');
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->hasRole('admin', 'cs');
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Customer $customer): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Customer $customer): bool
    {
        return $user->isAdmin();
    }
}
