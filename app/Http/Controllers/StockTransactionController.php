<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Stock;
use App\Models\StockTransaction;
use App\Models\Supplier;
use App\Models\Vendor;
use App\Models\Kitchen;
use App\Models\Invoice;
use App\Models\NotaKeluar;
use App\Models\PurchaseResponsible;
use App\Models\StockTransactionPayment;
use App\Models\PettyCashTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class StockTransactionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DAFTAR BARANG MASUK
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = StockTransaction::with([
            'supplier',
            'purchaseResponsible',
            'details.item',
            'details.supplier',
        ])->where('type', 'IN');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', '%' . $search . '%')
                    ->orWhere('vendor_name', 'like', '%' . $search . '%')
                    ->orWhere('purchase_note_number', 'like', '%' . $search . '%');
            });
        }

        $periode = $request->input('periode');

        if ($periode === 'hari' && $request->filled('tanggal')) {
            $request->validate([
                'tanggal' => ['date_format:Y-m-d'],
            ]);

            $query->whereDate('transaction_date', $request->input('tanggal'));
        } elseif ($periode === 'bulan') {
            $request->validate([
                'bulan' => ['required', 'regex:/^(0?[1-9]|1[0-2])$/'],
                'tahun' => ['required', 'integer', 'between:2000,2100'],
            ]);

            $query->whereYear('transaction_date', (int) $request->input('tahun'))
                ->whereMonth('transaction_date', (int) $request->input('bulan'));
        } elseif ($periode === 'tahun') {
            $request->validate([
                'tahun' => ['required', 'integer', 'between:2000,2100'],
            ]);

            $query->whereYear('transaction_date', (int) $request->input('tahun'));
        }

        $transactions = $query
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $suppliers = Supplier::orderBy('name')->get();

        return view('stock_transactions.index', compact(
            'transactions',
            'suppliers'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | FORM TAMBAH BARANG MASUK
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $items = Item::orderBy('name')->get();
        $vendors = Vendor::orderBy('name')->get();

        $responsibles = PurchaseResponsible::where('is_active', true)
            ->orderBy('name')
            ->get();

        $accounts = \App\Models\ChartOfAccount::query()
            ->where('is_cash_bank', true)
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        // Saldo kas kecil dan top up terakhir setiap PJ
        $pettyCashBalances = [];

        foreach ($responsibles as $responsible) {
            $balance = PettyCashTransaction::query()
                ->where('purchase_responsible_id', $responsible->id)
                ->selectRaw("
                    COALESCE(
                        SUM(
                            CASE
                                WHEN type = 'top_up' THEN amount
                                WHEN type = 'purchase_payment' THEN -amount
                                ELSE 0
                            END
                        ), 0
                    ) as balance
                ")
                ->value('balance');

            $lastTopUp = PettyCashTransaction::query()
                ->where('purchase_responsible_id', $responsible->id)
                ->where('type', 'top_up')
                ->orderByDesc('transaction_date')
                ->orderByDesc('id')
                ->first();

            $pettyCashBalances[$responsible->id] = [
                'balance' => (float) $balance,
                'last_top_up_date' => $lastTopUp?->transaction_date,
                'last_top_up_amount' => $lastTopUp
                    ? (float) $lastTopUp->amount
                    : 0,
            ];
        }

        return view(
            'stock_transactions.create',
            compact(
                'vendors',
                'suppliers',
                'items',
                'responsibles',
                'accounts',
                'pettyCashBalances'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SIMPAN BARANG MASUK
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_note_number' => [
                'required',
                'string',
                'max:100',
            ],
            'vendor_name' => [
                'required',
                'string',
                'max:150',
            ],
            'transaction_date' => [
                'required',
                'date',
            ],

            'payment_type' => [
                'required',
                'in:cash,credit',
            ],

            'initial_payments' => [
                'nullable',
                'array',
                'max:50',
            ],

            'initial_payments.*.source_type' => [
                'nullable',
                Rule::in(['cash_bank', 'petty_cash']),
            ],

            'initial_payments.*.account_id' => [
                'nullable',
                'integer',
                Rule::exists('chart_of_accounts', 'id')->where(
                    fn ($query) => $query
                        ->where('is_cash_bank', true)
                        ->where('is_active', true)
                ),
            ],

            'initial_payments.*.purchase_responsible_id' => [
                'nullable',
                'integer',
                Rule::exists('purchase_responsibles', 'id')->where(
                    fn ($query) => $query->where('is_active', true)
                ),
            ],

            'initial_payments.*.payment_method' => [
                'nullable',
                'in:cash,transfer,other',
            ],

            'initial_payments.*.amount' => [
                'required',
                'numeric',
                'min:0',
                'max:9999999999999.99',
            ],

            'due_date' => [
                'nullable',
                'required_if:payment_type,credit',
                'date',
                'after_or_equal:transaction_date',
            ],
            'porter_cost' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'fuel_cost_details' => [
                'nullable',
                'array',
            ],

            'fuel_cost_details.*.vehicle_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'fuel_cost_details.*.amount' => [
                'required_with:fuel_cost_details',
                'numeric',
                'min:0',
                'max:9999999999999.99',
            ],
            'parking_cost' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999999.99',
            ],
            'cooking_cost' => [
                'nullable', 
                'numeric', 
                'min:0'
            ],
            'other_cost' => [
                'nullable', 
                'numeric', 
                'min:0'
            ],
            'purchase_discount_type' => [
                'nullable',
                'in:amount,percent',
            ],
            'purchase_discount_value' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999999.99',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'items' => [
                'required',
                'array',
                'min:1',
            ],
            'items.*.item_id' => [
                'required',
                'integer',
                'distinct',
                'exists:items,id',
            ],
            'items.*.quantity' => [
                'required',
                'numeric',
                'min:0',
            ],
            'items.*.unit_price' => [
                'required',
                'numeric',
                'min:0',
            ],
            'items.*.discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'items.*.unit' => [
                'nullable',
                'string',
                'max:50',
            ],
            'purchase_responsible_id' => [
                'required',
                'integer',
                Rule::exists('purchase_responsibles', 'id')
                    ->where('is_active', true),
            ],
        ], [
            'purchase_note_number.required' =>
                'Nomor nota pembelian wajib diisi.',
            'vendor_name.required' =>
                'Nama vendor wajib diisi.',
            'transaction_date.required' =>
                'Tanggal pembelian wajib diisi.',
            'porter_cost.numeric' =>
                'Biaya kuli harus berupa angka.',
            'porter_cost.min' =>
                'Biaya kuli tidak boleh negatif.',
            'fuel_cost.numeric' =>
                'Biaya BBM harus berupa angka.',
            'fuel_cost.min' =>
                'Biaya BBM tidak boleh negatif.',
            'parking_cost.numeric' =>
                'Biaya parkir harus berupa angka.',
            'parking_cost.min' =>
                'Biaya parkir tidak boleh negatif.',
            'purchase_discount_type.in' =>
                'Jenis diskon tambahan nota tidak valid.',
            'purchase_discount_value.numeric' =>
                'Nilai diskon tambahan nota harus berupa angka.',
            'purchase_discount_value.min' =>
                'Diskon tambahan nota tidak boleh negatif.',
            'items.required' =>
                'Tambahkan minimal satu barang.',
            'items.min' =>
                'Tambahkan minimal satu barang.',
            'items.*.item_id.distinct' =>
                'Barang yang sama tidak boleh ditambahkan dua kali.',
            'items.*.quantity.required' =>
                'Jumlah barang wajib diisi.',
            'items.*.quantity.min' =>
                'Jumlah barang tidak boleh negatif.',
            'items.*.unit_price.required' =>
                'Harga satuan wajib diisi.',
            'items.*.unit_price.min' =>
                'Harga satuan tidak boleh negatif.',
            'items.*.discount.min' =>
                'Diskon barang tidak boleh negatif.',
            'purchase_responsible_id.required' =>
                'Penanggung jawab pembelian wajib dipilih.',
            'purchase_responsible_id.exists' =>
                'Penanggung jawab tidak ditemukan atau sudah tidak aktif.',
            'payment_type.required' =>
                'Jenis pembayaran wajib dipilih.',
            'payment_type.in' =>
                'Jenis pembayaran tidak valid.',
            'initial_payment_amount.numeric' =>
                'Nominal pembayaran awal harus berupa angka.',
            'initial_payment_amount.min' =>
                'Nominal pembayaran awal tidak boleh negatif.',
            'due_date.required_if' =>
                'Tanggal jatuh tempo wajib diisi untuk pembelian kredit.',
            'due_date.after_or_equal' =>
                'Tanggal jatuh tempo tidak boleh sebelum tanggal pembelian.',
        ]);

        $itemsData = collect($validated['items'])
            ->filter(fn ($row) => (float) ($row['quantity'] ?? 0) > 0)
            ->values();

        if ($itemsData->isEmpty()) {
            throw ValidationException::withMessages([
                'items' =>
                    'Isi jumlah minimal satu barang yang benar-benar masuk.',
            ]);
        }

        $porterCost = round(
            (float) ($validated['porter_cost'] ?? 0),
            2
        );

        $fuelCostDetails = collect(
            $validated['fuel_cost_details'] ?? []
        )->map(function ($row) {
            return [
                'vehicle_name' => trim($row['vehicle_name'] ?? ''),
                'amount' => round((float) ($row['amount'] ?? 0), 2),
            ];
        })->filter(function ($row) {
            return $row['vehicle_name'] !== ''
                || $row['amount'] > 0;
        })->values()->all();

        $fuelCost = round(
            collect($fuelCostDetails)->sum('amount'),
            2
        );

        $parkingCost = round(
            (float) ($validated['parking_cost'] ?? 0),
            2
        );

        $cookingCost = round(
            (float) ($validated['cooking_cost'] ?? 0),
            2
        );

        $otherCost = round(
            (float) ($validated['other_cost'] ?? 0),
            2
        );

        $purchaseDiscountType =
            $validated['purchase_discount_type'] ?? 'amount';

        $purchaseDiscountValue = round(
            (float) ($validated['purchase_discount_value'] ?? 0),
            2
        );

        if (
            $purchaseDiscountType === 'percent'
            && $purchaseDiscountValue > 100
        ) {
            throw ValidationException::withMessages([
                'purchase_discount_value' =>
                    'Diskon persentase tidak boleh lebih dari 100%.',
            ]);
        }

        DB::transaction(function () use (
            $validated,
            $itemsData,
            $porterCost,
            $fuelCost,
            $fuelCostDetails,
            $parkingCost,
            $cookingCost,
            $otherCost,
            $purchaseDiscountType,
            $purchaseDiscountValue
        ) {
            $itemIds = $itemsData
                ->pluck('item_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->sort()
                ->values();

            $items = Item::whereIn('id', $itemIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $stockItemIds = $items
                ->filter(fn ($item) => $item->track_stock)
                ->keys();

            $stocks = Stock::whereIn('item_id', $stockItemIds)
                ->orderBy('item_id')
                ->lockForUpdate()
                ->get()
                ->keyBy('item_id');

            foreach ($stockItemIds as $itemId) {
                if (!$stocks->has($itemId)) {
                    $stock = Stock::create([
                        'item_id' => $itemId,
                        'quantity' => 0,
                    ]);

                    $stocks->put($itemId, $stock);
                }
            }

            do {
                $transactionNumber = 'IN-'
                    . now()->format('YmdHis')
                    . '-'
                    . Str::upper(Str::random(4));
            } while (
                StockTransaction::where(
                    'transaction_number',
                    $transactionNumber
                )->exists()
            );

            $transaction = StockTransaction::create([
                'transaction_number' => $transactionNumber,
                'type' => 'IN',
                'supplier_id' => null,
                'vendor_name' => trim($validated['vendor_name']),
                'purchase_note_number' => trim(
                    $validated['purchase_note_number']
                ),
                'porter_cost' => $porterCost,
                'fuel_cost' => $fuelCost,
                'fuel_cost_details' => $fuelCostDetails,
                'parking_cost' => $parkingCost,
                'cooking_cost' => $cookingCost,
                'other_cost' => $otherCost,
                'purchase_discount_type' => $purchaseDiscountType,
                'purchase_discount_value' => $purchaseDiscountValue,
                'purchase_responsible_id' =>
                    $validated['purchase_responsible_id'],
                'transaction_date' => $validated['transaction_date'],
                'payment_type' => $validated['payment_type'],
                'payment_status' => 'unpaid',
                'due_date' => $validated['due_date'] ?? null,
                'paid_at' => null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id() ?? 1,
            ]);

            foreach ($itemsData as $row) {
                $itemId = (int) $row['item_id'];
                $item = $items->get($itemId);

                if (!$item) {
                    throw ValidationException::withMessages([
                        'items' => 'Barang tidak ditemukan.',
                    ]);
                }

                $quantity = (float) $row['quantity'];
                $unitPrice = (float) $row['unit_price'];
                $discount = (float) ($row['discount'] ?? 0);

                $subtotalBeforeDiscount = $quantity * $unitPrice;

                if ($discount > $subtotalBeforeDiscount) {
                    throw ValidationException::withMessages([
                        'items' => 'Diskon barang "'
                            . $item->name
                            . '" tidak boleh melebihi total harga barang.',
                    ]);
                }

                $unit = !empty($row['unit'])
                    ? $row['unit']
                    : $item->unit;

                $subtotal = round(
                    max(0, $subtotalBeforeDiscount - $discount),
                    2
                );

                $transaction->details()->create([
                    'item_id' => $itemId,
                    'supplier_id' => $item->supplier_id,
                    'quantity' => $quantity,
                    'unit' => $unit,
                    'unit_price' => $unitPrice,
                    'discount' => $discount,
                    'subtotal' => $subtotal,
                    'allocated_porter_cost' => 0,
                ]);

                if ($item->track_stock) {
                    $stock = $stocks->get($itemId);

                    if (!$stock) {
                        $stock = Stock::create([
                            'item_id' => $itemId,
                            'quantity' => 0,
                        ]);

                        $stocks->put($itemId, $stock);
                    }

                    $stock->quantity =
                        (float) $stock->quantity + $quantity;

                    $stock->save();
                }
            }

            $goodsSubtotal = round(
                (float) $transaction->details()->sum('subtotal'),
                2
            );

            if (
                $purchaseDiscountType === 'amount'
                && $purchaseDiscountValue > $goodsSubtotal
            ) {
                throw ValidationException::withMessages([
                    'purchase_discount_value' =>
                        'Diskon tambahan nota tidak boleh melebihi subtotal barang.',
                ]);
            }

            $discountAmount = $this->calculatePurchaseDiscount(
                $goodsSubtotal,
                $purchaseDiscountType,
                $purchaseDiscountValue
            );

            $grandTotal = round(
                max(0, $goodsSubtotal - $discountAmount)
                    + $porterCost
                    + $fuelCost
                    + $parkingCost
                    + $cookingCost
                    + $otherCost,
                2
            );

            /*
            |--------------------------------------------------------------------------
            | PEMBAYARAN AWAL
            |--------------------------------------------------------------------------
            */

            $initialPayments = collect(
                $validated['initial_payments'] ?? []
            )
                ->filter(fn ($payment) =>
                    isset($payment['amount'])
                    && is_numeric($payment['amount'])
                    && (float) $payment['amount'] > 0
                )
                ->values();

            $initialPaymentAmount = 0;
            $pettyCashPaymentsByResponsible = [];

            /*
            |--------------------------------------------------------------------------
            | VALIDASI SEMUA SUMBER PEMBAYARAN
            |--------------------------------------------------------------------------
            */

            foreach ($initialPayments as $payment) {
                $sourceType = $payment['source_type'] ?? 'cash_bank';
                $amount = round((float) $payment['amount'], 2);

                if (!in_array($sourceType, ['cash_bank', 'petty_cash'], true)) {
                    throw ValidationException::withMessages([
                        'initial_payments' => 'Sumber pembayaran tidak valid.',
                    ]);
                }

                if ($sourceType === 'cash_bank') {
                    $accountId = $payment['account_id'] ?? null;
                    $paymentMethod = $payment['payment_method'] ?? null;

                    if (!$accountId || !$paymentMethod) {
                        throw ValidationException::withMessages([
                            'initial_payments' =>
                                'Pilih akun dan metode pembayaran untuk pembayaran kas/bank.',
                        ]);
                    }

                    $validAccount = \App\Models\ChartOfAccount::query()
                        ->whereKey($accountId)
                        ->where('is_cash_bank', true)
                        ->where('is_active', true)
                        ->exists();

                    if (!$validAccount) {
                        throw ValidationException::withMessages([
                            'initial_payments' =>
                                'Akun kas/bank tidak ditemukan atau sudah tidak aktif.',
                        ]);
                    }
                }

                if ($sourceType === 'petty_cash') {
                    $responsibleId = $payment['purchase_responsible_id'] ?? null;

                    if (!$responsibleId) {
                        throw ValidationException::withMessages([
                            'initial_payments' =>
                                'Pilih PJ untuk pembayaran menggunakan kas kecil.',
                        ]);
                    }

                    $responsible = PurchaseResponsible::query()
                        ->whereKey($responsibleId)
                        ->where('is_active', true)
                        ->lockForUpdate()
                        ->first();

                    if (!$responsible) {
                        throw ValidationException::withMessages([
                            'initial_payments' =>
                                'PJ kas kecil tidak ditemukan atau sudah tidak aktif.',
                        ]);
                    }

                    /*
                    * Saldo kas kecil = seluruh pemasukan - seluruh pengeluaran.
                    * Asumsi jenis transaksi kas kecil:
                    * top_up = pemasukan
                    * purchase_payment = pembayaran pembelian
                    */
                    $pettyCashBalance = round(
                        (float) PettyCashTransaction::query()
                            ->where('purchase_responsible_id', $responsibleId)
                            ->selectRaw("
                                COALESCE(
                                    SUM(
                                        CASE
                                            WHEN type = 'top_up' THEN amount
                                            WHEN type = 'purchase_payment' THEN -amount
                                            ELSE 0
                                        END
                                    ), 0
                                ) as balance
                            ")
                            ->value('balance'),
                        2
                    );

                    // Jumlahkan pembayaran dalam nota ini untuk PJ yang sama.
                    $pettyCashPaymentsByResponsible[$responsibleId] = round(
                        ($pettyCashPaymentsByResponsible[$responsibleId] ?? 0)
                            + $amount,
                        2
                    );

                    $totalPaymentForResponsible =
                        $pettyCashPaymentsByResponsible[$responsibleId];

                    if ($totalPaymentForResponsible > $pettyCashBalance + 0.01) {
                        throw ValidationException::withMessages([
                            'initial_payments' =>
                                'Saldo kas kecil PJ '
                                . $responsible->name
                                . ' tidak cukup. Saldo tersedia Rp'
                                . number_format($pettyCashBalance, 2, ',', '.')
                                . ', total pembayaran PJ dalam nota Rp'
                                . number_format($totalPaymentForResponsible, 2, ',', '.')
                                . '.',
                        ]);
                    }
                } // <-- TAMBAHKAN KURAWAL PENUTUP INI

                $initialPaymentAmount = round(
                    $initialPaymentAmount + $amount,
                    2
                );
            }

            /*
            |--------------------------------------------------------------------------
            | VALIDASI TOTAL PEMBAYARAN
            |--------------------------------------------------------------------------
            */

            if ($validated['payment_type'] === 'cash') {
                if (abs($initialPaymentAmount - $grandTotal) > 0.01) {
                    throw ValidationException::withMessages([
                        'initial_payments' =>
                            'Total pembayaran tunai harus sama dengan total nota. '
                            . 'Total nota: Rp'
                            . number_format($grandTotal, 2, ',', '.')
                            . ', total pembayaran: Rp'
                            . number_format($initialPaymentAmount, 2, ',', '.')
                            . '.',
                    ]);
                }
            }

            if ($initialPaymentAmount > $grandTotal + 0.01) {
                throw ValidationException::withMessages([
                    'initial_payments' =>
                        'Total pembayaran awal tidak boleh melebihi total nota.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | STATUS NOTA
            |--------------------------------------------------------------------------
            */

            if (abs($initialPaymentAmount - $grandTotal) <= 0.01) {
                $paymentStatus = 'paid';
            } elseif ($initialPaymentAmount > 0) {
                $paymentStatus = 'partial';
            } else {
                $paymentStatus = 'unpaid';
            }

            $transaction->payment_status = $paymentStatus;

            $transaction->paid_at = $paymentStatus === 'paid'
                ? $validated['transaction_date']
                : null;

            $transaction->save();

            /*
            |--------------------------------------------------------------------------
            | SIMPAN PEMBAYARAN DAN LEDGER KAS KECIL
            |--------------------------------------------------------------------------
            */

            foreach ($initialPayments as $payment) {
                $sourceType = $payment['source_type'] ?? 'cash_bank';
                $amount = round((float) $payment['amount'], 2);

                $paymentMethod = $sourceType === 'petty_cash'
                    ? 'cash'
                    : $payment['payment_method'];

                $paymentRecord = $transaction->payments()->create([
                    'account_id' => $sourceType === 'cash_bank'
                        ? $payment['account_id']
                        : null,

                    'source_type' => $sourceType,

                    'purchase_responsible_id' => $sourceType === 'petty_cash'
                        ? $payment['purchase_responsible_id']
                        : null,

                    'payment_date' => $validated['transaction_date'],
                    'amount' => $amount,
                    'payment_method' => $paymentMethod,
                    'created_by' => auth()->id() ?? 1,
                ]);

                if ($sourceType === 'petty_cash') {
                    PettyCashTransaction::create([
                        'purchase_responsible_id' =>
                            $payment['purchase_responsible_id'],

                        'type' => 'purchase_payment',
                        'transaction_date' => $validated['transaction_date'],
                        'amount' => $amount,
                        'source_account_id' => null,
                        'stock_transaction_payment_id' => $paymentRecord->id,
                        'reference_number' => $transaction->purchase_note_number,
                        'notes' => 'Pembayaran nota pembelian '
                            . $transaction->purchase_note_number
                            . ' - '
                            . $transaction->vendor_name,
                        'created_by' => auth()->id() ?? 1,
                    ]);
                }
            }
        });

        return redirect()
            ->route('stock-transactions.index')
            ->with(
                'success',
                'Barang masuk berhasil disimpan. Stok, diskon nota, dan alokasi biaya kuli telah diperbarui.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | DAFTAR BARANG KELUAR
    |--------------------------------------------------------------------------
    */

    public function outIndex(Request $request)
    {
        $query = StockTransaction::with([
            'supplier',
            'purchaseOrder',
            'kitchen',
            'details.item',
            'details.supplier',
            'invoice',
            'notaKeluars.supplier',
        ])->where('type', 'OUT');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', '%' . $search . '%')
                    ->orWhere('notes', 'like', '%' . $search . '%')
                    ->orWhereHas('purchaseOrder', function ($po) use ($search) {
                        $po->where('po_number', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('kitchen', function ($kitchen) use ($search) {
                        $kitchen->where(function ($subQuery) use ($search) {
                            $subQuery->where('name', 'like', '%' . $search . '%')
                                ->orWhere('id_sppg', 'like', '%' . $search . '%');
                        });
                    });
            });
        }

        if ($request->filled('kitchen_id')) {
            $query->where('kitchen_id', $request->input('kitchen_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate(
                'transaction_date',
                '>=',
                $request->input('date_from')
            );
        }

        if ($request->filled('date_to')) {
            $query->whereDate(
                'transaction_date',
                '<=',
                $request->input('date_to')
            );
        }

        $transactions = $query
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $suppliers = Supplier::orderBy('name')->get();
        $kitchens = Kitchen::orderBy('name')->get();

        return view('stock_transactions.out_index', compact(
            'transactions',
            'suppliers',
            'kitchens'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | DETAIL TRANSAKSI
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | SIMPAN PEMBAYARAN TAMBAHAN / PELUNASAN TEMPO
    |--------------------------------------------------------------------------
    */

    public function storePayment(Request $request, $stockTransaction)
    {
        $validated = $request->validate([
            'payment_date' => [
                'required',
                'date',
            ],
            'amount' => [
                'required',
                'numeric',
                'gt:0',
                'max:9999999999999.99',
            ],
            'source_type' => [
                'required',
                Rule::in(['cash_bank', 'petty_cash']),
            ],
            'payment_method' => [
                'nullable',
                Rule::in(['cash', 'transfer', 'other']),
            ],
            'account_id' => [
                'nullable',
                'integer',
                Rule::exists('chart_of_accounts', 'id')->where(
                    fn ($query) => $query
                        ->where('is_cash_bank', true)
                        ->where('is_active', true)
                ),
            ],
            'purchase_responsible_id' => [
                'nullable',
                'integer',
                Rule::exists('purchase_responsibles', 'id')->where(
                    fn ($query) => $query->where('is_active', true)
                ),
            ],
            'reference_number' => [
                'nullable',
                'string',
                'max:100',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        DB::transaction(function () use ($validated, $stockTransaction) {
            $transaction = StockTransaction::whereKey($stockTransaction)
                ->lockForUpdate()
                ->firstOrFail();

            if ($transaction->type !== 'IN') {
                throw ValidationException::withMessages([
                    'amount' =>
                        'Pembayaran hanya dapat ditambahkan pada transaksi barang masuk.',
                ]);
            }

            $sourceType = $validated['source_type'];
            $paymentAmount = round((float) $validated['amount'], 2);

            /*
            |--------------------------------------------------------------------------
            | HITUNG TOTAL NOTA
            |--------------------------------------------------------------------------
            */

            $goodsSubtotal = round(
                (float) $transaction->details()->sum('subtotal'),
                2
            );

            $discountAmount = $this->calculatePurchaseDiscount(
                $goodsSubtotal,
                $transaction->purchase_discount_type ?? 'amount',
                round(
                    (float) ($transaction->purchase_discount_value ?? 0),
                    2
                )
            );

            $grandTotal = round(
                max(0, $goodsSubtotal - $discountAmount)
                    + (float) ($transaction->porter_cost ?? 0)
                    + (float) ($transaction->fuel_cost ?? 0)
                    + (float) ($transaction->parking_cost ?? 0)
                    + (float) ($transaction->cooking_cost ?? 0)
                    + (float) ($transaction->other_cost ?? 0),
                2
            );

            /*
            |--------------------------------------------------------------------------
            | HITUNG PEMBAYARAN YANG SUDAH TERCATAT
            |--------------------------------------------------------------------------
            */

            $paidAmount = round(
                (float) $transaction->payments()->sum('amount'),
                2
            );

            $remainingAmount = round(
                max(0, $grandTotal - $paidAmount),
                2
            );

            if ($remainingAmount <= 0.01) {
                throw ValidationException::withMessages([
                    'amount' => 'Transaksi ini sudah lunas.',
                ]);
            }

            if ($paymentAmount > $remainingAmount + 0.01) {
                throw ValidationException::withMessages([
                    'amount' =>
                        'Pembayaran melebihi sisa tagihan sebesar Rp'
                        . number_format($remainingAmount, 2, ',', '.')
                        . '.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | VALIDASI SUMBER PEMBAYARAN
            |--------------------------------------------------------------------------
            */

            $accountId = null;
            $responsibleId = null;
            $paymentMethod = null;

            if ($sourceType === 'cash_bank') {
                $accountId = $validated['account_id'] ?? null;
                $paymentMethod = $validated['payment_method'] ?? null;

                if (!$accountId) {
                    throw ValidationException::withMessages([
                        'account_id' =>
                            'Pilih akun kas/bank untuk pembayaran ini.',
                    ]);
                }

                if (!$paymentMethod) {
                    throw ValidationException::withMessages([
                        'payment_method' =>
                            'Pilih metode pembayaran Tunai atau Transfer.',
                    ]);
                }

                $validAccount = \App\Models\ChartOfAccount::query()
                    ->whereKey($accountId)
                    ->where('is_cash_bank', true)
                    ->where('is_active', true)
                    ->exists();

                if (!$validAccount) {
                    throw ValidationException::withMessages([
                        'account_id' =>
                            'Akun kas/bank tidak ditemukan atau sudah tidak aktif.',
                    ]);
                }
            } else {
                /*
                |--------------------------------------------------------------------------
                | PEMBAYARAN DARI KAS KECIL PJ
                |--------------------------------------------------------------------------
                */

                $responsibleId =
                    $validated['purchase_responsible_id'] ?? null;

                if (!$responsibleId) {
                    throw ValidationException::withMessages([
                        'purchase_responsible_id' =>
                            'Pilih PJ untuk pembayaran kas kecil.',
                    ]);
                }

                $responsible = PurchaseResponsible::query()
                    ->whereKey($responsibleId)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();

                if (!$responsible) {
                    throw ValidationException::withMessages([
                        'purchase_responsible_id' =>
                            'PJ tidak ditemukan atau sudah tidak aktif.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | SALDO KAS KECIL
                |--------------------------------------------------------------------------
                | top_up = pemasukan
                | purchase_payment = pengeluaran
                */

                $balance = round(
                    (float) PettyCashTransaction::query()
                        ->where('purchase_responsible_id', $responsibleId)
                        ->selectRaw("
                            COALESCE(
                                SUM(
                                    CASE
                                        WHEN type = 'top_up' THEN amount
                                        WHEN type = 'purchase_payment' THEN -amount
                                        ELSE 0
                                    END
                                ), 0
                            ) as balance
                        ")
                        ->value('balance'),
                    2
                );

                if ($paymentAmount > $balance + 0.01) {
                    throw ValidationException::withMessages([
                        'amount' =>
                            'Saldo kas kecil PJ '
                            . $responsible->name
                            . ' tidak cukup. Saldo tersedia Rp'
                            . number_format($balance, 2, ',', '.')
                            . '.',
                    ]);
                }

                /*
                * Kas kecil selalu dibayarkan secara tunai.
                */
                $paymentMethod = 'cash';
            }

            /*
            |--------------------------------------------------------------------------
            | SIMPAN PEMBAYARAN
            |--------------------------------------------------------------------------
            */

            $paymentRecord = $transaction->payments()->create([
                'account_id' => $accountId,
                'source_type' => $sourceType,
                'purchase_responsible_id' => $responsibleId,
                'payment_date' => $validated['payment_date'],
                'amount' => $paymentAmount,
                'payment_method' => $paymentMethod,
                'reference_number' =>
                    $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id() ?? 1,
            ]);

            /*
            |--------------------------------------------------------------------------
            | CATAT PENGELUARAN KAS KECIL PJ
            |--------------------------------------------------------------------------
            */

            if ($sourceType === 'petty_cash') {
                PettyCashTransaction::create([
                    'purchase_responsible_id' => $responsibleId,
                    'type' => 'purchase_payment',
                    'transaction_date' => $validated['payment_date'],
                    'amount' => $paymentAmount,
                    'source_account_id' => null,
                    'stock_transaction_payment_id' => $paymentRecord->id,
                    'reference_number' =>
                        $validated['reference_number']
                        ?? $transaction->purchase_note_number,
                    'notes' =>
                        $validated['notes']
                        ?? 'Pembayaran nota pembelian '
                        . $transaction->purchase_note_number
                        . ' - '
                        . $transaction->vendor_name,
                    'created_by' => auth()->id() ?? 1,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | PERBARUI STATUS NOTA
            |--------------------------------------------------------------------------
            */

            $newPaidAmount = round(
                $paidAmount + $paymentAmount,
                2
            );

            if ($newPaidAmount >= $grandTotal - 0.01) {
                $transaction->payment_status = 'paid';

                /*
                * paid_at menggunakan tanggal pembayaran aktual terakhir.
                */
                $transaction->paid_at = $validated['payment_date'];
            } elseif ($newPaidAmount > 0) {
                $transaction->payment_status = 'partial';
                $transaction->paid_at = null;
            } else {
                $transaction->payment_status = 'unpaid';
                $transaction->paid_at = null;
            }

            $transaction->save();
        });

        return redirect()
            ->route('stock-transactions.show', $stockTransaction)
            ->with('success', 'Pembayaran berhasil dicatat.');
    }


    public function show($stockTransaction)
    {
        $stockTransaction = StockTransaction::with([
            'supplier',
            'kitchen',
            'purchaseOrder',
            'purchaseResponsible',
            'details.item',
            'details.supplier',
            'invoice',
            'notaKeluars.supplier',
            'payments.account',
        ])->findOrFail($stockTransaction);

        $accounts = \App\Models\ChartOfAccount::query()
            ->where('is_cash_bank', true)
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        /*
        |--------------------------------------------------------------------------
        | RINGKASAN SALDO PETTY CASH PJ
        |--------------------------------------------------------------------------
        */

        $pettyCashSummary = [];

        // Ambil pembayaran nota yang menggunakan petty cash.
        $pettyCashPayments = $stockTransaction->payments
            ->where('source_type', 'petty_cash')
            ->groupBy('purchase_responsible_id');

        foreach ($pettyCashPayments as $responsibleId => $payments) {
            $responsible = \App\Models\PurchaseResponsible::find(
                $responsibleId
            );

            if (!$responsible) {
                continue;
            }

            // Total pembayaran petty cash untuk nota ini oleh PJ tersebut.
            $transactionPayment = (float) $payments->sum('amount');

            // Saldo bersih petty cash PJ dari seluruh buku transaksi.
            $currentBalance = (float)
                \App\Models\PettyCashTransaction::query()
                    ->where('purchase_responsible_id', $responsibleId)
                    ->selectRaw("
                        COALESCE(
                            SUM(
                                CASE
                                    WHEN type = 'top_up' THEN amount
                                    WHEN type = 'purchase_payment' THEN -amount
                                    ELSE 0
                                END
                            ), 0
                        ) as balance
                    ")
                    ->value('balance');

            $pettyCashSummary[] = [
                'responsible_name' => $responsible->name,
                'opening_balance' => $currentBalance + $transactionPayment,
                'remaining_balance' => $currentBalance,
                'transaction_payment' => $transactionPayment,
            ];
        }

        return view(
            'stock_transactions.show',
            compact(
                'stockTransaction',
                'accounts',
                'pettyCashSummary'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FORM EDIT TRANSAKSI
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {
        $stockTransaction = StockTransaction::with([
            'supplier',
            'purchaseResponsible',
            'details.item',
            'details.supplier',
            'purchaseOrder.details',
        ])->findOrFail($id);

        $items = Item::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $kitchens = Kitchen::orderBy('name')->get();

        if ($stockTransaction->type === 'IN') {
            $responsibles = PurchaseResponsible::where('is_active', true)
                ->orderBy('name')
                ->get();

            return view('stock_transactions.edit_in', compact(
                'stockTransaction',
                'items',
                'suppliers',
                'kitchens',
                'responsibles'
            ));
        }

        return view('stock_transactions.edit', compact(
            'stockTransaction',
            'items',
            'suppliers',
            'kitchens'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE BARANG MASUK / BARANG KELUAR
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
    {
        $existingTransaction = StockTransaction::findOrFail($id);

        $rules = [
            'transaction_date' => [
                'required',
                'date',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'items' => [
                'required',
                'array',
                'min:1',
            ],
            'items.*.item_id' => [
                'required',
                'integer',
                'exists:items,id',
            ],
            'items.*.quantity' => [
                'required',
                'numeric',
                'min:0',
            ],
            'items.*.unit_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'items.*.discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'items.*.unit' => [
                'nullable',
                'string',
                'max:50',
            ],
            'items.*.section_name' => [
                'nullable',
                'string',
                'max:100',
            ],
            'items.*.section_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'items.*.reimburse' => [
                'nullable',
                'boolean',
            ],
        ];

        if ($existingTransaction->type === 'IN') {
            $rules['vendor_name'] = [
                'sometimes',
                'nullable',
                'string',
                'max:150',
            ];

            $rules['purchase_note_number'] = [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ];

            $rules['porter_cost'] = [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
            ];

            $rules['fuel_cost'] = [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999999.99',
            ];

            $rules['fuel_cost_details'] = [
                'sometimes',
                'nullable',
                'array',
            ];

            $rules['fuel_cost_details.*.vehicle_name'] = [
                'nullable',
                'string',
                'max:100',
            ];

            $rules['fuel_cost_details.*.vehicle_plate'] = [
                'nullable',
                'string',
                'max:30',
            ];

            $rules['fuel_cost_details.*.receipt_number'] = [
                'nullable',
                'string',
                'max:100',
            ];

            $rules['fuel_cost_details.*.amount'] = [
                'required_with:fuel_cost_details',
                'numeric',
                'min:0',
                'max:9999999999999.99',
            ];

            $rules['parking_cost'] = [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999999.99',
            ];

            $rules['cooking_cost'] = [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999999.99',
            ];

            $rules['other_cost'] = [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999999.99',
            ];

            $rules['purchase_discount_type'] = [
                'sometimes',
                'nullable',
                'in:amount,percent',
            ];

            $rules['purchase_discount_value'] = [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999999.99',
            ];

            $rules['purchase_responsible_id'] = [
                'sometimes',
                'required',
                'integer',
                Rule::exists('purchase_responsibles', 'id')
                    ->where('is_active', true),
            ];
        }

        if ($existingTransaction->type === 'OUT') {
            $rules['items.*.supplier_id'] = [
                'required',
                'integer',
                'exists:suppliers,id',
            ];
        }

        $validated = $request->validate($rules, [
            'items.required' =>
                'Minimal satu barang harus dipilih.',
            'items.min' =>
                'Minimal satu barang harus dipilih.',
            'items.*.item_id.required' =>
                'Barang wajib dipilih.',
            'items.*.item_id.exists' =>
                'Barang tidak ditemukan.',
            'items.*.quantity.required' =>
                'Jumlah barang wajib diisi.',
            'items.*.quantity.min' =>
                'Jumlah barang tidak boleh negatif.',
            'items.*.discount.numeric' =>
                'Diskon barang harus berupa angka.',
            'items.*.discount.min' =>
                'Diskon barang tidak boleh negatif.',
            'items.*.supplier_id.required' =>
                'Supplier setiap barang wajib dipilih.',
            'items.*.supplier_id.exists' =>
                'Supplier barang tidak ditemukan.',
            'items.*.section_order.integer' =>
                'Urutan Bagian harus berupa angka.',
            'items.*.reimburse.boolean' =>
                'Status Reimburse tidak valid.',
            'porter_cost.numeric' =>
                'Biaya kuli harus berupa angka.',
            'porter_cost.min' =>
                'Biaya kuli tidak boleh negatif.',
            'fuel_cost.numeric' =>
                'Biaya BBM harus berupa angka.',
            'fuel_cost.min' =>
                'Biaya BBM tidak boleh negatif.',
            'parking_cost.numeric' =>
                'Biaya parkir harus berupa angka.',
            'parking_cost.min' =>
                'Biaya parkir tidak boleh negatif.',
            'purchase_discount_type.in' =>
                'Jenis diskon tambahan nota tidak valid.',
            'purchase_discount_value.numeric' =>
                'Nilai diskon tambahan nota harus berupa angka.',
            'purchase_discount_value.min' =>
                'Diskon tambahan nota tidak boleh negatif.',
            'purchase_responsible_id.required' =>
                'Penanggung jawab pembelian wajib dipilih.',
            'purchase_responsible_id.exists' =>
                'Penanggung jawab tidak ditemukan atau sudah tidak aktif.',
        ]);

        if ($existingTransaction->type === 'IN') {
            $discountType = array_key_exists(
                'purchase_discount_type',
                $validated
            )
                ? ($validated['purchase_discount_type'] ?: 'amount')
                : (
                    $existingTransaction->purchase_discount_type
                    ?: 'amount'
                );

            $discountValue = array_key_exists(
                'purchase_discount_value',
                $validated
            )
                ? (float) ($validated['purchase_discount_value'] ?? 0)
                : (float) (
                    $existingTransaction->purchase_discount_value ?? 0
                );

            if ($discountType === 'percent' && $discountValue > 100) {
                throw ValidationException::withMessages([
                    'purchase_discount_value' =>
                        'Diskon persentase tidak boleh lebih dari 100%.',
                ]);
            }
        }

        DB::transaction(function () use ($validated, $id) {
            $transaction = StockTransaction::with([
                'details.item',
                'purchaseOrder.details',
            ])
                ->lockForUpdate()
                ->findOrFail($id);

            $oldDetails = $transaction->details->values();

            $purchaseOrderDetails = $transaction->purchaseOrder?->details
                ?? collect();

            $oldItemIds = $oldDetails->pluck('item_id');

            $newItemIds = collect($validated['items'])
                ->pluck('item_id')
                ->map(fn ($itemId) => (int) $itemId);

            $allItemIds = $oldItemIds
                ->merge($newItemIds)
                ->unique()
                ->sort()
                ->values();

            $stocks = Stock::whereIn('item_id', $allItemIds)
                ->orderBy('item_id')
                ->lockForUpdate()
                ->get()
                ->keyBy('item_id');

            /*
             * Kembalikan dampak semua detail lama terhadap stok.
             */
            foreach ($oldDetails as $detail) {
                $stock = $stocks->get($detail->item_id);

                if (!$stock) {
                    if ($transaction->type === 'IN') {
                        throw ValidationException::withMessages([
                            'items' =>
                                'Stok barang lama tidak ditemukan. Transaksi tidak dapat diperbarui dengan aman.',
                        ]);
                    }

                    $stock = Stock::create([
                        'item_id' => $detail->item_id,
                        'quantity' => 0,
                    ]);

                    $stocks->put($detail->item_id, $stock);
                }

                if ($transaction->type === 'IN') {
                    $newQuantity = (float) $stock->quantity
                        - (float) $detail->quantity;

                    if ($newQuantity < 0) {
                        throw ValidationException::withMessages([
                            'items' =>
                                'Stok saat ini tidak cukup untuk membatalkan barang masuk lama. Periksa transaksi stok terkait terlebih dahulu.',
                        ]);
                    }

                    $stock->quantity = $newQuantity;
                } else {
                    $stock->quantity = (float) $stock->quantity
                        + (float) $detail->quantity;
                }

                $stock->save();
            }

            $items = Item::whereIn('id', $newItemIds->unique())
                ->get()
                ->keyBy('id');

            $transaction->details()->delete();

            $usedOldDetailIds = [];
            $usedPoDetailIds = [];

            foreach ($validated['items'] as $row) {
                $itemId = (int) $row['item_id'];
                $item = $items->get($itemId);

                if (!$item) {
                    throw ValidationException::withMessages([
                        'items' => 'Barang tidak ditemukan.',
                    ]);
                }

                $quantity = (float) $row['quantity'];

                if ($quantity <= 0) {
                    continue;
                }

                $unitPrice = (
                    isset($row['unit_price'])
                    && $row['unit_price'] !== ''
                    && $row['unit_price'] !== null
                )
                    ? (float) $row['unit_price']
                    : (float) ($item->price ?? 0);

                $discount = (float) ($row['discount'] ?? 0);

                if ($unitPrice < 0 || $discount < 0) {
                    throw ValidationException::withMessages([
                        'items' =>
                            'Harga dan diskon barang tidak boleh negatif.',
                    ]);
                }

                $subtotalBeforeDiscount = $quantity * $unitPrice;

                if (
                    $transaction->type === 'IN'
                    && $discount > $subtotalBeforeDiscount
                ) {
                    throw ValidationException::withMessages([
                        'items' => 'Diskon barang "'
                            . $item->name
                            . '" tidak boleh melebihi total harga barang.',
                    ]);
                }

                $stock = $stocks->get($itemId);

                if (!$stock) {
                    $stock = Stock::create([
                        'item_id' => $itemId,
                        'quantity' => 0,
                    ]);

                    $stocks->put($itemId, $stock);
                }

                if ($transaction->type === 'IN') {
                    $supplierId = $item->supplier_id
                        ? (int) $item->supplier_id
                        : null;

                    $stock->quantity =
                        (float) $stock->quantity + $quantity;
                } else {
                    $supplierId = (int) ($row['supplier_id'] ?? 0);

                    if (!Supplier::whereKey($supplierId)->exists()) {
                        throw ValidationException::withMessages([
                            'items' => 'Supplier barang tidak ditemukan.',
                        ]);
                    }

                    if ((float) $stock->quantity < $quantity) {
                        throw ValidationException::withMessages([
                            'items' =>
                                'Stok ' . $item->name
                                . ' tidak mencukupi. Stok tersedia: '
                                . number_format(
                                    (float) $stock->quantity,
                                    2,
                                    ',',
                                    '.'
                                )
                                . ', kebutuhan: '
                                . number_format(
                                    $quantity,
                                    2,
                                    ',',
                                    '.'
                                )
                                . '.',
                        ]);
                    }

                    $stock->quantity =
                        (float) $stock->quantity - $quantity;
                }

                $stock->save();

                $sectionName = trim(
                    (string) ($row['section_name'] ?? '')
                );

                $oldDetail = $oldDetails->first(
                    function ($detail) use (
                        $itemId,
                        $supplierId,
                        $sectionName,
                        &$usedOldDetailIds,
                        $transaction
                    ) {
                        if (in_array($detail->id, $usedOldDetailIds)) {
                            return false;
                        }

                        if ((int) $detail->item_id !== $itemId) {
                            return false;
                        }

                        $oldSupplierId = $detail->supplier_id
                            ? (int) $detail->supplier_id
                            : null;

                        if (
                            $transaction->type === 'OUT'
                            && $oldSupplierId !== $supplierId
                        ) {
                            return false;
                        }

                        if ($sectionName !== '') {
                            return trim(
                                (string) ($detail->section_name ?? '')
                            ) === $sectionName;
                        }

                        return true;
                    }
                );

                if (!$oldDetail) {
                    $oldDetail = $oldDetails->first(
                        function ($detail) use (
                            $itemId,
                            $supplierId,
                            &$usedOldDetailIds,
                            $transaction
                        ) {
                            if (in_array($detail->id, $usedOldDetailIds)) {
                                return false;
                            }

                            if ((int) $detail->item_id !== $itemId) {
                                return false;
                            }

                            $oldSupplierId = $detail->supplier_id
                                ? (int) $detail->supplier_id
                                : null;

                            return $transaction->type !== 'OUT'
                                || $oldSupplierId === $supplierId;
                        }
                    );
                }

                if ($oldDetail) {
                    $usedOldDetailIds[] = $oldDetail->id;
                }

                $poDetail = $purchaseOrderDetails->first(
                    function ($detail) use (
                        $itemId,
                        $supplierId,
                        $sectionName,
                        &$usedPoDetailIds
                    ) {
                        if (in_array($detail->id, $usedPoDetailIds)) {
                            return false;
                        }

                        if ((int) $detail->item_id !== $itemId) {
                            return false;
                        }

                        $detailSupplierId = $detail->supplier_id
                            ?? $detail->item?->supplier_id
                            ?? null;

                        $detailSupplierId = $detailSupplierId
                            ? (int) $detailSupplierId
                            : null;

                        if ($detailSupplierId !== $supplierId) {
                            return false;
                        }

                        if ($sectionName !== '') {
                            return trim(
                                (string) ($detail->section_name ?? '')
                            ) === $sectionName;
                        }

                        return true;
                    }
                );

                if (!$poDetail) {
                    $poDetail = $purchaseOrderDetails->first(
                        function ($detail) use (
                            $itemId,
                            $supplierId,
                            &$usedPoDetailIds
                        ) {
                            if (in_array($detail->id, $usedPoDetailIds)) {
                                return false;
                            }

                            if ((int) $detail->item_id !== $itemId) {
                                return false;
                            }

                            $detailSupplierId = $detail->supplier_id
                                ?? $detail->item?->supplier_id
                                ?? null;

                            $detailSupplierId = $detailSupplierId
                                ? (int) $detailSupplierId
                                : null;

                            return $detailSupplierId === $supplierId;
                        }
                    );
                }

                if ($poDetail) {
                    $usedPoDetailIds[] = $poDetail->id;
                }

                if ($sectionName === '') {
                    $sectionName = trim((string) (
                        $oldDetail?->section_name
                        ?? $poDetail?->section_name
                        ?? ''
                    ));
                }

                $sectionOrder = $row['section_order'] ?? null;

                if ($sectionOrder === null || $sectionOrder === '') {
                    $sectionOrder = $oldDetail?->section_order
                        ?? $poDetail?->section_order
                        ?? 0;
                }

                if (array_key_exists('reimburse', $row)) {
                    $isReimburse = (bool) $row['reimburse'];
                } else {
                    $oldNotes = trim(
                        (string) ($oldDetail?->notes ?? '')
                    );

                    $poNotes = trim(
                        (string) ($poDetail?->notes ?? '')
                    );

                    $isReimburse = (
                        $oldNotes === '*Reimburse'
                        || $poNotes === '*Reimburse'
                    );
                }

                $subtotal = $transaction->type === 'IN'
                    ? round(
                        max(0, $subtotalBeforeDiscount - $discount),
                        2
                    )
                    : round($quantity * $unitPrice, 2);

                $detailData = [
                    'item_id' => $itemId,
                    'supplier_id' => $supplierId,
                    'quantity' => $quantity,
                    'unit' => !empty($row['unit'])
                        ? $row['unit']
                        : $item->unit,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ];

                if ($transaction->type === 'IN') {
                    $detailData['discount'] = $discount;
                    $detailData['allocated_porter_cost'] = 0;
                }

                if ($transaction->type === 'OUT') {
                    $detailData['section_name'] = $sectionName !== ''
                        ? $sectionName
                        : null;

                    $detailData['section_order'] = (int) $sectionOrder;
                }

                $detailTable = $transaction->details()
                    ->getModel()
                    ->getTable();

                if (Schema::hasColumn($detailTable, 'notes')) {
                    $detailData['notes'] = $isReimburse
                        ? '*Reimburse'
                        : null;
                } elseif (
                    $transaction->type === 'OUT'
                    && $isReimburse
                ) {
                    throw ValidationException::withMessages([
                        'items' =>
                            'Kolom notes pada detail transaksi belum tersedia. Tambahkan kolom tersebut sebelum menyimpan Reimburse.',
                    ]);
                }

                $transaction->details()->create($detailData);
            }

            $transaction->transaction_date =
                $validated['transaction_date'];

            $transaction->notes = $validated['notes'] ?? null;

            if ($transaction->type === 'IN') {
                $transaction->supplier_id = null;

                if (array_key_exists('vendor_name', $validated)) {
                    $transaction->vendor_name = $validated['vendor_name'];
                }

                if (array_key_exists('purchase_note_number', $validated)) {
                    $transaction->purchase_note_number =
                        $validated['purchase_note_number'];
                }

                if (array_key_exists('purchase_responsible_id', $validated)) {
                    $transaction->purchase_responsible_id =
                        $validated['purchase_responsible_id'];
                }

                if (array_key_exists('porter_cost', $validated)) {
                    $transaction->porter_cost = round(
                        (float) ($validated['porter_cost'] ?? 0),
                        2
                    );
                }

                if (array_key_exists('fuel_cost_details', $validated)) {
                    $fuelCostDetails = collect(
                        $validated['fuel_cost_details'] ?? []
                    )->map(function ($row) {
                        return [
                            'vehicle_name' => trim($row['vehicle_name'] ?? ''),
                            'vehicle_plate' => trim($row['vehicle_plate'] ?? ''),
                            'receipt_number' => trim($row['receipt_number'] ?? ''),
                            'amount' => round((float) ($row['amount'] ?? 0), 2),
                        ];
                    })->filter(function ($row) {
                        return $row['vehicle_name'] !== ''
                            || $row['vehicle_plate'] !== ''
                            || $row['receipt_number'] !== ''
                            || $row['amount'] > 0;
                    })->values()->all();

                    $transaction->fuel_cost_details = $fuelCostDetails;

                    $transaction->fuel_cost = round(
                        collect($fuelCostDetails)->sum('amount'),
                        2
                    );
                } elseif (array_key_exists('fuel_cost', $validated)) {
                    $transaction->fuel_cost = round(
                        (float) ($validated['fuel_cost'] ?? 0),
                        2
                    );
                }

                if (array_key_exists('parking_cost', $validated)) {
                    $transaction->parking_cost = round(
                        (float) ($validated['parking_cost'] ?? 0),
                        2
                    );
                }

                if (array_key_exists('cooking_cost', $validated)) {
                    $transaction->cooking_cost = round(
                        (float) ($validated['cooking_cost'] ?? 0),
                        2
                    );
                }

                if (array_key_exists('other_cost', $validated)) {
                    $transaction->other_cost = round(
                        (float) ($validated['other_cost'] ?? 0),
                        2
                    );
                }

                if (array_key_exists('purchase_discount_type', $validated)) {
                    $transaction->purchase_discount_type =
                        $validated['purchase_discount_type'] ?: 'amount';
                } elseif (!$transaction->purchase_discount_type) {
                    $transaction->purchase_discount_type = 'amount';
                }

                if (array_key_exists('purchase_discount_value', $validated)) {
                    $transaction->purchase_discount_value = round(
                        (float) ($validated['purchase_discount_value'] ?? 0),
                        2
                    );
                } elseif ($transaction->purchase_discount_value === null) {
                    $transaction->purchase_discount_value = 0;
                }

                $discountType = $transaction->purchase_discount_type ?: 'amount';
                $discountValue = round(
                    (float) ($transaction->purchase_discount_value ?? 0),
                    2
                );

                if ($discountType === 'percent' && $discountValue > 100) {
                    throw ValidationException::withMessages([
                        'purchase_discount_value' =>
                            'Diskon persentase tidak boleh lebih dari 100%.',
                    ]);
                }

                $goodsSubtotal = round(
                    (float) $transaction->details()->sum('subtotal'),
                    2
                );

                if (
                    $discountType === 'amount'
                    && $discountValue > $goodsSubtotal
                ) {
                    throw ValidationException::withMessages([
                        'purchase_discount_value' =>
                            'Diskon tambahan nota tidak boleh melebihi subtotal barang.',
                    ]);
                }

                $this->calculatePurchaseDiscount(
                    $goodsSubtotal,
                    $discountType,
                    $discountValue
                );
            }

            $transaction->save();

            if ($transaction->type === 'IN') {

                /*
                * Hitung ulang total nota setelah barang,
                * diskon, dan biaya diperbarui.
                */
                $goodsSubtotal = round(
                    (float) $transaction->details()->sum('subtotal'),
                    2
                );

                $discountType = $transaction->purchase_discount_type
                    ?: 'amount';

                $discountValue = round(
                    (float) ($transaction->purchase_discount_value ?? 0),
                    2
                );

                $discountAmount = $this->calculatePurchaseDiscount(
                    $goodsSubtotal,
                    $discountType,
                    $discountValue
                );

                $grandTotal = round(
                    max(0, $goodsSubtotal - $discountAmount)
                        + (float) ($transaction->porter_cost ?? 0)
                        + (float) ($transaction->fuel_cost ?? 0)
                        + (float) ($transaction->parking_cost ?? 0)
                        + (float) ($transaction->cooking_cost ?? 0)
                        + (float) ($transaction->other_cost ?? 0),
                    2
                );

                /*
                * Pembayaran lama tetap dipertahankan.
                * Hanya hitung jumlahnya untuk menentukan status nota.
                */
                $paidAmount = round(
                    (float) $transaction->payments()->sum('amount'),
                    2
                );

                if ($paidAmount > 0 && $paidAmount >= $grandTotal) {
                    $transaction->payment_status = 'paid';

                    /*
                    * Ambil tanggal pembayaran terakhir yang tercatat.
                    */
                    $transaction->paid_at = $transaction->payments()
                        ->orderByDesc('payment_date')
                        ->orderByDesc('id')
                        ->value('payment_date');
                } elseif ($paidAmount > 0) {
                    $transaction->payment_status = 'partial';
                    $transaction->paid_at = null;
                } else {
                    $transaction->payment_status = 'unpaid';
                    $transaction->paid_at = null;
                }

                $transaction->save();
            }

            if ($transaction->type === 'OUT') {
                app(InvoiceController::class)->syncFromOut($transaction);
            }
        });

        $transactionType = StockTransaction::whereKey($id)
            ->value('type');

        return redirect()
            ->route(
                $transactionType === 'OUT'
                    ? 'stock-transactions.out'
                    : 'stock-transactions.index'
            )
            ->with(
                'success',
                $transactionType === 'OUT'
                    ? 'Barang Keluar berhasil diperbarui. Stok dan Invoice telah disinkronkan jika Invoice tersedia.'
                    : 'Transaksi Barang Masuk berhasil diperbarui. Stok, diskon nota, dan alokasi biaya kuli telah dihitung ulang.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | HAPUS BARANG MASUK / BARANG KELUAR
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            $transactionType = null;

            DB::transaction(function () use ($id, &$transactionType) {
                $transaction = StockTransaction::with([
                    'details.item',
                    'purchaseOrder',
                ])
                    ->lockForUpdate()
                    ->findOrFail($id);

                $transactionType = $transaction->type;

                $invoice = Invoice::where(
                    'stock_transaction_id',
                    $transaction->id
                )
                    ->lockForUpdate()
                    ->first();

                $notaIds = NotaKeluar::where(
                    'stock_transaction_id',
                    $transaction->id
                )->pluck('id')->all();

                $detailsByItem = $transaction->details
                    ->filter(fn ($detail) => $detail->item_id !== null)
                    ->groupBy('item_id');

                $itemIds = $detailsByItem->keys()
                    ->map(fn ($id) => (int) $id)
                    ->sort()
                    ->values();

                $stocks = Stock::whereIn('item_id', $itemIds)
                    ->orderBy('item_id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('item_id');

                if ($transaction->type === 'IN') {
                    foreach ($detailsByItem as $itemId => $details) {
                        $itemId = (int) $itemId;

                        $requiredQuantity = (float) $details->sum(
                            fn ($detail) => (float) $detail->quantity
                        );

                        $stock = $stocks->get($itemId);

                        $availableQuantity = $stock
                            ? (float) $stock->quantity
                            : 0;

                        if ($availableQuantity < $requiredQuantity) {
                            $itemName = $details->first()->item?->name
                                ?? Item::whereKey($itemId)->value('name')
                                ?? 'Barang ID ' . $itemId;

                            $formatQuantity = function ($quantity) {
                                return rtrim(
                                    rtrim(
                                        number_format(
                                            (float) $quantity,
                                            2,
                                            ',',
                                            '.'
                                        ),
                                        '0'
                                    ),
                                    ','
                                );
                            };

                            throw ValidationException::withMessages([
                                'transaction' => sprintf(
                                    'Barang "%s" tidak dapat dihapus. Jumlah pada transaksi yang ingin dibatalkan: %s. Stok tersedia saat ini: %s. Penghapusan dibatalkan agar stok tidak menjadi minus. Periksa transaksi Barang Keluar terkait terlebih dahulu.',
                                    $itemName,
                                    $formatQuantity($requiredQuantity),
                                    $formatQuantity($availableQuantity)
                                ),
                            ]);
                        }
                    }
                }

                if (Schema::hasTable('invoice_nota_allocations')) {
                    $allocationQuery = DB::table(
                        'invoice_nota_allocations'
                    );

                    $hasAllocation = false;

                    if (!empty($notaIds)) {
                        $allocationQuery->whereIn(
                            'nota_keluar_id',
                            $notaIds
                        );

                        $hasAllocation = true;
                    }

                    if (
                        $invoice
                        && Schema::hasTable('invoice_details')
                    ) {
                        $invoiceDetailIds = DB::table('invoice_details')
                            ->where('invoice_id', $invoice->id)
                            ->pluck('id')
                            ->all();

                        if (!empty($invoiceDetailIds)) {
                            if ($hasAllocation) {
                                $allocationQuery->orWhereIn(
                                    'invoice_detail_id',
                                    $invoiceDetailIds
                                );
                            } else {
                                $allocationQuery->whereIn(
                                    'invoice_detail_id',
                                    $invoiceDetailIds
                                );
                            }

                            $hasAllocation = true;
                        }
                    }

                    if ($hasAllocation) {
                        $allocationQuery->delete();
                    }
                }

                if (!empty($notaIds)) {
                    foreach ([
                        'nota_keluar_details',
                        'nota_keluar_items',
                    ] as $detailTable) {
                        if (
                            Schema::hasTable($detailTable)
                            && Schema::hasColumn(
                                $detailTable,
                                'nota_keluar_id'
                            )
                        ) {
                            DB::table($detailTable)
                                ->whereIn('nota_keluar_id', $notaIds)
                                ->delete();
                        }
                    }

                    NotaKeluar::where(
                        'stock_transaction_id',
                        $transaction->id
                    )->delete();
                }

                if ($invoice) {
                    if (Schema::hasTable('invoice_details')) {
                        DB::table('invoice_details')
                            ->where('invoice_id', $invoice->id)
                            ->delete();
                    }

                    $invoice->delete();
                }

                foreach ($transaction->details as $detail) {
                    if (!$detail->item_id) {
                        continue;
                    }

                    $itemId = (int) $detail->item_id;
                    $stock = $stocks->get($itemId);

                    if (!$stock) {
                        if ($transaction->type === 'IN') {
                            throw ValidationException::withMessages([
                                'transaction' =>
                                    'Data stok barang masuk tidak ditemukan. Transaksi tidak dihapus.',
                            ]);
                        }

                        $stock = Stock::create([
                            'item_id' => $itemId,
                            'quantity' => 0,
                        ]);

                        $stocks->put($itemId, $stock);
                    }

                    if ($transaction->type === 'IN') {
                        $stock->quantity = (float) $stock->quantity
                            - (float) $detail->quantity;
                    } else {
                        $stock->quantity = (float) $stock->quantity
                            + (float) $detail->quantity;
                    }

                    $stock->save();
                }

                $purchaseOrderId = $transaction->purchase_order_id;

                                /*
                |--------------------------------------------------------------------------
                | HAPUS LEDGER KAS KECIL TERKAIT PEMBAYARAN NOTA
                |--------------------------------------------------------------------------
                */

                if (
                    Schema::hasTable('petty_cash_transactions')
                    && Schema::hasTable('stock_transaction_payments')
                ) {
                    $paymentIds = DB::table('stock_transaction_payments')
                        ->where('stock_transaction_id', $transaction->id)
                        ->pluck('id');

                    if ($paymentIds->isNotEmpty()) {
                        DB::table('petty_cash_transactions')
                            ->whereIn(
                                'stock_transaction_payment_id',
                                $paymentIds
                            )
                            ->delete();
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | HAPUS PEMBAYARAN NOTA
                |--------------------------------------------------------------------------
                */

                if (Schema::hasTable('stock_transaction_payments')) {
                    DB::table('stock_transaction_payments')
                        ->where('stock_transaction_id', $transaction->id)
                        ->delete();
                }

                /*
                |--------------------------------------------------------------------------
                | HAPUS DETAIL TRANSAKSI
                |--------------------------------------------------------------------------
                */

                $transaction->details()->delete();

                /*
                |--------------------------------------------------------------------------
                | HAPUS TRANSAKSI
                |--------------------------------------------------------------------------
                */

                $transaction->delete();
            });

            return redirect()
                ->route(
                    $transactionType === 'OUT'
                        ? 'stock-transactions.out'
                        : 'stock-transactions.index'
                )
                ->with(
                    'success',
                    'Transaksi berhasil dihapus dan stok telah diperbarui.'
                );

        } catch (ValidationException $e) {
            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FORM EDIT BARANG MASUK
    |--------------------------------------------------------------------------
    */

    public function editIn(StockTransaction $stockTransaction)
    {
        abort_unless($stockTransaction->type === 'IN', 404);

        $stockTransaction->load([
            'details.item',
            'details.supplier',
            'purchaseResponsible',
        ]);

        $suppliers = Supplier::orderBy('name')->get();
        $items = Item::orderBy('name')->get();
        $kitchens = Kitchen::orderBy('name')->get();

        $responsibles = PurchaseResponsible::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('stock_transactions.edit_in', compact(
            'stockTransaction',
            'suppliers',
            'items',
            'kitchens',
            'responsibles'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | HITUNG DISKON TAMBAHAN NOTA
    |--------------------------------------------------------------------------
    */

    private function calculatePurchaseDiscount(
        float $goodsSubtotal,
        string $discountType,
        float $discountValue
    ): float {
        $goodsSubtotal = round(max(0, $goodsSubtotal), 2);
        $discountValue = round(max(0, $discountValue), 2);

        if ($discountType === 'percent') {
            if ($discountValue > 100) {
                throw ValidationException::withMessages([
                    'purchase_discount_value' =>
                        'Diskon persentase tidak boleh lebih dari 100%.',
                ]);
            }

            return round(
                $goodsSubtotal * $discountValue / 100,
                2
            );
        }

        return round(min($goodsSubtotal, $discountValue), 2);
    }
}
