<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evaluator extends Model
{
    protected $fillable = [
        'code',
        'name',
        'role_label',
        'user_id',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class, 'evaluator_id');
    }

    /**
     * Periode-periode yang evaluator ini ikuti.
     */
    public function periods(): BelongsToMany
    {
        return $this->belongsToMany(SelectionPeriod::class, 'selection_period_evaluators', 'evaluator_id', 'selection_period_id')
            ->withTimestamps()
            ->orderBy('selection_periods.name');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
