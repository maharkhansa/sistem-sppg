<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('supplier_id')
                ->after('kitchen_id')
                ->constrained('suppliers')
                ->restrictOnDelete();

            $table->timestamp('approved_at')
                ->nullable()
                ->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn([
                'supplier_id',
                'approved_at',
            ]);
        });
    }
};