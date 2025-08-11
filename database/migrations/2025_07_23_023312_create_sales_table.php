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
            $table->string('invoice_number')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->datetime('sale_date');
            $table->string('table_no')->nullable();
            $table->enum('sales_type', [
                'regular',
                'complimentary',     // Free/complimentary
                'owner_guest',       // Tamu owner
                'staff_meal',        // Makan staff
                'vip_guest',         // Tamu VIP
                'business_entertainment', // Entertaining tamu bisnis
                'banquet/wedding',       // Event/wedding catering
            ])->default('regular')->nullable();
            $table->enum('order_type', ['dine_in', 'room_service', 'takeaway','other'])->default('dine_in');
            // Financial info
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            
            // Payment info
            $table->enum('payment_method', ['cash', 'card', 'qris','room_charge','complimentary'])->default('room_charge')->nullable();

            $table->integer('total_items');
         
            $table->json('payment_details')->nullable(); // untuk multiple payment methods
            $table->enum('activity', ['breakfast', 'lunch', 'dinner', 'afternoontea']);
            // Status
            $table->enum('status', ['completed', 'pending', 'cancelled', 'refunded'])->default('pending');
                
            // Staff info
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Additional info
            $table->text('notes')->nullable();
            $table->string('slug')->unique();
            $table->timestamps();
            
            // Indexes
            $table->index('invoice_number');
            $table->index(['sale_date']);
            $table->index(['customer_id']);
            $table->index(['status']);
            $table->index('user_id');
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
