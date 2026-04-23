<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Applicant extends Model
{
    protected $fillable = [
        'period_id', 'name', 'email', 'phone', 'gender',
        'birth_date', 'education', 'major', 'gpa', 'age',
        'address', 'photo', 'cv',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'gpa' => 'decimal:2',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(SelectionPeriod::class, 'period_id');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class, 'applicant_id');
    }

    public function result(): HasOne
    {
        return $this->hasOne(SelectionResult::class, 'applicant_id');
    }

    public function getGenderLabelAttribute(): string
    {
        return $this->gender === 'L' ? 'Laki-laki' : 'Perempuan';
    }
}
