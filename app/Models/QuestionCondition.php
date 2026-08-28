<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionCondition extends Model
{
    protected $fillable = [
        'question_id',
        'depends_on_question_id',
        'operator',
        'option_value_id',
        'text_value',
        'sort_order',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function dependsOnQuestion(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'depends_on_question_id');
    }

    public function optionValue(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class, 'option_value_id');
    }
}
