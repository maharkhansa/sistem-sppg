<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->foreignId('purchase_responsible_id')
                ->nullable()
                ->after('purchase_discount_value')
                ->constrained('purchase_responsibles')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->dropForeign(['purchase_responsible_id']);
            $table->dropColumn('purchase_responsible_id');
        });
    }
};