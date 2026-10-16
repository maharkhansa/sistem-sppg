<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChartOfAccount extends Model
{
    protected $table = 'chart_of_accounts';

    protected $fillable = [
        'code',
        'name',
        'type',
        'parent_id',
        'is_cash_bank',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'is_cash_bank' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            ChartOfAccount::class,
            'parent_id'
        );
    }

    public function children(): HasMany
    {
        return $this->hasMany(
            ChartOfAccount::class,
            'parent_id'
        );
    }

    public function journalEntryLines(): HasMany
    {
        return $this->hasMany(
            JournalEntryLine::class,
            'account_id'
        );
    }
}