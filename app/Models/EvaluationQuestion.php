<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationQuestion extends Model
{
    protected $fillable = [
        'evaluation_question_category_id', 'sequence_no',
        'question_text', 'question_type',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(EvaluationQuestionCategory::class, 'evaluation_question_category_id');
    }

    public function isLikert(): bool
    {
        return $this->question_type === 'likert_5';
    }
}
