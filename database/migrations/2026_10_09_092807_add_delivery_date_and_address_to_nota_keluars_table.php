<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nota_keluars', function (Blueprint $table) {
            $table->date('delivery_date')->nullable();
            $table->text('delivery_address')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('nota_keluars', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_date',
                'delivery_address',
            ]);
        });
    }
};