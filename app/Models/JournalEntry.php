<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    protected $table = 'journal_entries';

    protected $fillable = [
        'entry_number',
        'entry_date',
        'description',
        'source_type',
        'source_id',
        'status',
        'posted_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'posted_at' => 'datetime',
            'source_id' => 'integer',
            'created_by' => 'integer',
        ];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(
            JournalEntryLine::class,
            'journal_entry_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function getTotalDebitAttribute(): float
    {
        return (float) $this->lines()->sum('debit');
    }

    public function getTotalCreditAttribute(): float
    {
        return (float) $this->lines()->sum('credit');
    }

    public function isBalanced(): bool
    {
        return round($this->total_debit, 2)
            === round($this->total_credit, 2);
    }
}
