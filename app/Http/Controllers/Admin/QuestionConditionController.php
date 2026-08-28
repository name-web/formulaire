<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionCondition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QuestionConditionController extends Controller
{
    public function store(Request $request, Question $question): RedirectResponse
    {
        $validated = $request->validate([
            'depends_on_question_id' => ['required', 'exists:questions,id'],
            'operator' => ['required', 'in:equals,not_equals,contains,not_contains'],
            'option_value_id' => ['nullable', 'exists:question_options,id'],
            'text_value' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['sort_order'] = ($question->conditions()->max('sort_order') ?? 0) + 1;

        $question->conditions()->create($validated);

        return redirect()->route('admin.questions.edit', $question)->with('success', 'Condition ajoutée.');
    }

    public function update(Request $request, Question $question, QuestionCondition $condition): RedirectResponse
    {
        $validated = $request->validate([
            'depends_on_question_id' => ['required', 'exists:questions,id'],
            'operator' => ['required', 'in:equals,not_equals,contains,not_contains'],
            'option_value_id' => ['nullable', 'exists:question_options,id'],
            'text_value' => ['nullable', 'string', 'max:255'],
        ]);

        $condition->update($validated);

        return redirect()->route('admin.questions.edit', $question)->with('success', 'Condition mise à jour.');
    }

    public function destroy(Question $question, QuestionCondition $condition): RedirectResponse
    {
        $condition->delete();

        return redirect()->route('admin.questions.edit', $question)->with('success', 'Condition supprimée.');
    }
}
