<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kitchens', function (Blueprint $table) {
            $table->dropColumn([
                'address',
                'person_in_charge',
                'phone',
            ]);

            $table->string('kabupaten_kota', 100)->after('name');
            $table->string('provinsi', 100)->after('kabupaten_kota');
        });
    }

    public function down(): void
    {
        Schema::table('kitchens', function (Blueprint $table) {
            $table->dropColumn([
                'kabupaten_kota',
                'provinsi',
            ]);

            $table->text('address')->nullable();
            $table->string('person_in_charge', 100)->nullable();
            $table->string('phone', 30)->nullable();
        });
    }
};