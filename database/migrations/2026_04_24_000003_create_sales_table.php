<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->decimal('total_price', 12, 2);
            $table->decimal('total_cost', 12, 2);
            $table->decimal('profit', 12, 2);
            $table->enum('status', ['Lunas', 'Hutang']);
            $table->enum('payment_method', ['Tunai', 'Transfer', 'QRIS']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
