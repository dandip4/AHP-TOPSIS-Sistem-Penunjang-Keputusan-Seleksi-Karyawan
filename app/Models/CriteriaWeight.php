<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CriteriaWeight extends Model
{
    protected $fillable = ['period_id', 'criteria_id', 'weight'];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:6',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(SelectionPeriod::class, 'period_id');
    }

    public function criteria(): BelongsTo
    {
        return $this->belongsTo(Criteria::class, 'criteria_id');
    }
}
