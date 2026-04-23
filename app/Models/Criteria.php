<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Criteria extends Model
{
    protected $table = 'criteria';

    protected $fillable = [
        'code', 'name', 'type', 'importance', 'description', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function subCriteria(): HasMany
    {
        return $this->hasMany(SubCriteria::class, 'criteria_id');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class, 'criteria_id');
    }

    public function weights(): HasMany
    {
        return $this->hasMany(CriteriaWeight::class, 'criteria_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
