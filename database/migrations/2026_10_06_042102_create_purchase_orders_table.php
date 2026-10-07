<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('kitchen_id')
                ->constrained('kitchens')
                ->restrictOnDelete();

            $table->string('po_number', 50)->unique();

            $table->date('po_date');

            $table->string('status', 30)
                ->default('RECEIVED');

            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamp('processed_at')->nullable();

            $table->foreignId('processed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};