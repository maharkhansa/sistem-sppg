<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->string('detail_type', 20)->default('stock')->after('item_id');
            $table->foreignId('expense_type_id')
                ->nullable()
                ->after('detail_type')
                ->constrained('expense_types')
                ->restrictOnDelete();
            $table->date('expense_date')->nullable()->after('expense_type_id');

            $table->unsignedBigInteger('item_id')->nullable()->change();
        });

        Schema::table('invoice_details', function (Blueprint $table) {
            $table->string('detail_type', 20)->default('stock')->after('item_id');
            $table->foreignId('expense_type_id')
                ->nullable()
                ->after('detail_type')
                ->constrained('expense_types')
                ->restrictOnDelete();
            $table->date('expense_date')->nullable()->after('expense_type_id');

            $table->unsignedBigInteger('item_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('invoice_details', function (Blueprint $table) {
            $table->dropForeign(['expense_type_id']);
            $table->dropColumn([
                'detail_type',
                'expense_type_id',
                'expense_date',
            ]);
        });

        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->dropForeign(['expense_type_id']);
            $table->dropColumn([
                'detail_type',
                'expense_type_id',
                'expense_date',
            ]);
        });
    }
};