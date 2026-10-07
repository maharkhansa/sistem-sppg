<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->decimal('unit_price', 15, 2)
                ->default(0)
                ->after('unit');

            $table->decimal('subtotal', 18, 2)
                ->default(0)
                ->after('unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->dropColumn([
                'unit_price',
                'subtotal',
            ]);
        });
    }
};