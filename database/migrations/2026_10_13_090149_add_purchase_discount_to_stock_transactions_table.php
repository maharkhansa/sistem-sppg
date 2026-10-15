<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('stock_transactions', 'purchase_discount_type')) {
            Schema::table('stock_transactions', function (Blueprint $table) {
                $table->string('purchase_discount_type', 20)
                    ->default('amount');
            });
        }

        if (!Schema::hasColumn('stock_transactions', 'purchase_discount_value')) {
            Schema::table('stock_transactions', function (Blueprint $table) {
                $table->decimal('purchase_discount_value', 15, 2)
                    ->default(0);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('stock_transactions', 'purchase_discount_value')) {
            Schema::table('stock_transactions', function (Blueprint $table) {
                $table->dropColumn('purchase_discount_value');
            });
        }

        if (Schema::hasColumn('stock_transactions', 'purchase_discount_type')) {
            Schema::table('stock_transactions', function (Blueprint $table) {
                $table->dropColumn('purchase_discount_type');
            });
        }
    }
};