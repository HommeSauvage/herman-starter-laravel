<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * REFERENCE MODULE — delete with Posts when unused.
 *
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->sentence(4),
            'excerpt' => fake()->sentence(16),
            'body' => '## '.fake()->sentence(3)."\n\n".fake()->paragraph()."\n\n".fake()->paragraph(),
            'image_path' => null,
            'published_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * A draft post that is hidden from the public.
     */
    public function draft(): static
    {
        return $this->state(fn () => [
            'published_at' => null,
        ]);
    }

    /**
     * A post scheduled for the future, hidden from the public until then.
     */
    public function scheduled(): static
    {
        return $this->state(fn () => [
            'published_at' => now()->addWeek(),
        ]);
    }
}
