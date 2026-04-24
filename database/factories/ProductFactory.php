<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $costPrice = fake()->randomFloat(2, 1000, 50000);
        $sellingPrice = $costPrice * fake()->randomFloat(2, 1.1, 1.5);

        return [
            'name' => fake()->words(3, true),
            'barcode' => fake()->ean13(),
            'category' => fake()->randomElement(['Makanan', 'Minuman', 'Kebutuhan Rumah', 'Elektronik', 'Pakaian']),
            'cost_price' => $costPrice,
            'selling_price' => $sellingPrice,
            'stock' => fake()->numberBetween(0, 100),
            'min_stock' => 5,
        ];
    }
}
