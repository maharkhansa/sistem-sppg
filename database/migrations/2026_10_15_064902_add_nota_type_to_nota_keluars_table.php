<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nota_keluars', function (Blueprint $table) {
            $table->string('nota_type', 20)
                ->default('stock')
                ->after('supplier_id');
        });
    }

    public function down(): void
    {
        Schema::table('nota_keluars', function (Blueprint $table) {
            $table->dropColumn('nota_type');
        });
    }
};