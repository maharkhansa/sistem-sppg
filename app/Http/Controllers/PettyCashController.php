<?php

namespace App\Http\Controllers;

use App\Models\PettyCashTransaction;
use App\Models\PurchaseResponsible;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PettyCashController extends Controller
{
    
    public function index(Request $request)
    {
        $query = PurchaseResponsible::query();

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where('name', 'like', "%{$search}%");
        }

        $responsibles = $query
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        // Hitung saldo hanya untuk PJ yang tampil di halaman ini
        $responsibleIds = $responsibles->pluck('id');

        $balances = PettyCashTransaction::query()
            ->whereIn('purchase_responsible_id', $responsibleIds)
            ->selectRaw("
                purchase_responsible_id,
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
            ->groupBy('purchase_responsible_id')
            ->pluck('balance', 'purchase_responsible_id');

        return view(
            'petty-cash.index',
            compact('responsibles', 'balances')
        );
    }

    public function createTopUp()
    {
        $responsibles = PurchaseResponsible::where('is_active', true)
            ->orderBy('name')
            ->get();

        $accounts = ChartOfAccount::query()
            ->where('is_cash_bank', true)
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        // Hitung saldo kas kecil dan top up terakhir setiap PJ
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
                'last_top_up_amount' => $lastTopUp
                    ? (float) $lastTopUp->amount
                    : 0,
                'last_top_up_date' => $lastTopUp?->transaction_date,
            ];
        }

        return view(
            'petty-cash.top-up',
            compact(
                'responsibles',
                'accounts',
                'pettyCashBalances'
            )
        );
    }

    public function storeTopUp(Request $request)
    {
        $validated = $request->validate([
           'purchase_responsible_id' => [
                'required',
                Rule::exists('purchase_responsibles', 'id')
                    ->where('is_active', true),
            ],
            'transaction_date' => [
                'required',
                'date',
            ],
            'amount' => [
                'required',
                'numeric',
                'gt:0',
            ],
            'source_account_id' => [
                'required',
                'exists:chart_of_accounts,id',
            ],
            'reference_number' => [
                'nullable',
                'string',
                'max:100',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $accountIsValid = ChartOfAccount::query()
            ->where('id', $validated['source_account_id'])
            ->where('is_cash_bank', true)
            ->where('is_active', true)
            ->exists();

        if (! $accountIsValid) {
            return back()
                ->withInput()
                ->withErrors([
                    'source_account_id' =>
                        'Rekening sumber harus berupa akun kas/bank aktif.',
                ]);
        }

        DB::transaction(function () use ($validated) {
            PettyCashTransaction::create([
                'purchase_responsible_id' =>
                    $validated['purchase_responsible_id'],
                'type' => 'top_up',
                'transaction_date' =>
                    $validated['transaction_date'],
                'amount' => $validated['amount'],
                'source_account_id' =>
                    $validated['source_account_id'],
                'reference_number' =>
                    $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);
        });

        return redirect()
            ->route('petty-cash.index')
            ->with('success', 'Top up Sub Kas Operasional berhasil disimpan.');
    }
}