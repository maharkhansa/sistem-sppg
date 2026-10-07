<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lpdhs', function (Blueprint $table) {
            $table->decimal('beneficiary_rent_incentive', 15, 2)
                ->default(0)
                ->after('operational_expenses');

            $table->decimal('car_rental', 15, 2)
                ->default(0)
                ->after('beneficiary_rent_incentive');

            $table->decimal('volunteer_salary', 15, 2)
                ->default(0)
                ->after('car_rental');
        });
    }

    public function down(): void
    {
        Schema::table('lpdhs', function (Blueprint $table) {
            $table->dropColumn([
                'beneficiary_rent_incentive',
                'car_rental',
                'volunteer_salary',
            ]);
        });
    }
};