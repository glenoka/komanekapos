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
        Schema::create('sales_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('product_name'); // nama produk saat transaksi
            $table->decimal('quantity', 10, 2); // support decimal untuk produk yang dijual per porsi, dll
            $table->string('unit')->default('porsi'); // satuan (porsi, gelas, botol, dll)
            $table->decimal('unit_price', 15, 2); // harga per unit saat transaksi
            $table->decimal('original_price', 15, 2); // harga asli sebelum diskon
            $table->decimal('discount_amount', 15, 2)->default(0); // diskon per item
            $table->decimal('total_price', 15, 2); // total untuk item ini
            $table->boolean('is_complimentary')->default(false); // apakah item ini complimentary
            $table->timestamps();

            $table->index('sale_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_details');
    }
};
