<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        Supplier::updateOrCreate(
            ['code' => 'SUP001'],
            [
                'name' => 'KOPERASI SUMBER REJEKI',
                'status' => true,
            ]
        );

        Supplier::updateOrCreate(
            ['code' => 'SUP002'],
            [
                'name' => 'ZENZIE PRODUCTION',
                'status' => true,
            ]
        );

        Supplier::updateOrCreate(
            ['code' => 'SUP003'],
            [
                'name' => 'GEMILANG MART',
                'status' => true,
            ]
        );

        Supplier::updateOrCreate(
            ['code' => 'SUP004'],
            [
                'name' => 'TOP FAST MART',
                'status' => true,
            ]
        );
    }
}