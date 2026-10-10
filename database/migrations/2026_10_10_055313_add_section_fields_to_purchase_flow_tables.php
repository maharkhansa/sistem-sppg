<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'purchase_order_details',
            'stock_transaction_details',
            'invoice_details',
        ];

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'section_name')) {
                    $table->string('section_name', 100)->nullable();
                }

                if (!Schema::hasColumn($tableName, 'section_order')) {
                    $table->unsignedInteger('section_order')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'purchase_order_details',
            'stock_transaction_details',
            'invoice_details',
        ];

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $columns = [];

                if (Schema::hasColumn($tableName, 'section_name')) {
                    $columns[] = 'section_name';
                }

                if (Schema::hasColumn($tableName, 'section_order')) {
                    $columns[] = 'section_order';
                }

                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};