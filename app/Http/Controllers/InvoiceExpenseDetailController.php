<?php

namespace App\Http\Controllers;

use App\Models\ExpenseType;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceExpenseDetailController extends Controller
{
    /**
     * Menampilkan form edit biaya non-stok dalam Invoice.
     * Tidak mengubah transaksi stok maupun Nota Keluar.
     */
    public function edit(Invoice $invoice)
    {
        $invoice->load([
            'kitchen',
            'stockTransaction',
            'purchaseOrder',
        ]);

        $details = $invoice->details()
            ->where('detail_type', 'expense')
            ->with('expenseType')
            ->orderBy('expense_date')
            ->orderBy('expense_type_id')
            ->orderBy('id')
            ->get();

        $expenseTypes = ExpenseType::orderBy('name')->get();

        return view('invoices.expenses-edit', compact(
            'invoice',
            'details',
            'expenseTypes'
        ));
    }

    /**
     * Menyimpan perubahan biaya non-stok.
     */
    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'details' => [
                'nullable',
                'array',
            ],

            'details.*.id' => [
                'nullable',
                'integer',
            ],

            'details.*.supplier_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'details.*.expense_type_id' => [
                'required',
                'integer',
                'exists:expense_types,id',
            ],

            'details.*.expense_date' => [
                'required',
                'date',
            ],

            'details.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'details.*.unit' => [
                'required',
                'string',
                'max:50',
            ],

            'details.*.unit_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'details.*.notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ], [
            'details.*.supplier_name.string' =>
                'Nama supplier harus berupa teks.',

            'details.*.supplier_name.max' =>
                'Nama supplier maksimal 255 karakter.',

            'details.*.expense_type_id.required' =>
                'Jenis biaya wajib dipilih.',

            'details.*.expense_type_id.exists' =>
                'Jenis biaya yang dipilih tidak valid.',

            'details.*.expense_date.required' =>
                'Tanggal biaya wajib diisi.',

            'details.*.expense_date.date' =>
                'Tanggal biaya tidak valid.',

            'details.*.quantity.required' =>
                'Qty biaya wajib diisi.',

            'details.*.quantity.gt' =>
                'Qty biaya harus lebih dari nol.',

            'details.*.unit.required' =>
                'Satuan biaya wajib diisi.',

            'details.*.unit_price.required' =>
                'Harga biaya wajib diisi.',

            'details.*.unit_price.min' =>
                'Harga biaya tidak boleh negatif.',
        ]);

        $rows = $validated['details'] ?? [];

        DB::transaction(function () use ($invoice, $rows) {
            /*
             * Ambil hanya detail biaya non-stok milik Invoice ini.
             * Detail barang stok tidak akan disentuh.
             */
            $existingExpenses = $invoice->details()
                ->where('detail_type', 'expense')
                ->get()
                ->keyBy('id');

            $submittedIds = [];

            foreach ($rows as $row) {
                $id = $row['id'] ?? null;

                if ($id !== null && $id !== '') {
                    $detail = $existingExpenses->get((int) $id);

                    if (!$detail) {
                        throw ValidationException::withMessages([
                            'details' =>
                                'Detail biaya tidak valid atau bukan milik Invoice ini.',
                        ]);
                    }

                    $submittedIds[] = (int) $id;
                } else {
                    $detail = new InvoiceDetail();

                    $detail->invoice_id = $invoice->id;
                    $detail->detail_type = 'expense';
                    $detail->supplier_id = null;
                    $detail->item_id = null;
                    $detail->section_name = null;
                    $detail->section_order = null;
                }

                $quantity = (float) $row['quantity'];
                $unitPrice = (float) $row['unit_price'];

                /*
                 * Simpan nama supplier yang dimasukkan melalui form.
                 * Nilai kosong disimpan sebagai null.
                 */
                $supplierName = trim(
                    (string) ($row['supplier_name'] ?? '')
                );

                $detail->supplier_name = $supplierName !== ''
                    ? $supplierName
                    : null;

                $detail->expense_type_id = $row['expense_type_id'];
                $detail->expense_date = $row['expense_date'];
                $detail->quantity = $quantity;
                $detail->unit = trim($row['unit']);
                $detail->unit_price = $unitPrice;

                $detail->subtotal = round(
                    $quantity * $unitPrice,
                    2
                );

                $detail->notes = $row['notes'] ?? null;

                $detail->save();
            }

            /*
             * Hapus hanya biaya non-stok yang sengaja dihapus
             * dari form. Detail stok tetap aman.
             */
            $idsToDelete = $existingExpenses->keys()
                ->map(fn ($id) => (int) $id)
                ->reject(fn ($id) => in_array(
                    $id,
                    $submittedIds,
                    true
                ))
                ->values()
                ->all();

            if (!empty($idsToDelete)) {
                $invoice->details()
                    ->where('detail_type', 'expense')
                    ->whereIn('id', $idsToDelete)
                    ->delete();
            }

            /*
             * Hitung kembali total dari seluruh detail Invoice,
             * termasuk barang stok dan biaya non-stok.
             */
            $invoiceTotal = $invoice->details()->sum('subtotal');

            $invoice->total_amount = $invoiceTotal;
            $invoice->save();

            /*
             * Sengaja tidak menjalankan:
             * - perubahan transaksi stok
             * - sinkronisasi stok
             * - pengubahan detail Nota Keluar
             */
        });

        return redirect()
            ->route('invoices.show', $invoice->id)
            ->with(
                'success',
                'Biaya non-stok dan nama supplier berhasil diperbarui. Total Invoice sudah dihitung ulang.'
            );
    }
}