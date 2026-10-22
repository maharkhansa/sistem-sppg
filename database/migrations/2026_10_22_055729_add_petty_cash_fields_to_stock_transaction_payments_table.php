<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transaction_payments', function (Blueprint $table) {
            $table->string('source_type', 20)
                ->default('cash_bank')
                ->after('account_id');

            $table->foreignId('purchase_responsible_id')
                ->nullable()
                ->after('source_type')
                ->constrained('purchase_responsibles')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_transaction_payments', function (Blueprint $table) {
            $table->dropForeign(['purchase_responsible_id']);
            $table->dropColumn([
                'purchase_responsible_id',
                'source_type',
            ]);
        });
    }
};