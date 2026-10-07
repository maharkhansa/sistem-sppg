<?php

namespace Database\Seeders;

use App\Models\Kitchen;
use Illuminate\Database\Seeder;

class KitchenSeeder extends Seeder
{
    public function run(): void
    {
        Kitchen::updateOrCreate(
            ['id_sppg' => '5DRSMJ1U'],
            [
                'name' => 'SPPG Kota Magelang Magelang Utara Kedungsari 2',
                'status' => true,
            ]
        );
    }
}