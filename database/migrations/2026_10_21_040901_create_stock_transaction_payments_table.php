<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transaction_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stock_transaction_id')
                ->constrained('stock_transactions')
                ->restrictOnDelete();

            $table->date('payment_date');
            $table->decimal('amount', 15, 2);

            $table->string('payment_method', 30)->nullable();
            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(
                ['stock_transaction_id', 'payment_date'],
                'stock_transaction_payments_date_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transaction_payments');
    }
};