<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SelectionResult extends Model
{
    protected $fillable = [
        'period_id', 'applicant_id', 'preference_value',
        'positive_distance', 'negative_distance', 'rank', 'status',
    ];

    protected function casts(): array
    {
        return [
            'preference_value' => 'decimal:6',
            'positive_distance' => 'decimal:6',
            'negative_distance' => 'decimal:6',
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

    public function getStatusBadgeAttribute(): string
    {
        return $this->status === 'lulus'
            ? '<span class="badge bg-success">Lulus</span>'
            : '<span class="badge bg-danger">Tidak Lulus</span>';
    }
}
