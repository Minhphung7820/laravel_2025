<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('attribute_first_id')->nullable()->constrained('attributes')->onDelete('cascade');
            $table->foreignId('attribute_second_id')->nullable()->constrained('attributes')->onDelete('cascade');
            $table->integer('quantity');
            $table->unsignedBigInteger('sell_price');
            $table->unsignedBigInteger('purchase_price');
            $table->enum('product_type', ['single', 'combo', 'variable']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_products');
    }
};
