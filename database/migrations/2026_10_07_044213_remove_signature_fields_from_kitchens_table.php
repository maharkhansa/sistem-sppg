<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kitchens', function (Blueprint $table) {
            $table->dropColumn([
                'foundation_logo_path',
                'finance_officer_signature_path',
                'head_sppg_signature_path',
                'head_sppg_stamp_path',
                'foundation_rep_signature_path',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kitchens', function (Blueprint $table) {
            $table->string('foundation_logo_path')->nullable();
            $table->string('finance_officer_signature_path')->nullable();
            $table->string('head_sppg_signature_path')->nullable();
            $table->string('head_sppg_stamp_path')->nullable();
            $table->string('foundation_rep_signature_path')->nullable();
        });
    }
};