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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('type', ['beverage', 'food'])->default('food');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->string('path_images')->nullable();
            $table->enum('status', ['active', 'inactive', 'draft'])->default('active');
            $table->timestamps();

            // Indexes untuk performa
            $table->index('category_id');
            $table->index('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
