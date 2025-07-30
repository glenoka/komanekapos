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
        Schema::create('printers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "Kitchen Printer", "Receipt Printer Main"
            $table->string('code')->unique(); // "KITCHEN-01", "RECEIPT-01"
            $table->text('description')->nullable(); // deskripsi printer
            
            // Connection Settings
            $table->enum('connection_type', ['network', 'usb', 'bluetooth', 'serial'])->default('network');
            $table->string('ip_address')->nullable(); // untuk network printer
            $table->string('port')->default('9100'); // printer port (9100 untuk raw, 80 untuk http)
            $table->string('mac_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('printers');
    }
};
