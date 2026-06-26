<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(['income', 'expense', 'payment', 'investment', 'note']);

        return [
            'user_id'     => User::factory(),
            'title'       => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'type'        => $type,
            'amount'      => $type === 'note' ? null : fake()->randomFloat(2, 5, 2000),
            'status'      => fake()->randomElement(['pending', 'cleared']),
            'event_date'  => fake()->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
            'deadline'    => null,
        ];
    }
}
