<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PairwiseComparison extends Model
{
    protected $fillable = ['period_id', 'criteria_row_id', 'criteria_col_id', 'value'];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:4',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(SelectionPeriod::class, 'period_id');
    }

    public function criteriaRow(): BelongsTo
    {
        return $this->belongsTo(Criteria::class, 'criteria_row_id');
    }

    public function criteriaCol(): BelongsTo
    {
        return $this->belongsTo(Criteria::class, 'criteria_col_id');
    }
}
