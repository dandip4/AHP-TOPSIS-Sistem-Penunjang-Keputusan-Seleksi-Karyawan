<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluation extends Model
{
    protected $fillable = ['period_id', 'applicant_id', 'criteria_id', 'score'];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:4',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(SelectionPeriod::class, 'period_id');
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class, 'applicant_id');
    }

    public function criteria(): BelongsTo
    {
        return $this->belongsTo(Criteria::class, 'criteria_id');
    }
}
