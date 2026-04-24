<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_number' => 'INV-' . strtoupper(fake()->unique()->bothify('??###??')),
            'customer_id' => Customer::factory(),
            'total_price' => 0, // Will be updated based on items
            'total_cost' => 0,  // Will be updated based on items
            'profit' => 0,      // Will be updated based on items
            'status' => fake()->randomElement(['Lunas', 'Hutang']),
            'payment_method' => fake()->randomElement(['Tunai', 'Transfer', 'QRIS']),
        ];
    }
}
