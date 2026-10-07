<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nota_keluars', function (Blueprint $table) {

            $table->id();

            $table->string('nota_number')->unique();

            $table->date('nota_date');

            $table->foreignId('stock_transaction_id')
                ->constrained('stock_transactions')
                ->restrictOnDelete();

            $table->foreignId('purchase_order_id')
                ->constrained('purchase_orders')
                ->restrictOnDelete();

            $table->foreignId('kitchen_id')
                ->constrained('kitchens')
                ->restrictOnDelete();

            $table->decimal('total_amount', 15, 2)
                ->default(0);

            $table->string('status')
                ->default('DRAFT');

            $table->text('notes')
                ->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nota_keluars');
    }
};