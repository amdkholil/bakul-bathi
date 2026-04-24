<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SaleItem>
 */
class SaleItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Product::inRandomOrder()->first() ?? Product::factory()->create();
        $quantity = fake()->numberBetween(1, 10);

        return [
            'sale_id' => Sale::factory(),
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price_at_sale' => $product->selling_price,
            'cost_at_sale' => $product->cost_price,
        ];
    }
}
