<?php

namespace App\Imports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ItemImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Item([
            'code'           => $row['kode'],
            'name'           => $row['nama_barang'],
            'category_id'    => $row['category_id'], // atau sesuaikan dengan relasi kategori
            'supplier_id'    => $row['supplier_id'], // atau sesuaikan dengan relasi supplier
            'unit'           => $row['satuan'],
            'minimum_stock'  => $row['min_stok'],
            'status'         => $row['status'] ?? 1,
        ]);
    }
}
