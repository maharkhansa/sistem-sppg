<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->string('payment_type', 20)
                ->nullable()
                ->after('purchase_responsible_id');

            $table->string('payment_status', 20)
                ->nullable()
                ->after('payment_type');

            $table->date('paid_at')
                ->nullable()
                ->after('payment_status');

            $table->date('due_date')
                ->nullable()
                ->after('paid_at');

            $table->index(
                ['type', 'payment_status', 'due_date'],
                'stock_transactions_payment_due_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->dropIndex(
                'stock_transactions_payment_due_index'
            );

            $table->dropColumn([
                'payment_type',
                'payment_status',
                'paid_at',
                'due_date',
            ]);
        });
    }
};