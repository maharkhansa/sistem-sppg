<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Item;
use App\Models\Supplier;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ItemImport implements ToCollection
{
    public int $created = 0;

    public int $updated = 0;

    /**
     * Import data dari Excel.
     *
     * Format Excel:
     *
     * NAMA BARANG | HARGA | SUPPLIER
     *
     * Aturan kode:
     *
     * Gemilang       = GM-0xxxxxxxx
     * Sumber Rezeki  = SRN-xxxxxxxxx
     * TopFast        = TF-xxxxxxxxx
     * Zenzie         = ZP-xxxxxxxxx
     */
    public function collection(Collection $rows): void
    {
        /*
        |--------------------------------------------------------------------------
        | Cek Excel kosong
        |--------------------------------------------------------------------------
        */

        if ($rows->isEmpty()) {
            throw new \Exception(
                'File Excel kosong atau tidak memiliki data.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Cari baris HEADER secara otomatis
        |--------------------------------------------------------------------------
        */

        $headerRowIndex = null;
        $headers = [];

        foreach ($rows as $rowIndex => $row) {

            $temporaryHeaders = [];

            foreach ($row as $columnIndex => $value) {

                $temporaryHeaders[$columnIndex] =
                    $this->normalizeHeader($value);
            }

            $hasName = false;
            $hasPrice = false;
            $hasSupplier = false;

            foreach ($temporaryHeaders as $header) {

                if (
                    in_array(
                        $header,
                        [
                            'namabarang',
                            'nama',
                            'barang',
                        ],
                        true
                    )
                ) {
                    $hasName = true;
                }

                if (
                    in_array(
                        $header,
                        [
                            'harga',
                            'hargabarang',
                            'hargaunit',
                            'hargasatuan',
                        ],
                        true
                    )
                ) {
                    $hasPrice = true;
                }

                if (
                    in_array(
                        $header,
                        [
                            'supplier',
                            'namasupplier',
                            'suppliers',
                        ],
                        true
                    )
                ) {
                    $hasSupplier = true;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Header ditemukan
            |--------------------------------------------------------------------------
            */

            if (
                $hasName &&
                $hasPrice &&
                $hasSupplier
            ) {
                $headerRowIndex = $rowIndex;
                $headers = $temporaryHeaders;

                break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Header tidak ditemukan
        |--------------------------------------------------------------------------
        */

        if ($headerRowIndex === null) {
            throw new \Exception(
                'Header Excel tidak ditemukan. Pastikan file memiliki kolom NAMA BARANG, HARGA, dan SUPPLIER.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Tentukan posisi kolom
        |--------------------------------------------------------------------------
        */

        $nameColumn = $this->findHeaderColumn(
            $headers,
            [
                'namabarang',
                'nama',
                'barang',
            ]
        );

        $priceColumn = $this->findHeaderColumn(
            $headers,
            [
                'harga',
                'hargabarang',
                'hargaunit',
                'hargasatuan',
            ]
        );

        $supplierColumn = $this->findHeaderColumn(
            $headers,
            [
                'supplier',
                'namasupplier',
                'suppliers',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Validasi kolom
        |--------------------------------------------------------------------------
        */

        if ($nameColumn === null) {
            throw new \Exception(
                'Kolom "NAMA BARANG" tidak ditemukan pada file Excel.'
            );
        }

        if ($priceColumn === null) {
            throw new \Exception(
                'Kolom "HARGA" tidak ditemukan pada file Excel.'
            );
        }

        if ($supplierColumn === null) {
            throw new \Exception(
                'Kolom "SUPPLIER" tidak ditemukan pada file Excel.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Proses semua data
        |--------------------------------------------------------------------------
        */

        foreach ($rows as $rowIndex => $row) {

            /*
            |--------------------------------------------------------------------------
            | Lewati baris header
            |--------------------------------------------------------------------------
            */

            if ($rowIndex <= $headerRowIndex) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Ambil data
            |--------------------------------------------------------------------------
            */

            $name = $this->getRowValue(
                $row,
                $nameColumn
            );

            $priceRaw = $this->getRowValue(
                $row,
                $priceColumn
            );

            $supplierName = $this->getRowValue(
                $row,
                $supplierColumn
            );

            /*
            |--------------------------------------------------------------------------
            | Bersihkan nama barang
            |--------------------------------------------------------------------------
            */

            $name = $this->cleanText($name);

            /*
            |--------------------------------------------------------------------------
            | Lewati baris kosong
            |--------------------------------------------------------------------------
            */

            if ($name === '') {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Bersihkan supplier
            |--------------------------------------------------------------------------
            */

            $supplierName = $this->cleanText(
                $supplierName
            );

            /*
            |--------------------------------------------------------------------------
            | Normalisasi harga
            |--------------------------------------------------------------------------
            */

            $price = $this->normalizePrice(
                $priceRaw
            );

            /*
            |--------------------------------------------------------------------------
            | Cari supplier
            |--------------------------------------------------------------------------
            */

            $supplier = $this->findSupplier(
                $supplierName
            );

            /*
            |--------------------------------------------------------------------------
            | Tentukan prefix kode
            |--------------------------------------------------------------------------
            */

            $prefix = $this->getSupplierPrefix(
                $supplierName,
                $supplier?->name
            );

            /*
            |--------------------------------------------------------------------------
            | Cari kategori
            |--------------------------------------------------------------------------
            */

            $category = $this->findCategory(
                $name,
                $supplierName
            );

            /*
            |--------------------------------------------------------------------------
            | Cari barang berdasarkan nama
            |--------------------------------------------------------------------------
            */

            $item = $this->findExistingItem(
                $name
            );

            /*
            |--------------------------------------------------------------------------
            | BARANG SUDAH ADA
            |--------------------------------------------------------------------------
            */

            if ($item) {

                /*
                |--------------------------------------------------------------------------
                | Update harga
                |--------------------------------------------------------------------------
                */

                $item->price = $price;

                /*
                |--------------------------------------------------------------------------
                | Update supplier
                |--------------------------------------------------------------------------
                */

                if ($supplier) {
                    $item->supplier_id = $supplier->id;
                }

                /*
                |--------------------------------------------------------------------------
                | Update kategori jika belum ada
                |--------------------------------------------------------------------------
                */

                if (
                    !$item->category_id &&
                    $category
                ) {
                    $item->category_id = $category->id;
                }

                /*
                |--------------------------------------------------------------------------
                | Perbaiki kode berdasarkan supplier
                |--------------------------------------------------------------------------
                */

                if ($prefix !== '') {

                    $currentCode = strtoupper(
                        trim((string) $item->code)
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Cek apakah kode sudah sesuai
                    |--------------------------------------------------------------------------
                    */

                    if (!$this->isValidSupplierCode(
                        $currentCode,
                        $prefix
                    )) {

                        $item->code =
                            $this->generateItemCode(
                                $prefix
                            );
                    }
                }

                $item->save();

                $this->updated++;

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | BARANG BARU
            |--------------------------------------------------------------------------
            */

            Item::create([
                'category_id' => $category?->id,
                'supplier_id' => $supplier?->id,

                'code' => $this->generateItemCode(
                    $prefix
                ),

                'name' => $name,

                'unit' => 'pcs',

                'price' => $price,

                'minimum_stock' => 0,

                'status' => true,
            ]);

            $this->created++;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MEMBERSIHKAN TEXT
    |--------------------------------------------------------------------------
    */

    private function cleanText(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        $value = (string) $value;

        /*
        |--------------------------------------------------------------------------
        | Karakter spasi khusus
        |--------------------------------------------------------------------------
        */

        $value = str_replace(
            [
                "\xC2\xA0",
                "\xE2\x80\x8B",
                "\xE2\x80\x8C",
                "\xE2\x80\x8D",
                "\xEF\xBB\xBF",
            ],
            ' ',
            $value
        );

        /*
        |--------------------------------------------------------------------------
        | Hilangkan spasi berlebihan
        |--------------------------------------------------------------------------
        */

        $value = preg_replace(
            '/\s+/u',
            ' ',
            $value
        );

        return trim($value);
    }

    /*
    |--------------------------------------------------------------------------
    | NORMALISASI HEADER
    |--------------------------------------------------------------------------
    */

    private function normalizeHeader(
        mixed $value
    ): string {

        $value = $this->cleanText(
            $value
        );

        if ($value === '') {
            return '';
        }

        $value = strtolower(
            $value
        );

        /*
        |--------------------------------------------------------------------------
        | Hilangkan spasi, underscore, dash dan titik
        |--------------------------------------------------------------------------
        */

        $value = str_replace(
            [
                ' ',
                '_',
                '-',
                '.',
            ],
            '',
            $value
        );

        return $value;
    }

    /*
    |--------------------------------------------------------------------------
    | CARI KOLOM HEADER
    |--------------------------------------------------------------------------
    */

    private function findHeaderColumn(
        array $headers,
        array $possibleNames
    ): ?int {

        $normalizedNames = [];

        foreach ($possibleNames as $name) {

            $normalizedNames[] =
                $this->normalizeHeader(
                    $name
                );
        }

        foreach ($headers as $index => $header) {

            if (
                in_array(
                    $header,
                    $normalizedNames,
                    true
                )
            ) {
                return $index;
            }
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | AMBIL NILAI BARIS
    |--------------------------------------------------------------------------
    */

    private function getRowValue(
        mixed $row,
        int $columnIndex
    ): mixed {

        if ($row instanceof Collection) {
            return $row->get(
                $columnIndex
            );
        }

        if (is_array($row)) {
            return $row[$columnIndex] ?? null;
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | NORMALISASI HARGA
    |--------------------------------------------------------------------------
    */

    private function normalizePrice(
        mixed $value
    ): float {

        /*
        |--------------------------------------------------------------------------
        | Harga kosong
        |--------------------------------------------------------------------------
        */

        if (
            $value === null ||
            $value === ''
        ) {
            return 0;
        }

        /*
        |--------------------------------------------------------------------------
        | Jika Excel sudah berupa angka
        |--------------------------------------------------------------------------
        */

        if (
            is_int($value) ||
            is_float($value)
        ) {
            return (float) $value;
        }

        /*
        |--------------------------------------------------------------------------
        | Ubah ke string
        |--------------------------------------------------------------------------
        */

        $value = trim(
            (string) $value
        );

        if ($value === '') {
            return 0;
        }

        /*
        |--------------------------------------------------------------------------
        | Hapus Rp
        |--------------------------------------------------------------------------
        */

        $value = preg_replace(
            '/rp\s*/i',
            '',
            $value
        );

        /*
        |--------------------------------------------------------------------------
        | Hapus spasi
        |--------------------------------------------------------------------------
        */

        $value = str_replace(
            ' ',
            '',
            $value
        );

        /*
        |--------------------------------------------------------------------------
        | Format:
        |
        | 14.500,50
        |--------------------------------------------------------------------------
        */

        if (
            str_contains(
                $value,
                '.'
            ) &&
            str_contains(
                $value,
                ','
            )
        ) {

            $value = str_replace(
                '.',
                '',
                $value
            );

            $value = str_replace(
                ',',
                '.',
                $value
            );

            return is_numeric(
                $value
            )
                ? (float) $value
                : 0;
        }

        /*
        |--------------------------------------------------------------------------
        | Format:
        |
        | 14500,50
        |--------------------------------------------------------------------------
        */

        if (
            str_contains(
                $value,
                ','
            )
        ) {

            $value = str_replace(
                ',',
                '.',
                $value
            );

            return is_numeric(
                $value
            )
                ? (float) $value
                : 0;
        }

        /*
        |--------------------------------------------------------------------------
        | Format:
        |
        | 145.000
        |--------------------------------------------------------------------------
        */

        if (
            str_contains(
                $value,
                '.'
            )
        ) {

            $parts = explode(
                '.',
                $value
            );

            /*
            |--------------------------------------------------------------------------
            | 145.000 → 145000
            |--------------------------------------------------------------------------
            */

            if (
                count($parts) === 2 &&
                strlen($parts[1]) === 3
            ) {

                $value = str_replace(
                    '.',
                    '',
                    $value
                );

                return is_numeric(
                    $value
                )
                    ? (float) $value
                    : 0;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Bersihkan karakter selain angka
        |--------------------------------------------------------------------------
        */

        $value = preg_replace(
            '/[^0-9.]/',
            '',
            $value
        );

        return is_numeric(
            $value
        )
            ? (float) $value
            : 0;
    }

    /*
    |--------------------------------------------------------------------------
    | CARI SUPPLIER
    |--------------------------------------------------------------------------
    */

    private function findSupplier(
        ?string $supplierName
    ): ?Supplier {

        if (!$supplierName) {
            return null;
        }

        $name = strtolower(
            $this->cleanText(
                $supplierName
            )
        );

        /*
        |--------------------------------------------------------------------------
        | GEMILANG
        |--------------------------------------------------------------------------
        */

        if (
            str_contains(
                $name,
                'gemilang'
            )
        ) {

            return Supplier::query()
                ->whereRaw(
                    'LOWER(name) LIKE ?',
                    ['%gemilang%']
                )
                ->first();
        }

        /*
        |--------------------------------------------------------------------------
        | SUMBER REZEKI / REJEKI
        |--------------------------------------------------------------------------
        */

        if (
            str_contains(
                $name,
                'sumber'
            ) &&
            (
                str_contains(
                    $name,
                    'rezeki'
                ) ||
                str_contains(
                    $name,
                    'rejeki'
                )
            )
        ) {

            return Supplier::query()
                ->whereRaw(
                    'LOWER(name) LIKE ?',
                    ['%sumber%']
                )
                ->where(
                    function ($query) {

                        $query
                            ->whereRaw(
                                'LOWER(name) LIKE ?',
                                ['%rezeki%']
                            )
                            ->orWhereRaw(
                                'LOWER(name) LIKE ?',
                                ['%rejeki%']
                            );
                    }
                )
                ->first();
        }

        /*
        |--------------------------------------------------------------------------
        | TOPFAST
        |--------------------------------------------------------------------------
        */

        if (
            str_contains(
                $name,
                'topfast'
            ) ||
            str_contains(
                $name,
                'top fast'
            )
        ) {

            return Supplier::query()
                ->where(
                    function ($query) {

                        $query
                            ->whereRaw(
                                'LOWER(name) LIKE ?',
                                ['%topfast%']
                            )
                            ->orWhereRaw(
                                'LOWER(name) LIKE ?',
                                ['%top fast%']
                            );
                    }
                )
                ->first();
        }

        /*
        |--------------------------------------------------------------------------
        | ZENZIE
        |--------------------------------------------------------------------------
        */

        if (
            str_contains(
                $name,
                'zenzie'
            )
        ) {

            return Supplier::query()
                ->whereRaw(
                    'LOWER(name) LIKE ?',
                    ['%zenzie%']
                )
                ->first();
        }

        /*
        |--------------------------------------------------------------------------
        | Pencarian supplier umum
        |--------------------------------------------------------------------------
        */

        return Supplier::query()
            ->whereRaw(
                'LOWER(TRIM(name)) = ?',
                [$name]
            )
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | PREFIX SUPPLIER
    |--------------------------------------------------------------------------
    */

    private function getSupplierPrefix(
        ?string $supplierName,
        ?string $databaseSupplierName = null
    ): string {

        $name = strtolower(
            $this->cleanText(
                $supplierName
                ?: $databaseSupplierName
            )
        );

        /*
        |--------------------------------------------------------------------------
        | GEMILANG
        |
        | Buah
        |
        | GM-0xxxxxxxx
        |--------------------------------------------------------------------------
        */

        if (
            str_contains(
                $name,
                'gemilang'
            )
        ) {
            return 'GM';
        }

        /*
        |--------------------------------------------------------------------------
        | SUMBER REZEKI
        |
        | Bahan baku
        |
        | SRN-xxxxxxxxx
        |--------------------------------------------------------------------------
        */

        if (
            str_contains(
                $name,
                'sumber'
            ) &&
            (
                str_contains(
                    $name,
                    'rezeki'
                ) ||
                str_contains(
                    $name,
                    'rejeki'
                )
            )
        ) {
            return 'SRN';
        }

        /*
        |--------------------------------------------------------------------------
        | TOPFAST
        |
        | Operasional / alat-alat
        |
        | TF-xxxxxxxxx
        |--------------------------------------------------------------------------
        */

        if (
            str_contains(
                $name,
                'topfast'
            ) ||
            str_contains(
                $name,
                'top fast'
            )
        ) {
            return 'TF';
        }

        /*
        |--------------------------------------------------------------------------
        | ZENZIE
        |
        | Bumbu & sayur
        |
        | ZP-xxxxxxxxx
        |--------------------------------------------------------------------------
        */

        if (
            str_contains(
                $name,
                'zenzie'
            )
        ) {
            return 'ZP';
        }

        /*
        |--------------------------------------------------------------------------
        | Supplier tidak dikenal
        |--------------------------------------------------------------------------
        */

        return 'ITM';
    }

    /*
    |--------------------------------------------------------------------------
    | CARI KATEGORI
    |--------------------------------------------------------------------------
    |
    | Prioritas:
    |
    | 1. Cari kategori berdasarkan nama supplier.
    | 2. Jika tidak ditemukan, gunakan Bahan Kering.
    | 3. Jika masih tidak ada, gunakan kategori aktif pertama.
    |
    */

    private function findCategory(
        string $itemName,
        string $supplierName
    ): ?Category {

        $supplier = strtolower(
            $this->cleanText(
                $supplierName
            )
        );

        /*
        |--------------------------------------------------------------------------
        | GEMILANG → BUAH
        |--------------------------------------------------------------------------
        */

        if (
            str_contains(
                $supplier,
                'gemilang'
            )
        ) {

            $category = Category::query()
                ->whereRaw(
                    'LOWER(name) LIKE ?',
                    ['%buah%']
                )
                ->first();

            if ($category) {
                return $category;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | SUMBER REZEKI → BAHAN BAKU
        |--------------------------------------------------------------------------
        */

        if (
            str_contains(
                $supplier,
                'sumber'
            ) &&
            (
                str_contains(
                    $supplier,
                    'rezeki'
                ) ||
                str_contains(
                    $supplier,
                    'rejeki'
                )
            )
        ) {

            $category = Category::query()
                ->whereRaw(
                    'LOWER(name) LIKE ?',
                    ['%bahan baku%']
                )
                ->first();

            if ($category) {
                return $category;
            }

            /*
            | Jika kategori Bahan Baku belum ada,
            | coba Bahan Kering.
            */

            $category = Category::query()
                ->whereRaw(
                    'LOWER(name) LIKE ?',
                    ['%bahan kering%']
                )
                ->first();

            if ($category) {
                return $category;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | TOPFAST → OPERASIONAL
        |--------------------------------------------------------------------------
        */

        if (
            str_contains(
                $supplier,
                'topfast'
            ) ||
            str_contains(
                $supplier,
                'top fast'
            )
        ) {

            $category = Category::query()
                ->where(function ($query) {

                    $query
                        ->whereRaw(
                            'LOWER(name) LIKE ?',
                            ['%operasional%']
                        )
                        ->orWhereRaw(
                            'LOWER(name) LIKE ?',
                            ['%alat%']
                        );
                })
                ->first();

            if ($category) {
                return $category;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | ZENZIE → BUMBU & SAYUR
        |--------------------------------------------------------------------------
        */

        if (
            str_contains(
                $supplier,
                'zenzie'
            )
        ) {

            $category = Category::query()
                ->where(function ($query) {

                    $query
                        ->whereRaw(
                            'LOWER(name) LIKE ?',
                            ['%bumbu%']
                        )
                        ->orWhereRaw(
                            'LOWER(name) LIKE ?',
                            ['%sayur%']
                        );
                })
                ->first();

            if ($category) {
                return $category;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | DEFAULT → BAHAN KERING
        |--------------------------------------------------------------------------
        */

        $category = Category::query()
            ->whereRaw(
                'LOWER(name) LIKE ?',
                ['%bahan kering%']
            )
            ->first();

        if ($category) {
            return $category;
        }

        /*
        |--------------------------------------------------------------------------
        | DEFAULT TERAKHIR
        |--------------------------------------------------------------------------
        */

        return Category::query()
            ->where('status', true)
            ->orderBy('id')
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | CARI BARANG YANG SUDAH ADA
    |--------------------------------------------------------------------------
    */

    private function findExistingItem(
        string $name
    ): ?Item {

        $normalizedName = strtolower(
            $this->cleanText(
                $name
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Pencarian normal
        |--------------------------------------------------------------------------
        */

        $item = Item::query()
            ->whereRaw(
                'LOWER(TRIM(name)) = ?',
                [$normalizedName]
            )
            ->first();

        if ($item) {
            return $item;
        }

        /*
        |--------------------------------------------------------------------------
        | Pencarian manual untuk spasi ganda
        |--------------------------------------------------------------------------
        */

        $items = Item::query()->get();

        foreach ($items as $existingItem) {

            $existingName = strtolower(
                $this->cleanText(
                    $existingItem->name
                )
            );

            if (
                $existingName ===
                $normalizedName
            ) {
                return $existingItem;
            }
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | CEK KODE SESUAI SUPPLIER
    |--------------------------------------------------------------------------
    */

    private function isValidSupplierCode(
        string $code,
        string $prefix
    ): bool {

        $prefix = strtoupper(
            $prefix
        );

        $code = strtoupper(
            trim($code)
        );

        /*
        |--------------------------------------------------------------------------
        | Gemilang
        |
        | Harus:
        |
        | GM-0xxxxxxxx
        |--------------------------------------------------------------------------
        */

        if ($prefix === 'GM') {

            return (bool) preg_match(
                '/^GM-0[0-9]{8}$/',
                $code
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Supplier lain:
        |
        | SRN-xxxxxxxxx
        | TF-xxxxxxxxx
        | ZP-xxxxxxxxx
        |--------------------------------------------------------------------------
        */

        return (bool) preg_match(
            '/^' .
            preg_quote(
                $prefix,
                '/'
            ) .
            '-[0-9]{9}$/',
            $code
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE KODE BARANG
    |--------------------------------------------------------------------------
    */

    private function generateItemCode(
        string $prefix
    ): string {

        do {

            /*
            |--------------------------------------------------------------------------
            | GEMILANG
            |
            | GM-0xxxxxxxx
            |
            | 0 + 8 angka
            |--------------------------------------------------------------------------
            */

            if ($prefix === 'GM') {

                $number = '0' .
                    str_pad(
                        (string) random_int(
                            1,
                            99999999
                        ),
                        8,
                        '0',
                        STR_PAD_LEFT
                    );

            } else {

                /*
                |--------------------------------------------------------------------------
                | Supplier lainnya
                |
                | SRN-xxxxxxxxx
                | TF-xxxxxxxxx
                | ZP-xxxxxxxxx
                |--------------------------------------------------------------------------
                */

                $number = str_pad(
                    (string) random_int(
                        1,
                        999999999
                    ),
                    9,
                    '0',
                    STR_PAD_LEFT
                );
            }

            $code =
                $prefix .
                '-' .
                $number;

        } while (
            Item::query()
                ->where(
                    'code',
                    $code
                )
                ->exists()
        );

        return $code;
    }
}
