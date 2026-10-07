<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_expenses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->cascadeOnDelete();

            $table->foreignId('expense_type_id')
                ->constrained('expense_types')
                ->restrictOnDelete();

            $table->decimal('amount', 15, 2)
                ->default(0);

            $table->text('description')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_expenses');
    }
};