<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => 'C-'.fake()->unique()->numerify('######'),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('08##-####-####'),
            'address' => fake()->address(),
            'package' => fake()->randomElement(['Home 20 Mbps', 'Home 30 Mbps', 'Home 50 Mbps', 'Business 100 Mbps']),
        ];
    }
}
