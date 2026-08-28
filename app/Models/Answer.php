<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Answer extends Model
{
    protected $fillable = [
        'project_id',
        'question_id',
        'text_value',
        'file_path',
        'other_value',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function answerOptions(): HasMany
    {
        return $this->hasMany(AnswerOption::class);
    }

    public function getDisplayValue(): ?string
    {
        $question = $this->question;

        if (! $question) {
            return $this->text_value;
        }

        if ($question->type === 'file') {
            return $this->file_path ? 'Fichier joint' : null;
        }

        if ($question->type === 'yes_no') {
            return match (strtolower((string) $this->text_value)) {
                'yes', 'oui' => 'Oui',
                'no', 'non' => 'Non',
                default => $this->text_value ?? '—',
            };
        }

        if (in_array($question->type, ['select', 'multiselect'])) {
            $optionLabels = $this->answerOptions
                ->map(fn (AnswerOption $ao) => $ao->questionOption?->label)
                ->filter()
                ->values();

            if ($optionLabels->isNotEmpty()) {
                $result = $optionLabels->implode(', ');
                if ($this->other_value) {
                    $result .= ', '.$this->other_value;
                }

                return $result;
            }
        }

        return $this->text_value;
    }
}
