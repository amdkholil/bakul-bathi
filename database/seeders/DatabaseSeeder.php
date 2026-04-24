<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\DebtLog;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create 20 Products
        $products = Product::factory(20)->create();

        // 2. Create 10 Customers
        $customers = Customer::factory(10)->create();

        // 3. Create sample sales
        Sale::factory(15)->create([
            'customer_id' => fn() => $customers->random()->id
        ])->each(function ($sale) use ($products) {
            // Add 1-5 random products as SaleItems
            $items = SaleItem::factory(fake()->numberBetween(1, 5))->make([
                'sale_id' => $sale->id,
            ])->each(function ($item) use ($products) {
                $product = $products->random();
                $item->product_id = $product->id;
                $item->price_at_sale = $product->selling_price;
                $item->cost_at_sale = $product->cost_price;
                $item->save();
            });

            // Calculate totals for the sale
            $totalPrice = $items->sum(fn($i) => $i->quantity * $i->price_at_sale);
            $totalCost = $items->sum(fn($i) => $i->quantity * $i->cost_at_sale);
            
            $sale->update([
                'total_price' => $totalPrice,
                'total_cost' => $totalCost,
                'profit' => $totalPrice - $totalCost,
            ]);

            // If status is 'Hutang', add some debt logs
            if ($sale->status === 'Hutang') {
                DebtLog::factory(fake()->numberBetween(1, 2))->create([
                    'sale_id' => $sale->id,
                    'amount_paid' => $totalPrice * 0.2, // Sample partial payment
                    'description' => 'Cicilan awal / Pembayaran sebagian'
                ]);
            }
        });
    }
}
