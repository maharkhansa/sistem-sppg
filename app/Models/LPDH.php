<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LPDH extends Model
{
    use HasFactory;

    protected $table = 'lpdhs';

    protected $fillable = [
        'kitchen_id',
        'service_date',
        'day_status',
        'beneficiaries_count',

        'raw_material_expenses',
        'operational_expenses',

        'beneficiary_rent_incentive',
        'car_rental',
        'volunteer_salary',

        'incentive_calculated',
        'incentive_paid',
        'va_final_balance',
        'topup_proposal',
        'inspection_result',
        'file_name',

        'volunteer_count',
        'volunteer_salary_per_person',
    ];

    protected $casts = [
        'service_date' => 'date',

        'beneficiaries_count' => 'integer',

        'raw_material_expenses' => 'decimal:2',
        'operational_expenses' => 'decimal:2',

        'beneficiary_rent_incentive' => 'decimal:2',
        'car_rental' => 'decimal:2',
        'volunteer_salary' => 'decimal:2',

        'incentive_calculated' => 'decimal:2',
        'incentive_paid' => 'decimal:2',
        'va_final_balance' => 'decimal:2',
        'topup_proposal' => 'decimal:2',

        'volunteer_count' => 'integer',
'volunteer_salary_per_person' => 'decimal:2',
    ];

    public function kitchen(): BelongsTo
    {
        return $this->belongsTo(Kitchen::class);
    }
}