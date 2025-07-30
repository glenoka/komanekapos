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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->text('header_receipt')->nullable();
            $table->text('footer_receipt')->nullable();
            $table->boolean('print_logo_on_receipt')->default(true);
            $table->enum('receipt_paper_size', ['58mm', '80mm', '112mm'])->default('80mm');
            $table->integer('receipt_copies')->default(1);
            $table->boolean('auto_print_receipt')->default(true);
            $table->string('currency_code', 3)->default('IDR');
            $table->string('currency_symbol')->default('Rp');
            $table->string('tax_name')->default('PPN');
            $table->decimal('default_tax_rate', 5, 2)->default(11.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
