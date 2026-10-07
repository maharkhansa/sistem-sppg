<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::updateOrCreate(
            ['name' => 'Bahan Kering'],
            ['status' => true]
        );

        Category::updateOrCreate(
            ['name' => 'Bumbu & Sayur'],
            ['status' => true]
        );

        Category::updateOrCreate(
            ['name' => 'Buah'],
            ['status' => true]
        );

        Category::updateOrCreate(
            ['name' => 'Operasional'],
            ['status' => true]
        );
    }
}