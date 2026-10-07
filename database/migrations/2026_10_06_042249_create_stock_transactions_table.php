<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();

            $table->string('transaction_number', 50)->unique();

            $table->date('transaction_date');

            $table->string('type', 10);

            $table->foreignId('supplier_id')
                ->nullable()
                ->constrained('suppliers')
                ->nullOnDelete();

            $table->foreignId('kitchen_id')
                ->nullable()
                ->constrained('kitchens')
                ->nullOnDelete();

            $table->foreignId('purchase_order_id')
                ->nullable()
                ->constrained('purchase_orders')
                ->nullOnDelete();

            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};