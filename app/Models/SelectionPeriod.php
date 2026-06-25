<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SelectionPeriod extends Model
{
    protected $fillable = [
        'name', 'position', 'start_date', 'end_date',
        'description', 'status', 'created_by',
        'aggregation_method', 'owa_alpha', 'aggregation_computed_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'aggregation_computed_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function applicants(): HasMany
    {
        return $this->hasMany(Applicant::class, 'period_id');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class, 'period_id');
    }

    public function aggregatedEvaluations(): HasMany
    {
        return $this->hasMany(AggregatedEvaluation::class, 'period_id');
    }

    public function pairwiseComparisons(): HasMany
    {
        return $this->hasMany(PairwiseComparison::class, 'period_id');
    }

    public function criteriaWeights(): HasMany
    {
        return $this->hasMany(CriteriaWeight::class, 'period_id');
    }

    public function selectionResults(): HasMany
    {
        return $this->hasMany(SelectionResult::class, 'period_id');
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'period_id');
    }

    /**
     * Kriteria aktif untuk periode ini (urutan mempengaruhi tampilan formulir & bobot).
     */
    public function linkedCriteria(): BelongsToMany
    {
        return $this->belongsToMany(Criteria::class, 'selection_period_criteria', 'selection_period_id', 'criteria_id')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order')
            ->orderBy('criteria.code');
    }

    /**
     * Evaluator yang berpartisipasi dalam periode ini.
     */
    public function evaluators(): BelongsToMany
    {
        return $this->belongsToMany(Evaluator::class, 'selection_period_evaluators', 'selection_period_id', 'evaluator_id')
            ->withTimestamps()
            ->orderBy('evaluators.sort_order');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft' => '<span class="badge bg-secondary">Draft</span>',
            'open' => '<span class="badge bg-success">Dibuka</span>',
            'closed' => '<span class="badge bg-warning">Ditutup</span>',
            'completed' => '<span class="badge bg-primary">Selesai</span>',
            default => '<span class="badge bg-light">-</span>',
        };
    }
}
