<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Kitchen;
use App\Models\LPDH;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

        return view(
            'lpdhs.index',
            compact('lpdhs')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $kitchens = Kitchen::where(
                'status',
                true
            )
            ->orderBy('name')
            ->get();

        return view(
            'lpdhs.create',
            compact('kitchens')
        );
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

                Rule::unique(
                    'lpdhs',
                    'service_date'
                )->where(function ($query) use ($request) {

                    return $query->where(
                        'kitchen_id',
                        $request->kitchen_id
                    );

                }),
            ],

            'day_status' =>
                'required|string|max:50',

            'beneficiaries_count' =>
                'required|integer|min:0',

            'raw_material_expenses' =>
                'required|numeric|min:0',

            'incentive_calculated' =>
                'required|numeric|min:0',

            'incentive_paid' =>
                'required|numeric|min:0',

            'va_final_balance' =>
                'required|numeric|min:0',

            'topup_proposal' =>
                'required|numeric|min:0',

            'inspection_result' =>
                'nullable|string|max:100',
        ]);


        /*
        |--------------------------------------------------------------------------
        | INSENTIF PENERIMA MANFAAT
        |--------------------------------------------------------------------------
        */

        $beneficiaryRentIncentive =
            (int) $data['beneficiaries_count']
            * 2000;


        /*
        |--------------------------------------------------------------------------
        | AMBIL TOTAL DARI INVOICE
        |--------------------------------------------------------------------------
        */

        $invoiceTotals =
            $this->getInvoiceTotals(
                $data['kitchen_id'],
                $data['service_date']
            );


        /*
        |--------------------------------------------------------------------------
        | BAHAN BAKU
        |--------------------------------------------------------------------------
        */

        $data['raw_material_expenses'] =
            $invoiceTotals['raw_material_total'];


        /*
        |--------------------------------------------------------------------------
        | INSENTIF PENERIMA MANFAAT
        |--------------------------------------------------------------------------
        */

        $data['beneficiary_rent_incentive'] =
            $beneficiaryRentIncentive;


        /*
        |--------------------------------------------------------------------------
        | SEMUA BIAYA OPERASIONAL DARI INVOICE
        |--------------------------------------------------------------------------
        */

        $data['operational_expenses'] =
            $invoiceTotals['operational_total'];


        /*
        |--------------------------------------------------------------------------
        | SIMPAN LPDH
        |--------------------------------------------------------------------------
        */

        LPDH::create($data);


        return redirect()
            ->route('lpdhs.index')
            ->with(
                'success',
                'LPDH berhasil dibuat.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(LPDH $lpdh)
    {
        $lpdh->load('kitchen');

        return view(
            'lpdhs.show',
            compact('lpdh')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(LPDH $lpdh)
    {
        $kitchens = Kitchen::where(
                'status',
                true
            )
            ->orderBy('name')
            ->get();

        return view(
            'lpdhs.edit',
            compact(
                'lpdh',
                'kitchens'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        LPDH $lpdh
    ) {

        $data = $request->validate([

            'kitchen_id' => [
                'required',
                'exists:kitchens,id',
            ],

            'service_date' => [
                'required',
                'date',

                Rule::unique(
                    'lpdhs',
                    'service_date'
                )
                ->ignore($lpdh->id)
                ->where(function ($query) use ($request) {

                    return $query->where(
                        'kitchen_id',
                        $request->kitchen_id
                    );

                }),
            ],

            'day_status' =>
                'required|string|max:50',

            'beneficiaries_count' =>
                'required|integer|min:0',

            'raw_material_expenses' =>
                'required|numeric|min:0',

            'incentive_calculated' =>
                'required|numeric|min:0',

            'incentive_paid' =>
                'required|numeric|min:0',

            'va_final_balance' =>
                'required|numeric|min:0',

            'topup_proposal' =>
                'required|numeric|min:0',

            'inspection_result' =>
                'nullable|string|max:100',
        ]);


        /*
        |--------------------------------------------------------------------------
        | INSENTIF
        |--------------------------------------------------------------------------
        */

        $beneficiaryRentIncentive =
            (int) $data['beneficiaries_count']
            * 2000;


        /*
        |--------------------------------------------------------------------------
        | AMBIL TOTAL INVOICE
        |--------------------------------------------------------------------------
        */

        $invoiceTotals =
            $this->getInvoiceTotals(
                $data['kitchen_id'],
                $data['service_date']
            );


        /*
        |--------------------------------------------------------------------------
        | UPDATE BAHAN BAKU
        |--------------------------------------------------------------------------
        */

        $data['raw_material_expenses'] =
            $invoiceTotals['raw_material_total'];


        /*
        |--------------------------------------------------------------------------
        | UPDATE INSENTIF
        |--------------------------------------------------------------------------
        */

        $data['beneficiary_rent_incentive'] =
            $beneficiaryRentIncentive;


        /*
        |--------------------------------------------------------------------------
        | UPDATE OPERASIONAL
        |--------------------------------------------------------------------------
        */

        $data['operational_expenses'] =
            $invoiceTotals['operational_total'];


        /*
        |--------------------------------------------------------------------------
        | UPDATE LPDH
        |--------------------------------------------------------------------------
        */

        $lpdh->update($data);


        return redirect()
            ->route('lpdhs.index')
            ->with(
                'success',
                'LPDH berhasil diperbarui.'
            );
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
            ->with(
                'success',
                'LPDH berhasil dihapus.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | AJAX TOTAL INVOICE
    |--------------------------------------------------------------------------
    */

    public function invoiceTotal(
        Request $request
    ) {

        $request->validate([

            'kitchen_id' => [
                'required',
                'exists:kitchens,id',
            ],

            'service_date' => [
                'required',
                'date',
            ],
        ]);


        $totals =
            $this->getInvoiceTotals(
                $request->kitchen_id,
                $request->service_date
            );


        return response()->json([

            'raw_material_total' =>
                $totals['raw_material_total'],

            'operational_total' =>
                $totals['operational_total'],

            'invoice_count' =>
                $totals['invoice_count'],

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | GET TOTAL INVOICE
    |--------------------------------------------------------------------------
    */

    private function getInvoiceTotals(
        $kitchenId,
        $serviceDate
    ): array {

        /*
        |--------------------------------------------------------------------------
        | CARI INVOICE
        |--------------------------------------------------------------------------
        |
        | Invoice tetap dicocokkan berdasarkan:
        |
        | 1. kitchen_id
        | 2. invoice_date
        |
        */

        $invoices = Invoice::query()
            ->where(
                'kitchen_id',
                $kitchenId
            )
            ->whereDate(
                'invoice_date',
                $serviceDate
            )
            ->with([
                'details.supplier',
                'expenses.expenseType',
            ])
            ->get();


        /*
        |--------------------------------------------------------------------------
        | TOTAL
        |--------------------------------------------------------------------------
        */

        $rawMaterialTotal = 0;

        $operationalTotal = 0;


        /*
        |--------------------------------------------------------------------------
        | HITUNG
        |--------------------------------------------------------------------------
        */

        foreach ($invoices as $invoice) {


            /*
            |--------------------------------------------------------------------------
            | BAHAN BAKU PANGAN
            |--------------------------------------------------------------------------
            */

            foreach (
                $invoice->details
                as $detail
            ) {

                $supplierName =
                    strtolower(
                        trim(
                            $detail
                                ->supplier
                                ?->name ?? ''
                        )
                    );


                if (
                    str_contains(
                        $supplierName,
                        'koperasi sumber rejeki'
                    )
                    ||
                    str_contains(
                        $supplierName,
                        'gemilang mart'
                    )
                    ||
                    str_contains(
                        $supplierName,
                        'zenzi production'
                    )
                ) {

                    $rawMaterialTotal +=
                        (float)
                        $detail->subtotal;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | SEMUA BIAYA OPERASIONAL
            |--------------------------------------------------------------------------
            |
            | JANGAN FILTER BERDASARKAN NAMA.
            |
            | Semua InvoiceExpense masuk.
            |
            */

            foreach (
                $invoice->expenses
                as $expense
            ) {

                $amount =
                    (float)
                    $expense->amount;


                if ($amount > 0) {

                    $operationalTotal +=
                        $amount;
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | RETURN
        |--------------------------------------------------------------------------
        */

        return [

            'raw_material_total' =>
                $rawMaterialTotal,

            'operational_total' =>
                $operationalTotal,

            'invoice_count' =>
                $invoices->count(),

        ];
    }
}