<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
final class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'role' => $this->faker->randomElement(['user', 'assistant']),
            'parts' => ['text' => $this->faker->sentence()],
            'attachments' => '[]',
        ];
    }

    public function user(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => 'user',
            'parts' => ['text' => $this->faker->sentence()],
        ]);
    }

    public function assistant(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => 'assistant',
            'parts' => ['text' => $this->faker->paragraph()],
        ]);
    }
}
