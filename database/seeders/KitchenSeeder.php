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
                'kabupaten_kota' => 'Kota Magelang',
                'provinsi' => 'Jawa Tengah',

                // Ganti dengan alamat jalan, RT/RW, dan kelurahan yang lengkap.
                'alamat' => 'Kedungsari 2, Kecamatan Magelang Utara, Kota Magelang, Jawa Tengah',

                'status' => true,
            ]
        );
    }
}