<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->cascadeOnDelete();

            $table->foreignId('item_id')
                ->constrained('items')
                ->restrictOnDelete();

            $table->decimal('quantity', 15, 2);

            $table->string('unit');

            $table->decimal('unit_price', 15, 2);

            $table->decimal('subtotal', 15, 2);

            $table->text('notes')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_details');
    }
};