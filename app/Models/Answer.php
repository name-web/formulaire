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

        return match ($question->type) {
            'file' => $this->file_path ? $this->file_path : null,
            'yes_no' => match (strtolower((string) $this->text_value)) {
                'yes', 'oui' => 'Oui',
                'no', 'non' => 'Non',
                default => $this->text_value ?? null,
            },
            'select', 'multiselect' => $this->getSelectDisplayValue(),
            'number' => $this->text_value !== null ? number_format((float) $this->text_value, 0, ',', ' ') : null,
            default => $this->text_value,
        };
    }

    public function getSelectDisplayValue(): ?string
    {
        $optionLabels = $this->answerOptions
            ->map(fn (AnswerOption $ao) => $ao->questionOption?->label)
            ->filter()
            ->values();

        if ($optionLabels->isEmpty()) {
            return $this->other_value ?: null;
        }

        $result = $optionLabels->implode(', ');

        if ($this->other_value) {
            $result .= ', '.$this->other_value;
        }

        return $result;
    }

    public function getSelectValues(): array
    {
        $labels = $this->answerOptions
            ->map(fn (AnswerOption $ao) => $ao->questionOption?->label)
            ->filter()
            ->values()
            ->all();

        if ($this->other_value) {
            $labels[] = $this->other_value;
        }

        return $labels;
    }
}
