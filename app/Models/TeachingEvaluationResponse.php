<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeachingEvaluationResponse extends Model
{
    protected $fillable = [
        'teaching_evaluation_id', 'evaluation_question_id',
        'rating', 'response_text',
    ];

    public function teachingEvaluation(): BelongsTo
    {
        return $this->belongsTo(TeachingEvaluation::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(EvaluationQuestion::class, 'evaluation_question_id');
    }
}
