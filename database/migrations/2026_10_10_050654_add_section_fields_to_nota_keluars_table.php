<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nota_keluars', function (Blueprint $table) {
            if (!Schema::hasColumn('nota_keluars', 'section_name')) {
                $table->string('section_name')->nullable();
            }

            if (!Schema::hasColumn('nota_keluars', 'section_order')) {
                $table->unsignedInteger('section_order')->default(1);
            }
        });
    }

    public function down(): void
    {
        Schema::table('nota_keluars', function (Blueprint $table) {
            if (Schema::hasColumn('nota_keluars', 'section_order')) {
                $table->dropColumn('section_order');
            }

            if (Schema::hasColumn('nota_keluars', 'section_name')) {
                $table->dropColumn('section_name');
            }
        });
    }
};
