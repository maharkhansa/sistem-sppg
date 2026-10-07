<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transaction_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stock_transaction_id')
                ->constrained('stock_transactions')
                ->cascadeOnDelete();

            $table->foreignId('item_id')
                ->constrained('items')
                ->restrictOnDelete();

            $table->decimal('quantity', 15, 2);

            $table->string('unit', 30);

            $table->decimal('unit_price', 15, 2)
                ->default(0);

            $table->decimal('subtotal', 18, 2)
                ->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transaction_details');
    }
};