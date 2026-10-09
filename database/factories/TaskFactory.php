<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'title' => rtrim($title, '.'),
            'description' => fake()->boolean(60) ? fake()->paragraph() : null,
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'due_date' => fake()->boolean(50)
                ? fake()->dateTimeBetween('now', '+14 days')->format('Y-m-d')
                : null,
            'is_completed' => fake()->boolean(25),
            'completed_at' => null,
        ];
    }

    /**
     * Task yang sudah selesai.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_completed' => true,
            'completed_at' => now(),
        ]);
    }
}
