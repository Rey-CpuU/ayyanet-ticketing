<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\Ticket;
use App\Policies\CustomerPolicy;
use App\Policies\TicketPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Ticket::class => TicketPolicy::class,
        Customer::class => CustomerPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
