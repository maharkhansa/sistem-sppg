<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->enum('type', [
                'asset',
                'liability',
                'equity',
                'revenue',
                'expense',
            ]);
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('chart_of_accounts')
                ->restrictOnDelete();

            $table->boolean('is_cash_bank')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['type', 'is_active']);
        });

        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_number', 50)->unique();
            $table->date('entry_date');
            $table->string('description');
            $table->string('source_type', 50)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->enum('status', [
                'draft',
                'posted',
                'void',
            ])->default('draft');

            $table->timestamp('posted_at')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['entry_date', 'status']);
            $table->index(['source_type', 'source_id']);
        });

        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('journal_entry_id')
                ->constrained('journal_entries')
                ->cascadeOnDelete();

            $table->foreignId('account_id')
                ->constrained('chart_of_accounts')
                ->restrictOnDelete();

            $table->text('description')->nullable();

            $table->decimal('debit', 18, 2)->default(0);
            $table->decimal('credit', 18, 2)->default(0);

            $table->timestamps();

            $table->index(['account_id', 'journal_entry_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('chart_of_accounts');
    }
};