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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            // Define additional columns based on your CSV file structure
            $table->string('order_id')->unique(); // Example column for order ID
            $table->string('customer_name'); // Example column for customer name
            $table->decimal('order_total', 8, 2); // Example column for order total

            // ... Add other columns as needed

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
