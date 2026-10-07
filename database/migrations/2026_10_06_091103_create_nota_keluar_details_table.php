<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nota_keluar_details', function (Blueprint $table) {

            $table->id();

            $table->foreignId('nota_keluar_id')
                ->constrained('nota_keluars')
                ->cascadeOnDelete();

            $table->foreignId('supplier_id')
                ->nullable()
                ->constrained('suppliers')
                ->nullOnDelete();

            $table->foreignId('item_id')
                ->constrained('items')
                ->restrictOnDelete();

            $table->decimal('quantity', 15, 2);

            $table->string('unit');

            $table->decimal('unit_price', 15, 2)
                ->default(0);

            $table->decimal('subtotal', 15, 2)
                ->default(0);

            $table->text('notes')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nota_keluar_details');
    }
};