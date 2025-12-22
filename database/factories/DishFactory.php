<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Dish>
 */
class DishFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => \App\Models\Category::factory(),
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'cost_price' => $this->faker->randomFloat(2, 1, 10),
            'sale_price' => $this->faker->randomFloat(2, 5, 30),
            'is_available' => true,
            'image_url' => $this->faker->imageUrl(),
            'preparation_time' => $this->faker->numberBetween(5, 30),
        ];
    }
}
