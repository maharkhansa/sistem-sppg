<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Stock;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    /**
     * Menampilkan daftar stok barang.
     */
    public function index(Request $request)
    {
        $request->validate([
            'supplier_id' => [
                'nullable',
                'integer',
                'exists:suppliers,id',
            ],
            'search' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);

        $query = Item::with([
            'category',
            'stock',
            'supplier',
        ])->where('status', true);

        // FILTER PEMASOK INTERNAL
        if ($request->filled('supplier_id')) {
            $query->where(
                'supplier_id',
                $request->supplier_id
            );
        }

        // PENCARIAN NAMA ATAU KODE BARANG
        if ($request->filled('search')) {
            $search = trim($request->search);

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%');
                });
            }
        }

        // AMBIL DATA DAN PAGINATION
        $items = $query
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        // DATA PEMASOK INTERNAL
        $suppliers = Supplier::orderBy('name')->get();

        return view(
            'stocks.index',
            compact('items', 'suppliers')
        );
    }

    /**
     * Mengoreksi jumlah stok barang.
     *
     * Tidak mengubah riwayat transaksi Barang Masuk
     * maupun Barang Keluar.
     */
    public function updateQuantity(Request $request, Item $item)
    {
        $validated = $request->validate([
            'quantity' => [
                'required',
                'numeric',
                'min:0',
                'decimal:0,2',
            ],
        ], [
            'quantity.required' => 'Jumlah stok baru wajib diisi.',
            'quantity.numeric' => 'Jumlah stok harus berupa angka.',
            'quantity.min' => 'Jumlah stok tidak boleh negatif.',
            'quantity.decimal' => 'Jumlah stok maksimal dua angka desimal.',
        ]);

        DB::transaction(function () use ($item, $validated) {
            // Mengunci data barang selama koreksi berlangsung.
            Item::whereKey($item->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Perbarui saldo stok, atau buat jika belum tersedia.
            Stock::updateOrCreate(
                ['item_id' => $item->id],
                ['quantity' => $validated['quantity']]
            );
        });

        // Kembali ke halaman stok dengan filter dan halaman sebelumnya.
        $redirectParams = [];

        if ($request->filled('supplier_id')) {
            $redirectParams['supplier_id'] =
                $request->input('supplier_id');
        }

        if ($request->filled('page')) {
            $redirectParams['page'] =
                $request->input('page');
        }

        return redirect()
            ->route('stocks.index', $redirectParams)
            ->with(
                'success',
                'Stok barang "' . $item->name .
                '" berhasil dikoreksi.'
            );
    }
}