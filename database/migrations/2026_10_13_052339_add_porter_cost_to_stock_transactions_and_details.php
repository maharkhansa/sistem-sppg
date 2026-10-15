<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_transactions', 'porter_cost')) {
                $table->decimal('porter_cost', 15, 2)
                    ->default(0)
                    ->after('purchase_note_number');
            }
        });

        Schema::table('stock_transaction_details', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_transaction_details', 'allocated_porter_cost')) {
                $table->decimal('allocated_porter_cost', 15, 2)
                    ->default(0)
                    ->after('subtotal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_transaction_details', function (Blueprint $table) {
            if (Schema::hasColumn('stock_transaction_details', 'allocated_porter_cost')) {
                $table->dropColumn('allocated_porter_cost');
            }
        });

        Schema::table('stock_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('stock_transactions', 'porter_cost')) {
                $table->dropColumn('porter_cost');
            }
        });
    }
};