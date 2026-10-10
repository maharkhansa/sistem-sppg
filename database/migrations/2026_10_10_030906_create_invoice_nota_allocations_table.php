<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_nota_allocations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('nota_keluar_id')
                ->constrained('nota_keluars')
                ->cascadeOnDelete();

            $table->foreignId('invoice_detail_id')
                ->constrained('invoice_details')
                ->cascadeOnDelete();

            $table->decimal('quantity', 15, 2);

            $table->timestamps();

            $table->unique(
                ['nota_keluar_id', 'invoice_detail_id'],
                'invoice_nota_allocations_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_nota_allocations');
    }
};
