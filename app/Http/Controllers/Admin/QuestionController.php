<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Question::withCount('options');

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $questions = $query->orderBy('sort_order')->paginate(10)->withQueryString();

        return view('admin.questions.index', compact('questions'));
    }

    public function create(): View
    {
        $projectTypes = config('project_types.types');
        $questionTypes = config('question_types.types');
        $questions_max_order = (Question::max('sort_order') ?? 0) + 1;

        return view('admin.questions.create', compact('projectTypes', 'questionTypes', 'questions_max_order'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:'.implode(',', array_keys(config('question_types.types')))],
            'project_types' => ['nullable', 'array'],
            'project_types.*' => ['string'],
            'is_required' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
            'group_label' => ['nullable', 'string', 'max:255'],
            'placeholder' => ['nullable', 'string', 'max:255'],
            'help_text' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
            'options' => ['nullable', 'array'],
            'options.*.label' => ['required_with:options', 'string', 'max:255'],
            'options.*.value' => ['required_with:options', 'string', 'max:255'],
            'options.*.sort_order' => ['nullable', 'integer'],
        ]);

        $maxOrder = Question::max('sort_order') ?? 0;
        $validated['sort_order'] = $validated['sort_order'] ?? ($maxOrder + 1);
        $validated['is_required'] = $validated['is_required'] ?? false;
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['project_types'] = $validated['project_types'] ?? [];

        $options = $validated['options'] ?? [];
        unset($validated['options']);

        $question = Question::create($validated);

        foreach ($options as $index => $option) {
            $question->options()->create([
                'label' => $option['label'],
                'value' => $option['value'],
                'sort_order' => $option['sort_order'] ?? ($index + 1),
                'is_active' => true,
            ]);
        }

        return redirect()->route('admin.questions.index')->with('success', 'Question créée.');
    }

    public function edit(Question $question): View
    {
        $question->load('options', 'conditions');
        $projectTypes = config('project_types.types');
        $questionTypes = config('question_types.types');

        return view('admin.questions.edit', compact('question', 'projectTypes', 'questionTypes'));
    }

    public function update(Request $request, Question $question): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:'.implode(',', array_keys(config('question_types.types')))],
            'project_types' => ['nullable', 'array'],
            'project_types.*' => ['string'],
            'is_required' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
            'group_label' => ['nullable', 'string', 'max:255'],
            'placeholder' => ['nullable', 'string', 'max:255'],
            'help_text' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
            'options' => ['nullable', 'array'],
            'options.*.id' => ['nullable', 'integer'],
            'options.*.label' => ['required_with:options', 'string', 'max:255'],
            'options.*.value' => ['required_with:options', 'string', 'max:255'],
            'options.*.sort_order' => ['nullable', 'integer'],
            'options.*._destroy' => ['boolean'],
        ]);

        $validated['is_required'] = $validated['is_required'] ?? false;
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['project_types'] = $validated['project_types'] ?? [];

        $options = $validated['options'] ?? [];
        unset($validated['options']);

        $question->update($validated);

        if (! empty($options)) {
            $existingIds = collect($options)->pluck('id')->filter()->toArray();

            $question->options()->whereNotIn('id', $existingIds)->delete();

            foreach ($options as $index => $option) {
                if (isset($option['_destroy']) && $option['_destroy']) {
                    if (isset($option['id'])) {
                        QuestionOption::destroy($option['id']);
                    }

                    continue;
                }

                if (isset($option['id']) && $option['id']) {
                    QuestionOption::where('id', $option['id'])->update([
                        'label' => $option['label'],
                        'value' => $option['value'],
                        'sort_order' => $option['sort_order'] ?? ($index + 1),
                    ]);
                } else {
                    $question->options()->create([
                        'label' => $option['label'],
                        'value' => $option['value'],
                        'sort_order' => $option['sort_order'] ?? ($index + 1),
                        'is_active' => true,
                    ]);
                }
            }
        }

        return redirect()->route('admin.questions.index')->with('success', 'Question mise à jour.');
    }

    public function destroy(Question $question): RedirectResponse
    {
        $question->delete();

        return redirect()->route('admin.questions.index')->with('success', 'Question supprimée.');
    }

    public function toggleActive(Question $question): RedirectResponse
    {
        $question->update(['is_active' => ! $question->is_active]);
        $status = $question->is_active ? 'activée' : 'désactivée';

        return redirect()->route('admin.questions.index')->with('success', "Question {$status}.");
    }

    public function reorder(Request $request): RedirectResponse
    {
        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:questions,id'],
        ]);

        foreach ($request->order as $index => $questionId) {
            Question::where('id', $questionId)->update(['sort_order' => $index + 1]);
        }

        return redirect()->route('admin.questions.index')->with('success', 'Ordre mis à jour.');
    }
}
