<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_details', function (Blueprint $table) {
            $table->foreign('supplier_id')
                ->references('id')
                ->on('suppliers')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoice_details', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
        });
    }
};