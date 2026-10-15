<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_order_details', 'supplier_name')) {
                $table->string('supplier_name')->nullable()->after('supplier_id');
            }
        });

        Schema::table('invoice_details', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_details', 'supplier_name')) {
                $table->string('supplier_name')->nullable()->after('supplier_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_order_details', 'supplier_name')) {
                $table->dropColumn('supplier_name');
            }
        });

        Schema::table('invoice_details', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_details', 'supplier_name')) {
                $table->dropColumn('supplier_name');
            }
        });
    }
};