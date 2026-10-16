<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountSeeder extends Seeder
{
    public function run(): void
    {
        /*
         * Akun induk.
         * firstOrCreate menjaga akun yang sudah ada agar tidak ditimpa.
         */
        $parents = [
            [
                'code' => '1000',
                'name' => 'ASET',
                'type' => 'asset',
            ],
            [
                'code' => '1100',
                'name' => 'Kas dan Bank',
                'type' => 'asset',
                'parent_code' => '1000',
            ],
            [
                'code' => '1200',
                'name' => 'Aset Lancar Lainnya',
                'type' => 'asset',
                'parent_code' => '1000',
            ],
            [
                'code' => '1300',
                'name' => 'Persediaan',
                'type' => 'asset',
                'parent_code' => '1000',
            ],
            [
                'code' => '2000',
                'name' => 'LIABILITAS / KEWAJIBAN',
                'type' => 'liability',
            ],
            [
                'code' => '2100',
                'name' => 'Utang Usaha dan Kewajiban',
                'type' => 'liability',
                'parent_code' => '2000',
            ],
            [
                'code' => '3000',
                'name' => 'EKUITAS / MODAL',
                'type' => 'equity',
            ],
            [
                'code' => '4000',
                'name' => 'PENDAPATAN',
                'type' => 'revenue',
            ],
            [
                'code' => '5000',
                'name' => 'HARGA POKOK PENJUALAN',
                'type' => 'expense',
            ],
            [
                'code' => '6000',
                'name' => 'BEBAN OPERASIONAL',
                'type' => 'expense',
            ],
        ];

        foreach ($parents as $data) {
            $parentCode = $data['parent_code'] ?? null;

            unset($data['parent_code']);

            $data['parent_id'] = $parentCode
                ? ChartOfAccount::where('code', $parentCode)->value('id')
                : null;

            ChartOfAccount::firstOrCreate(
                ['code' => $data['code']],
                array_merge($data, [
                    'is_cash_bank' => false,
                    'is_active' => true,
                ])
            );
        }

        /*
         * Akun yang digunakan untuk pencatatan transaksi.
         */
        $accounts = [
            [
                'code' => '1101',
                'name' => 'Kas Operasional',
                'type' => 'asset',
                'parent_code' => '1100',
                'is_cash_bank' => true,
            ],
            [
                'code' => '1102',
                'name' => 'Bank Operasional',
                'type' => 'asset',
                'parent_code' => '1100',
                'is_cash_bank' => true,
            ],
            [
                'code' => '1201',
                'name' => 'Piutang Usaha',
                'type' => 'asset',
                'parent_code' => '1200',
            ],
            [
                'code' => '1202',
                'name' => 'Piutang Karyawan atau Mitra',
                'type' => 'asset',
                'parent_code' => '1200',
            ],
            [
                'code' => '1301',
                'name' => 'Persediaan Bahan Baku',
                'type' => 'asset',
                'parent_code' => '1300',
            ],
            [
                'code' => '1302',
                'name' => 'Persediaan Operasional',
                'type' => 'asset',
                'parent_code' => '1300',
            ],
            [
                'code' => '2101',
                'name' => 'Utang Supplier',
                'type' => 'liability',
                'parent_code' => '2100',
            ],
            [
                'code' => '2102',
                'name' => 'Utang Reimburse Pembelian',
                'type' => 'liability',
                'parent_code' => '2100',
            ],
            [
                'code' => '2103',
                'name' => 'Utang Pajak',
                'type' => 'liability',
                'parent_code' => '2100',
            ],
            [
                'code' => '3101',
                'name' => 'Modal Mitra',
                'type' => 'equity',
                'parent_code' => '3000',
            ],
            [
                'code' => '3102',
                'name' => 'Saldo Laba',
                'type' => 'equity',
                'parent_code' => '3000',
            ],
            [
                'code' => '4101',
                'name' => 'Pendapatan Usaha',
                'type' => 'revenue',
                'parent_code' => '4000',
            ],
            [
                'code' => '4102',
                'name' => 'Pendapatan Lain-lain',
                'type' => 'revenue',
                'parent_code' => '4000',
            ],
            [
                'code' => '5101',
                'name' => 'Harga Pokok Bahan Baku',
                'type' => 'expense',
                'parent_code' => '5000',
            ],
            [
                'code' => '6101',
                'name' => 'Beban Operasional',
                'type' => 'expense',
                'parent_code' => '6000',
            ],
            [
                'code' => '6102',
                'name' => 'Beban Gaji Relawan',
                'type' => 'expense',
                'parent_code' => '6000',
            ],
            [
                'code' => '6103',
                'name' => 'Beban Insentif',
                'type' => 'expense',
                'parent_code' => '6000',
            ],
            [
                'code' => '6104',
                'name' => 'Beban Sewa Kendaraan',
                'type' => 'expense',
                'parent_code' => '6000',
            ],
            [
                'code' => '6105',
                'name' => 'Beban Pajak',
                'type' => 'expense',
                'parent_code' => '6000',
            ],
            [
                'code' => '6106',
                'name' => 'Beban Administrasi Bank',
                'type' => 'expense',
                'parent_code' => '6000',
            ],
        ];

        foreach ($accounts as $data) {
            $parentCode = $data['parent_code'];

            unset($data['parent_code']);

            $data['parent_id'] = ChartOfAccount::where(
                'code',
                $parentCode
            )->value('id');

            $data['is_active'] = true;

            ChartOfAccount::firstOrCreate(
                ['code' => $data['code']],
                $data
            );
        }
    }
}