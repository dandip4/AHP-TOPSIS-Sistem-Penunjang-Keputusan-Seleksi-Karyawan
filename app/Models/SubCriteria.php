<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubCriteria extends Model
{
    protected $table = 'sub_criteria';

    protected $fillable = ['criteria_id', 'name', 'value', 'description'];

    public function criteria(): BelongsTo
    {
        return $this->belongsTo(Criteria::class, 'criteria_id');
    }
}
