<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambahkan supplier_id ke detail PO
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->foreignId('supplier_id')
                ->after('purchase_order_id')
                ->constrained('suppliers')
                ->restrictOnDelete();
        });

        // Hapus supplier_id dari header PO
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
        });
    }

    public function down(): void
    {
        // Kembalikan supplier_id ke header PO
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('supplier_id')
                ->after('kitchen_id')
                ->constrained('suppliers')
                ->restrictOnDelete();
        });

        // Hapus supplier_id dari detail PO
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
        });
    }
};