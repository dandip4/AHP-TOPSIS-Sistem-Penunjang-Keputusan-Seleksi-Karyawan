<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SelectionPeriod extends Model
{
    protected $fillable = [
        'name', 'position', 'start_date', 'end_date',
        'description', 'status', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
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
