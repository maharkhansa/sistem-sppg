<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_responsible_vendor', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vendor_id')
                ->constrained('vendors')
                ->cascadeOnDelete();

            $table->foreignId('purchase_responsible_id')
                ->constrained('purchase_responsibles')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique([
                'vendor_id',
                'purchase_responsible_id',
            ], 'purchase_responsible_vendor_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_responsible_vendor');
    }
};