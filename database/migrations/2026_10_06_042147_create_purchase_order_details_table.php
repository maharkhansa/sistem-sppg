<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_order_id')
                ->constrained('purchase_orders')
                ->cascadeOnDelete();

            $table->foreignId('item_id')
                ->constrained('items')
                ->restrictOnDelete();

            $table->decimal('quantity', 15, 2);

            $table->string('unit', 30);

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_details');
    }
};