<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lpdhs', function (Blueprint $table) {
            $table->unsignedInteger('volunteer_count')
                ->default(0)
                ->after('car_rental');

            $table->decimal('volunteer_salary_per_person', 15, 2)
                ->default(0)
                ->after('volunteer_count');
        });
    }

    public function down(): void
    {
        Schema::table('lpdhs', function (Blueprint $table) {
            $table->dropColumn([
                'volunteer_count',
                'volunteer_salary_per_person',
            ]);
        });
    }
};