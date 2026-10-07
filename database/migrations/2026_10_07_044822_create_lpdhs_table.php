<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpdhs', function (Blueprint $table) {
            $table->id();

            // SPPG / Dapur
            $table->foreignId('kitchen_id')
                ->constrained('kitchens')
                ->cascadeOnDelete();

            // Identitas pelayanan
            $table->date('service_date');
            $table->string('day_status', 50)->nullable();

            // Data penggunaan dana
            $table->unsignedInteger('beneficiaries_count')->default(0);

            $table->decimal('raw_material_expenses', 15, 2)->default(0);
            $table->decimal('operational_expenses', 15, 2)->default(0);

            $table->decimal('incentive_calculated', 15, 2)->default(0);
            $table->decimal('incentive_paid', 15, 2)->default(0);

            // Saldo dan top up
            $table->decimal('va_final_balance', 15, 2)->default(0);
            $table->decimal('topup_proposal', 15, 2)->default(0);

            // Hasil pemeriksaan
            $table->string('inspection_result', 100)->nullable();

            // Nama file laporan
            $table->string('file_name', 255)->nullable();

            $table->timestamps();

            // Satu SPPG hanya memiliki satu LPDH untuk satu tanggal pelayanan
            $table->unique(
                ['kitchen_id', 'service_date'],
                'lpdhs_kitchen_date_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpdhs');
    }
};