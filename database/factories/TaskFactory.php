<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4), 'description' => fake()->paragraph(),
            'due_date' => today()->addDays(2), 'priority' => 'medium', 'status' => 'pending',
        ];
    }
}
