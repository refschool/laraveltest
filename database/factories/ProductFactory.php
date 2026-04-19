<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name'        => fake()->words(3, true),
            'price'       => fake()->randomFloat(2, 1, 500),
            'stock'       => fake()->numberBetween(1, 100),
            'description' => fake()->sentence(),
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(['stock' => 0]);
    }

    public function expensive(): static
    {
        return $this->state(['price' => fake()->randomFloat(2, 500, 2000)]);
    }
}
