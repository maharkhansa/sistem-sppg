<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Kitchen;
use App\Models\LPDH;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class LPDHController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $lpdhs = LPDH::with('kitchen')
            ->latest('service_date')
            ->latest('id')
            ->get();

        return view('lpdhs.index', compact('lpdhs'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $kitchens = Kitchen::where('status', true)
            ->orderBy('name')
            ->get();

        return view('lpdhs.create', compact('kitchens'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $data = $request->validate([
            'kitchen_id' => [
                'required',
                'exists:kitchens,id',
            ],

            'service_date' => [
                'required',
                'date',
                Rule::unique('lpdhs', 'service_date')
                    ->where(function ($query) use ($request) {
                        return $query->where(
                            'kitchen_id',
                            $request->kitchen_id
                        );
                    }),
            ],

            'day_status' => [
                'required',
                'string',
                'max:50',
            ],

            'beneficiaries_count' => [
                'required',
                'integer',
                'min:0',
            ],

            'raw_material_expenses' => [
                'required',
                'numeric',
                'min:0',
            ],

            'incentive_calculated' => [
                'required',
                'numeric',
                'min:0',
            ],

            'incentive_paid' => [
                'required',
                'numeric',
                'min:0',
            ],

            'va_final_balance' => [
                'required',
                'numeric',
                'min:0',
            ],

            'topup_proposal' => [
                'required',
                'numeric',
                'min:0',
            ],

            'inspection_result' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);

        // Insentif penerima manfaat = jumlah penerima x Rp2.000.
        $data['incentive_calculated'] =
            (int) $data['beneficiaries_count'] * 2000;

        $data['incentive_paid'] =
            (float) $data['incentive_paid'];

        $invoiceTotals = $this->getInvoiceTotals(
            $data['kitchen_id'],
            $data['service_date']
        );

        $data['raw_material_expenses'] =
            $invoiceTotals['raw_material_total'];

        $data['operational_expenses'] =
            $invoiceTotals['operational_total'];

        $data['beneficiary_rent_incentive'] =
            $data['incentive_calculated'];

        LPDH::create($data);

        return redirect()
            ->route('lpdhs.index')
            ->with('success', 'LPDH berhasil dibuat.');
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(LPDH $lpdh)
    {
        $lpdh->load('kitchen');

        return view('lpdhs.show', compact('lpdh'));
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(LPDH $lpdh)
    {
        $kitchens = Kitchen::where('status', true)
            ->orderBy('name')
            ->get();

        return view(
            'lpdhs.edit',
            compact('lpdh', 'kitchens')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, LPDH $lpdh)
    {
        $data = $request->validate([
            'kitchen_id' => [
                'required',
                'exists:kitchens,id',
            ],

            'service_date' => [
                'required',
                'date',
                Rule::unique('lpdhs', 'service_date')
                    ->ignore($lpdh->id)
                    ->where(function ($query) use ($request) {
                        return $query->where(
                            'kitchen_id',
                            $request->kitchen_id
                        );
                    }),
            ],

            'day_status' => [
                'required',
                'string',
                'max:50',
            ],

            'beneficiaries_count' => [
                'required',
                'integer',
                'min:0',
            ],

            'raw_material_expenses' => [
                'required',
                'numeric',
                'min:0',
            ],

            'incentive_calculated' => [
                'required',
                'numeric',
                'min:0',
            ],

            'incentive_paid' => [
                'required',
                'numeric',
                'min:0',
            ],

            'va_final_balance' => [
                'required',
                'numeric',
                'min:0',
            ],

            'topup_proposal' => [
                'required',
                'numeric',
                'min:0',
            ],

            'inspection_result' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);

        $data['incentive_calculated'] =
            (int) $data['beneficiaries_count'] * 2000;

        $data['incentive_paid'] =
            (float) $data['incentive_paid'];

        $invoiceTotals = $this->getInvoiceTotals(
            $data['kitchen_id'],
            $data['service_date']
        );

        $data['raw_material_expenses'] =
            $invoiceTotals['raw_material_total'];

        $data['operational_expenses'] =
            $invoiceTotals['operational_total'];

        $data['beneficiary_rent_incentive'] =
            $data['incentive_calculated'];

        $lpdh->update($data);

        return redirect()
            ->route('lpdhs.index')
            ->with('success', 'LPDH berhasil diperbarui.');
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(LPDH $lpdh)
    {
        $lpdh->delete();

        return redirect()
            ->route('lpdhs.index')
            ->with('success', 'LPDH berhasil dihapus.');
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX TOTAL INVOICE
    |--------------------------------------------------------------------------
    */

    public function invoiceTotal(Request $request)
    {
        $validated = $request->validate([
            'kitchen_id' => [
                'required',
                'exists:kitchens,id',
            ],

            'service_date' => [
                'required',
                'date',
            ],
        ]);

        $totals = $this->getInvoiceTotals(
            $validated['kitchen_id'],
            $validated['service_date']
        );

        return response()->json([
            'raw_material_total' => $totals['raw_material_total'],
            'operational_total' => $totals['operational_total'],
            'invoice_count' => $totals['invoice_count'],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | BANTUAN: NORMALISASI TEKS
    |--------------------------------------------------------------------------
    */

    private function normalizeText($value): string
    {
        return Str::of((string) ($value ?? ''))
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]/', '')
            ->toString();
    }

    /*
    |--------------------------------------------------------------------------
    | BANTUAN: AMBIL KATEGORI BIAYA
    |--------------------------------------------------------------------------
    |
    | Periksa atribut kategori yang tersedia pada model.
    | Sesuaikan daftar ini jika nama kolom kategori sebenarnya berbeda.
    |
    */

    private function getExpenseCategory($model): string
    {
        if (!$model) {
            return '';
        }

        $possibleFields = [
            'expense_type',
            'expense_category',
            'category_name',
            'category',
            'type',
            'name',
        ];

        foreach ($possibleFields as $field) {
            $value = $model->getAttribute($field);

            if (is_string($value) && trim($value) !== '') {
                return $this->normalizeText($value);
            }

            if (is_object($value)) {
                foreach (['name', 'nama', 'title'] as $nameField) {
                    $nestedValue = $value->{$nameField} ?? null;

                    if (
                        is_string($nestedValue)
                        && trim($nestedValue) !== ''
                    ) {
                        return $this->normalizeText($nestedValue);
                    }
                }
            }
        }

        return '';
    }

    /*
    |--------------------------------------------------------------------------
    | PERHITUNGAN TOTAL INVOICE
    |--------------------------------------------------------------------------
    */

    private function getInvoiceTotals(
        $kitchenId,
        $serviceDate
    ): array {
        $invoices = Invoice::query()
            ->where('kitchen_id', $kitchenId)
            ->whereDate('invoice_date', $serviceDate)
            ->with([
                'details.supplier',
                'expenses',
            ])
            ->get();

        $rawMaterialTotal = 0;
        $operationalTotal = 0;

        foreach ($invoices as $invoice) {
            /*
             * DETAIL INVOICE
             */

            foreach ($invoice->details as $detail) {
                $detailType = $this->normalizeText(
                    $detail->detail_type ?? ''
                );

                $supplierName = $this->normalizeText(
                    $detail->supplier?->name
                    ?: $detail->supplier_name
                    ?: ''
                );

                $amount = $detail->subtotal !== null
                    ? (float) $detail->subtotal
                    : (
                        (float) ($detail->quantity ?? 0)
                        * (float) ($detail->unit_price ?? 0)
                    );

                /*
                 * BAHAN BAKU
                 */

                $isRawMaterialSupplier =
                    str_contains($supplierName, 'sumberrejeki')
                    || str_contains($supplierName, 'sumberrezeki')
                    || str_contains($supplierName, 'gemilang')
                    || str_contains($supplierName, 'zenzi');

                if (
                    $detailType === 'stock'
                    && $isRawMaterialSupplier
                ) {
                    $rawMaterialTotal += $amount;
                    continue;
                }

                /*
                 * OPERASIONAL DARI TOP FAST
                 */

                $isTopFast = str_contains(
                    $supplierName,
                    'topfast'
                );

                if (
                    $detailType === 'stock'
                    && $isTopFast
                ) {
                    $operationalTotal += $amount;
                    continue;
                }

                /*
                 * OPERASIONAL DARI DETAIL EXPENSE
                 *
                 * Hanya masukkan expense jika kategori atau
                 * nama biayanya menunjukkan biaya operasional.
                 */

                if ($detailType === 'expense') {
                    // Ambil hanya biaya pada tanggal layanan LPDH.
                    $expenseDate = $detail->expense_date
                        ? \Illuminate\Support\Carbon::parse(
                            $detail->expense_date
                        )->toDateString()
                        : null;

                    if ($expenseDate !== $serviceDate) {
                        continue;
                    }

                    // expense_type_id = 4 merupakan Insentif Fasilitas SPPG.
                    // Perluas daftar ini jika jenis biaya operasional lain
                    // sudah ditambahkan ke master jenis biaya.
                    $isOperationalExpense =
                        (int) ($detail->expense_type_id ?? 0) === 4;

                    if ($isOperationalExpense) {
                        $operationalTotal += $amount;
                    }

                    continue;
                }
            }

            /*
             * RELASI EXPENSES
             *
             * Tetap menghitung biaya operasional dari relasi ini
             * berdasarkan kategori. Detail expense di atas dan
             * relasi ini harus dipastikan bukan dua representasi
             * dari biaya yang sama agar tidak terjadi duplikasi.
             */

            foreach ($invoice->expenses as $expense) {
                $category = $this->getExpenseCategory($expense);

                $description = $this->normalizeText(
                    ($expense->description ?? '')
                    . ' '
                    . ($expense->name ?? '')
                    . ' '
                    . ($expense->expense_name ?? '')
                );

                $isOperational =
                    str_contains($category, 'operasional')
                    || str_contains($category, 'operational')
                    || str_contains($category, 'fasilitassppg')
                    || str_contains($description, 'insentiffasilitassppg')
                    || str_contains($description, 'operasional')
                    || str_contains($description, 'operational');

                if ($isOperational) {
                    $operationalTotal += (float) (
                        $expense->amount ?? 0
                    );
                }
            }
        }

        return [
            'raw_material_total' => round($rawMaterialTotal, 2),
            'operational_total' => round($operationalTotal, 2),
            'invoice_count' => $invoices->count(),
        ];
    }
}