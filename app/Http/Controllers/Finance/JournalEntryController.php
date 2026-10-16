<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JournalEntryController extends Controller
{
    public function index(Request $request)
    {
        $query = JournalEntry::query()
            ->with('creator')
            ->withSum('lines as total_debit', 'debit')
            ->withSum('lines as total_credit', 'credit')
            ->orderByDesc('entry_date')
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('entry_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('entry_date', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($builder) use ($search) {
                $builder->where('entry_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $journals = $query->paginate(15)->withQueryString();

        return view('finance.journals.index', compact('journals'));
    }

    public function create()
    {
        $accounts = ChartOfAccount::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('finance.journals.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'entry_date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],

            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account_id' => [
                'required',
                'integer',
                'distinct',
                'exists:chart_of_accounts,id',
            ],
            'lines.*.description' => ['nullable', 'string', 'max:255'],
            'lines.*.debit' => [
                'required',
                'numeric',
                'min:0',
                'decimal:0,2',
            ],
            'lines.*.credit' => [
                'required',
                'numeric',
                'min:0',
                'decimal:0,2',
            ],
        ]);

        $accounts = ChartOfAccount::query()
            ->whereIn(
                'id',
                collect($validated['lines'])
                    ->pluck('account_id')
                    ->unique()
            )
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        if ($accounts->count() !== collect($validated['lines'])
            ->pluck('account_id')
            ->unique()
            ->count()) {
            throw ValidationException::withMessages([
                'lines' => 'Semua akun jurnal harus aktif.',
            ]);
        }

        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($validated['lines'] as $index => $line) {
            $debit = (float) $line['debit'];
            $credit = (float) $line['credit'];

            if ($debit > 0 && $credit > 0) {
                throw ValidationException::withMessages([
                    "lines.{$index}.debit" =>
                        'Satu baris hanya boleh memiliki debit atau kredit.',
                ]);
            }

            if ($debit <= 0 && $credit <= 0) {
                throw ValidationException::withMessages([
                    "lines.{$index}.debit" =>
                        'Isi nilai debit atau kredit pada setiap baris.',
                ]);
            }

            $totalDebit += $debit;
            $totalCredit += $credit;
        }

        if ($totalDebit <= 0 || $totalCredit <= 0) {
            throw ValidationException::withMessages([
                'lines' => 'Jurnal harus memiliki nilai debit dan kredit.',
            ]);
        }

        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            throw ValidationException::withMessages([
                'lines' => 'Total debit dan kredit harus sama.',
            ]);
        }

        $journal = DB::transaction(function () use ($validated) {
            $date = str_replace('-', '', $validated['entry_date']);
            $prefix = 'JU-' . $date . '-';

            $lastNumber = JournalEntry::query()
                ->where('entry_number', 'like', $prefix . '%')
                ->lockForUpdate()
                ->get(['entry_number'])
                ->map(function ($entry) use ($prefix) {
                    return (int) substr(
                        $entry->entry_number,
                        strlen($prefix)
                    );
                })
                ->max() ?? 0;

            $entryNumber = $prefix . str_pad(
                (string) ($lastNumber + 1),
                3,
                '0',
                STR_PAD_LEFT
            );

            $entry = JournalEntry::create([
                'entry_number' => $entryNumber,
                'entry_date' => $validated['entry_date'],
                'description' => $validated['description'],
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            foreach ($validated['lines'] as $line) {
                $entry->lines()->create([
                    'account_id' => $line['account_id'],
                    'description' => $line['description'] ?? null,
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                ]);
            }

            return $entry;
        });

        return redirect()
            ->route('finance.journals.show', $journal)
            ->with('success', 'Jurnal berhasil disimpan sebagai draft.');
    }

    public function show(JournalEntry $journalEntry)
    {
        $journalEntry->load([
            'lines.account',
            'creator',
        ]);

        return view('finance.journals.show', [
            'journal' => $journalEntry,
        ]);
    }

    public function post(JournalEntry $journalEntry)
    {
        DB::transaction(function () use ($journalEntry) {
            $journal = JournalEntry::query()
                ->whereKey($journalEntry->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($journal->status !== 'draft') {
                throw ValidationException::withMessages([
                    'status' => 'Hanya jurnal draft yang dapat diposting.',
                ]);
            }

            $lines = $journal->lines()->get();

            if ($lines->count() < 2) {
                throw ValidationException::withMessages([
                    'lines' => 'Jurnal harus memiliki minimal dua baris.',
                ]);
            }

            $totalDebit = 0;
            $totalCredit = 0;

            foreach ($lines as $line) {
                $debit = (float) $line->debit;
                $credit = (float) $line->credit;

                if (
                    ($debit > 0 && $credit > 0) ||
                    ($debit <= 0 && $credit <= 0)
                ) {
                    throw ValidationException::withMessages([
                        'lines' => 'Periksa kembali debit dan kredit setiap baris.',
                    ]);
                }

                $totalDebit += $debit;
                $totalCredit += $credit;
            }

            if (
                $totalDebit <= 0 ||
                $totalCredit <= 0 ||
                round($totalDebit, 2) !== round($totalCredit, 2)
            ) {
                throw ValidationException::withMessages([
                    'lines' => 'Jurnal tidak seimbang dan tidak dapat diposting.',
                ]);
            }

            $journal->update([
                'status' => 'posted',
                'posted_at' => now(),
            ]);
        });

        return redirect()
            ->route('finance.journals.show', $journalEntry)
            ->with('success', 'Jurnal berhasil diposting.');
    }
}
