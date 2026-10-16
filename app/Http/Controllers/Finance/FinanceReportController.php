<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\JournalEntryLine;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FinanceReportController extends Controller
{
    /**
     * Buku kas dan bank berdasarkan jurnal yang sudah diposting.
     */
    public function cashBank(Request $request)
    {
        $validated = $request->validate([
            'account_id' => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = $validated['start_date']
            ?? Carbon::today()->startOfMonth()->toDateString();

        $endDate = $validated['end_date']
            ?? Carbon::today()->toDateString();

        $accounts = ChartOfAccount::query()
            ->where('is_cash_bank', true)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $account = $accounts->firstWhere(
            'id',
            (int) ($validated['account_id'] ?? 0)
        );

        if (!$account) {
            $account = $accounts->first();
        }

        $openingBalance = 0;
        $transactions = collect();
        $runningBalance = 0;

        if ($account) {
            $openingBalance = (float) JournalEntryLine::query()
                ->where('account_id', $account->id)
                ->whereHas('journalEntry', function ($query) use ($startDate) {
                    $query->where('status', 'posted')
                        ->whereDate('entry_date', '<', $startDate);
                })
                ->selectRaw('COALESCE(SUM(debit - credit), 0) as balance')
                ->value('balance');

            $runningBalance = $openingBalance;

            $transactions = JournalEntryLine::query()
                ->with('journalEntry')
                ->where('account_id', $account->id)
                ->whereHas('journalEntry', function ($query) use ($startDate, $endDate) {
                    $query->where('status', 'posted')
                        ->whereBetween('entry_date', [$startDate, $endDate]);
                })
                ->get()
                ->sortBy(function ($line) {
                    return $line->journalEntry->entry_date->format('Y-m-d')
                        . '-'
                        . str_pad((string) $line->journalEntry->id, 12, '0', STR_PAD_LEFT)
                        . '-'
                        . str_pad((string) $line->id, 12, '0', STR_PAD_LEFT);
                })
                ->values()
                ->map(function ($line) use (&$runningBalance) {
                    $runningBalance += (float) $line->debit
                        - (float) $line->credit;

                    $line->running_balance = $runningBalance;

                    return $line;
                });
        }

        $totalDebit = (float) $transactions->sum('debit');
        $totalCredit = (float) $transactions->sum('credit');

        return view('finance.reports.cash-bank', compact(
            'accounts',
            'account',
            'startDate',
            'endDate',
            'openingBalance',
            'transactions',
            'runningBalance',
            'totalDebit',
            'totalCredit'
        ));
    }

    /**
     * Laporan laba rugi untuk rentang tanggal yang dipilih.
     */
    public function profitLoss(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = $validated['start_date']
            ?? Carbon::today()->startOfMonth()->toDateString();

        $endDate = $validated['end_date']
            ?? Carbon::today()->toDateString();

        $accounts = ChartOfAccount::query()
            ->whereIn('type', ['revenue', 'expense'])
            ->where('is_active', true)
            ->whereDoesntHave('children')
            ->orderBy('code')
            ->get();

        $revenues = collect();
        $expenses = collect();

        foreach ($accounts as $account) {
            $lines = $account->journalEntryLines()
                ->whereHas('journalEntry', function ($query) use ($startDate, $endDate) {
                    $query->where('status', 'posted')
                        ->whereBetween('entry_date', [$startDate, $endDate]);
                });

            $debit = (float) (clone $lines)->sum('debit');
            $credit = (float) (clone $lines)->sum('credit');

            $account->report_balance = $account->type === 'revenue'
                ? $credit - $debit
                : $debit - $credit;

            if ($account->type === 'revenue') {
                $revenues->push($account);
            } else {
                $expenses->push($account);
            }
        }

        $totalRevenue = (float) $revenues->sum('report_balance');
        $totalExpense = (float) $expenses->sum('report_balance');
        $netProfit = $totalRevenue - $totalExpense;

        return view('finance.reports.profit-loss', compact(
            'startDate',
            'endDate',
            'revenues',
            'expenses',
            'totalRevenue',
            'totalExpense',
            'netProfit'
        ));
    }

    /**
     * Neraca per tanggal berdasarkan saldo jurnal yang sudah diposting.
     */
    public function balanceSheet(Request $request)
    {
        $validated = $request->validate([
            'as_of' => ['nullable', 'date'],
        ]);

        $asOf = $validated['as_of']
            ?? Carbon::today()->toDateString();

        $accounts = ChartOfAccount::query()
            ->whereIn('type', ['asset', 'liability', 'equity'])
            ->where('is_active', true)
            ->whereDoesntHave('children')
            ->orderBy('code')
            ->get();

        $assets = collect();
        $liabilities = collect();
        $equity = collect();

        foreach ($accounts as $account) {
            $lines = $account->journalEntryLines()
                ->whereHas('journalEntry', function ($query) use ($asOf) {
                    $query->where('status', 'posted')
                        ->whereDate('entry_date', '<=', $asOf);
                });

            $debit = (float) (clone $lines)->sum('debit');
            $credit = (float) (clone $lines)->sum('credit');

            $account->report_balance = in_array(
                $account->type,
                ['asset'],
                true
            )
                ? $debit - $credit
                : $credit - $debit;

            if ($account->type === 'asset') {
                $assets->push($account);
            } elseif ($account->type === 'liability') {
                $liabilities->push($account);
            } else {
                $equity->push($account);
            }
        }

        /*
         * Hasil usaha kumulatif sampai tanggal neraca.
         * Nilai ini ditampilkan terpisah sebagai hasil usaha berjalan.
         */
        $incomeAccounts = ChartOfAccount::query()
            ->whereIn('type', ['revenue', 'expense'])
            ->where('is_active', true)
            ->whereDoesntHave('children')
            ->get();

        $currentEarnings = 0;

        foreach ($incomeAccounts as $incomeAccount) {
            $lines = $incomeAccount->journalEntryLines()
                ->whereHas('journalEntry', function ($query) use ($asOf) {
                    $query->where('status', 'posted')
                        ->whereDate('entry_date', '<=', $asOf);
                });

            $debit = (float) (clone $lines)->sum('debit');
            $credit = (float) (clone $lines)->sum('credit');

            if ($incomeAccount->type === 'revenue') {
                $currentEarnings += $credit - $debit;
            } else {
                $currentEarnings -= $debit - $credit;
            }
        }

        $totalAssets = (float) $assets->sum('report_balance');
        $totalLiabilities = (float) $liabilities->sum('report_balance');
        $totalEquityAccounts = (float) $equity->sum('report_balance');
        $totalEquity = $totalEquityAccounts + $currentEarnings;
        $totalLiabilitiesEquity = $totalLiabilities + $totalEquity;

        return view('finance.reports.balance-sheet', compact(
            'asOf',
            'assets',
            'liabilities',
            'equity',
            'currentEarnings',
            'totalAssets',
            'totalLiabilities',
            'totalEquityAccounts',
            'totalEquity',
            'totalLiabilitiesEquity'
        ));
    }
}