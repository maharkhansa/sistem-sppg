<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Tidak mengembalikan supplier_id menjadi NOT NULL,
        // karena baris biaya non-stok boleh tidak memiliki supplier.
    }
};