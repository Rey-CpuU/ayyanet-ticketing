<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketMessage>
 */
class TicketMessageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'user_id' => User::factory(),
            'message' => fake()->sentence(),
            'is_internal' => false,
            'type' => 'external',
        ];
    }

    public function internal(): static
    {
        return $this->state(fn () => ['is_internal' => true, 'type' => 'internal']);
    }
}
