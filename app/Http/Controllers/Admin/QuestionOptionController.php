<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QuestionOptionController extends Controller
{
    public function store(Request $request, Question $question): RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'value' => ['required', 'string', 'max:255'],
        ]);

        $maxOrder = $question->options()->max('sort_order') ?? 0;

        $question->options()->create([
            'label' => $validated['label'],
            'value' => $validated['value'],
            'sort_order' => $maxOrder + 1,
            'is_active' => true,
        ]);

        return redirect()->route('admin.questions.edit', $question)->with('success', 'Option ajoutée.');
    }

    public function update(Request $request, Question $question, QuestionOption $option): RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'value' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $option->update($validated);

        return redirect()->route('admin.questions.edit', $question)->with('success', 'Option mise à jour.');
    }

    public function destroy(Question $question, QuestionOption $option): RedirectResponse
    {
        abort_unless($option->question_id === $question->id, 404);

        $option->delete();

        return redirect()->route('admin.questions.edit', $question)->with('success', 'Option supprimée.');
    }
}
