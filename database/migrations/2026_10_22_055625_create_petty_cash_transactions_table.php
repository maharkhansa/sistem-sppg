<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('petty_cash_transactions', function (Blueprint $table) {
            $table->id();

            // PJ pemilik kas kecil
            $table->foreignId('purchase_responsible_id')
                ->constrained('purchase_responsibles')
                ->restrictOnDelete();

            // top_up = penambahan kas kecil
            // purchase_payment = pembayaran nota menggunakan kas kecil
            $table->string('type', 30);

            $table->date('transaction_date');
            $table->decimal('amount', 15, 2);

            // Rekening asal untuk top up dari kas/bank utama
            $table->foreignId('source_account_id')
                ->nullable()
                ->constrained('chart_of_accounts')
                ->restrictOnDelete();

            // Pembayaran nota yang menggunakan kas kecil
            $table->foreignId('stock_transaction_payment_id')
                ->nullable()
                ->unique()
                ->constrained('stock_transaction_payments')
                ->restrictOnDelete();

            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(
                ['purchase_responsible_id', 'transaction_date'],
                'petty_cash_responsible_date_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('petty_cash_transactions');
    }
};