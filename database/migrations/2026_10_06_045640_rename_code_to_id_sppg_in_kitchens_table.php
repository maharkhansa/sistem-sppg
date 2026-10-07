<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kitchens', function (Blueprint $table) {
            $table->renameColumn('code', 'id_sppg');
        });
    }

    public function down(): void
    {
        Schema::table('kitchens', function (Blueprint $table) {
            $table->renameColumn('id_sppg', 'code');
        });
    }
};