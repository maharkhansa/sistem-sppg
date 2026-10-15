<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('stock_transaction_details', 'discount')) {
            Schema::table('stock_transaction_details', function (Blueprint $table) {
                $table->decimal('discount', 15, 2)->default(0);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('stock_transaction_details', 'discount')) {
            Schema::table('stock_transaction_details', function (Blueprint $table) {
                $table->dropColumn('discount');
            });
        }
    }
};
