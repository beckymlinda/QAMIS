<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationQuestionCategory extends Model
{
    protected $fillable = ['section', 'code', 'title', 'sort_order'];

    public function questions(): HasMany
    {
        return $this->hasMany(EvaluationQuestion::class)->orderBy('sequence_no');
    }
}
