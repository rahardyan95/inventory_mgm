<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sku' => fake()->unique()->regexify('[A-Z]{3}-[0-9]{3}'),
            'name' => fake()->word(),
            'category_id' => Category::factory(),
            'current_stock' => fake()->numberBetween(10, 100),
            'minimum_stock' => 10,
        ];
    }
}
