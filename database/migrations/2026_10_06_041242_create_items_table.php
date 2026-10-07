<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->constrained('categories')
                ->restrictOnDelete();

            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->restrictOnDelete();

            $table->string('code', 30)->unique();

            $table->string('name', 150);

            $table->string('unit', 30);

            $table->decimal('minimum_stock', 15, 2)
                ->default(0);

            $table->boolean('status')
                ->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};