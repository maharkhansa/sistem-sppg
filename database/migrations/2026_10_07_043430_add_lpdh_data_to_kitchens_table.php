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
            // Data Mitra / Yayasan
            $table->string('foundation_name', 150)->nullable();

            // Pengawas Keuangan
            $table->string('finance_officer_name', 150)->nullable();
            $table->string('finance_officer_nik', 50)->nullable();

            // Kepala SPPG
            $table->string('head_sppg_name', 150)->nullable();
            $table->string('head_sppg_nip', 50)->nullable();

            // Perwakilan Mitra / Yayasan
            $table->string('foundation_rep_name', 150)->nullable();
            $table->string('foundation_rep_nik', 50)->nullable();

            // File logo dan tanda tangan
            $table->string('foundation_logo_path')->nullable();
            $table->string('finance_officer_signature_path')->nullable();
            $table->string('head_sppg_signature_path')->nullable();
            $table->string('head_sppg_stamp_path')->nullable();
            $table->string('foundation_rep_signature_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kitchens', function (Blueprint $table) {
            $table->dropColumn([
                'foundation_name',
                'finance_officer_name',
                'finance_officer_nik',
                'head_sppg_name',
                'head_sppg_nip',
                'foundation_rep_name',
                'foundation_rep_nik',
                'foundation_logo_path',
                'finance_officer_signature_path',
                'head_sppg_signature_path',
                'head_sppg_stamp_path',
                'foundation_rep_signature_path',
            ]);
        });
    }
};