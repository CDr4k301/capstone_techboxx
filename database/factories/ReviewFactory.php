<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'product_id' => fake()->randomElement(['1', '2', '3', '4', '5']),
            // 'user_id' => '', //null
            'name' => 'Anonymous',
            'rating' => fake()->numberBetween(1, 5),
            'title' => fake()->sentence(3),
            'content' => fake()->paragraph(2),
            'product_type' => fake()->randomElement(['pc_cases','coolers','cpus', 'motherboards', 'psus', 'rams', 'storages']),
        ];
    }
}
