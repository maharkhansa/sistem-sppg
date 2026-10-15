<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Kolom vendor dan nomor nota pembelian.
         * Nullable agar transaksi lama tetap kompatibel.
         */
        Schema::table('stock_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_transactions', 'vendor_name')) {
                $table->string('vendor_name', 150)->nullable();
            }

            if (!Schema::hasColumn('stock_transactions', 'purchase_note_number')) {
                $table->string('purchase_note_number', 100)->nullable();
            }
        });

        /*
         * Lengkapi detail transaksi hanya jika kolom belum tersedia.
         * Ini menghindari penambahan kolom yang sudah ada.
         */
        Schema::table('stock_transaction_details', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_transaction_details', 'supplier_id')) {
                $table->foreignId('supplier_id')
                    ->nullable()
                    ->constrained('suppliers')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('stock_transaction_details', 'discount')) {
                $table->decimal('discount', 15, 2)->default(0);
            }

            if (!Schema::hasColumn('stock_transaction_details', 'section_name')) {
                $table->string('section_name', 100)->nullable();
            }

            if (!Schema::hasColumn('stock_transaction_details', 'section_order')) {
                $table->unsignedInteger('section_order')->default(0);
            }
        });
    }

    public function down(): void
    {
        /*
         * Kolom sengaja tidak dihapus pada rollback.
         * Ini untuk melindungi data transaksi lama dan data yang
         * mungkin sudah dipakai fitur lain.
         */
    }
};