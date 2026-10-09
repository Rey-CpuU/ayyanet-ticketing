<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_number' => 'TKT-'.fake()->unique()->numerify('######'),
            'customer_id' => Customer::factory(),
            'created_by' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'category' => fake()->randomElement(Ticket::CATEGORIES),
            'priority' => 'Medium',
            'status' => 'Open',
            'sla_deadline' => now()->addHours(8),
            'sla_status' => Ticket::SLA_ACTIVE,
        ];
    }

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }

    public function priority(string $priority): static
    {
        return $this->state(fn () => ['priority' => $priority]);
    }

    public function assignedTo(User $user): static
    {
        return $this->state(fn () => ['assigned_to' => $user->id]);
    }

    /**
     * An open ticket whose SLA deadline has already passed but is not yet flagged.
     */
    public function overdue(): static
    {
        return $this->state(fn () => [
            'sla_deadline' => now()->subHour(),
            'sla_status' => Ticket::SLA_ACTIVE,
        ]);
    }
}
