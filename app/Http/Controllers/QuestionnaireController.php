<?php

namespace App\Http\Controllers;

use App\Http\Requests\StartQuestionnaireRequest;
use App\Http\Requests\SubmitAnswerRequest;
use App\Models\Answer;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\Question;
use App\Services\Questionnaire\AnswerService;
use App\Services\Questionnaire\FileService;
use App\Services\Questionnaire\ProjectService;
use App\Services\Questionnaire\QuestionnaireService;
use App\Services\Questionnaire\QuestionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QuestionnaireController extends Controller
{
    public function __construct(
        private QuestionnaireService $questionnaireService,
        private QuestionService $questionService,
        private AnswerService $answerService,
        private FileService $fileService,
        private ProjectService $projectService,
    ) {}

    public function index(): View
    {
        $types = config('project_types.types');

        return view('questionnaire.index', [
            'projectTypes' => $types,
            'selectedProjectTypes' => $this->resolveSelectedProjectTypes(old('project_types')),
        ]);
    }

    private function resolveSelectedProjectTypes(mixed $old): array
    {
        if (is_string($old)) {
            $decoded = json_decode($old, true);

            return is_array($decoded) ? $decoded : [];
        }

        if (is_array($old)) {
            return $old;
        }

        // Le home ne collecte que les coordonnées du client : on présélectionne
        // tous les types réels, mais jamais « autre » (aucun champ de précision ici).
        return array_values(array_diff(array_keys(config('project_types.types')), ['autre']));
    }

    public function start(StartQuestionnaireRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $projectTypes = $validated['project_types'];

        $clientData = collect($validated)
            ->except(['project_types', 'other_description'])
            ->toArray();

        $project = $this->questionnaireService->startSession(
            $clientData,
            $projectTypes
        );

        if (in_array('autre', $projectTypes) && ! empty($validated['other_description'])) {
            $project->update(['description' => $validated['other_description']]);
        }

        // Q7 is answered in the wizard, not pre-saved here

        session([
            'client_id' => $project->client_id,
            'project_id' => $project->id,
            'project_type' => $project->project_type,
        ]);

        return redirect()->route('questionnaire.wizard');
    }

    public function wizard(): View
    {
        $projectId = session('project_id');
        $projectType = session('project_type');
        abort_unless($projectId && $projectType, 404);

        $project = $this->projectService->find($projectId);
        abort_unless($project, 404);

        $visibleQuestions = $this->questionnaireService->getVisibleQuestions($projectId, $project->project_type ?? $projectType);
        $answeredAnswers = Answer::where('project_id', $projectId)
            ->with('answerOptions')
            ->get()
            ->keyBy('question_id');

        $steps = $this->buildSteps($visibleQuestions);

        return view('questionnaire.wizard', [
            'project' => $project,
            'steps' => $steps,
            'answers' => $answeredAnswers,
        ]);
    }

    private function buildSteps(Collection $questions): Collection
    {
        // Each question becomes its own wizard step (one question per page)
        return $questions
            ->sortBy('sort_order')
            ->values()
            ->map(fn (Question $q) => [
                'group_label' => $q->group_label,
                'question' => $q,
                'types' => $q->project_types ?? ['all'],
            ])
            ->values();
    }

    public function submitWizard(Request $request): RedirectResponse
    {
        $projectId = session('project_id');
        $projectType = session('project_type');
        abort_unless($projectId && $projectType, 404);

        $answers = collect($request->input('answers', []))
            ->map(function ($data) {
                if (isset($data['option_ids']) && is_array($data['option_ids'])) {
                    $data['option_ids'] = array_values(array_filter(
                        $data['option_ids'],
                        fn ($id) => $id !== null && $id !== ''
                    ));
                }

                return $data;
            })
            ->all();

        $request->merge(['answers' => $answers]);

        $request->validate([
            'answers' => ['nullable', 'array'],
            'answers.*' => ['array'],
            'answers.*.value' => ['nullable', 'string', 'max:5000'],
            'answers.*.other_value' => ['nullable', 'string', 'max:500'],
            'answers.*.option_ids' => ['nullable', 'array'],
            'answers.*.option_ids.*' => ['integer'],
        ]);

        DB::transaction(function () use ($request, $projectId, $answers) {
            foreach ($answers as $questionId => $data) {
                $questionId = (int) $questionId;
                $question = Question::find($questionId);
                if (! $question) {
                    continue;
                }

                $questionData = $data;

                if ($question->type === 'file' && $request->hasFile("answers.{$questionId}.file")) {
                    $request->validate([
                        "answers.{$questionId}.file" => ['required', 'file', 'max:5120', 'mimes:png,jpg,jpeg,gif,pdf,svg,doc,docx,xls,xlsx,zip'],
                    ]);
                    $filePath = $this->fileService->storeAnswerFile(
                        $request->file("answers.{$questionId}.file"),
                        $projectId
                    );
                    $questionData['file_path'] = $filePath;
                    unset($questionData['file']);
                }

                // Only keep option ids that actually belong to this question
                if (isset($questionData['option_ids']) && is_array($questionData['option_ids'])) {
                    $validOptionIds = $question->options()
                        ->whereIn('id', $questionData['option_ids'])
                        ->pluck('id')
                        ->all();
                    $questionData['option_ids'] = $validOptionIds;
                }

                $hasContent = false;
                if ($question->type === 'file') {
                    $hasContent = ! empty($questionData['file_path']);
                } elseif ($question->type === 'multiselect' || $question->type === 'select') {
                    $optionIds = $questionData['option_ids'] ?? [];
                    $hasContent = ! empty($optionIds);
                } else {
                    $hasContent = ! empty($questionData['value']);
                }

                if ($hasContent) {
                    $this->answerService->save($projectId, $questionId, $questionData);
                    $this->syncProjectTypeFromAnswer($projectId, $question);
                }
            }
        });

        return redirect()->route('questionnaire.summary');
    }

    public function show(int $step): View|RedirectResponse
    {
        $projectId = session('project_id');
        $projectType = session('project_type');

        abort_unless($projectId && $projectType, 404);

        $project = $this->projectService->find($projectId);
        abort_unless($project, 404);

        $result = $this->resolveCurrentQuestion($projectId, $project, $step);

        if ($result instanceof RedirectResponse) {
            return $result;
        }

        ['question' => $currentQuestion, 'step' => $resolvedStep, 'isEditing' => $isEditing] = $result;

        $existingAnswer = $this->answerService->getForQuestion($projectId, $currentQuestion->id);
        $progress = $this->questionnaireService->getProgress($projectId, $project->project_type ?? $projectType);

        return view('questionnaire.step', [
            'question' => $currentQuestion,
            'options' => $this->questionService->getOptions($currentQuestion->id),
            'answer' => $existingAnswer,
            'step' => $resolvedStep,
            'totalSteps' => $progress['total'],
            'progress' => $progress,
            'project' => $project,
            'visibleQuestions' => $this->questionnaireService->getVisibleQuestions($projectId, $project->project_type ?? $projectType),
            'answeredIds' => $this->answerService->getAnsweredQuestionIds($projectId),
            'isEditing' => $isEditing,
        ]);
    }

    public function store(SubmitAnswerRequest $request, int $step): RedirectResponse
    {
        $projectId = session('project_id');
        $projectType = session('project_type');

        abort_unless($projectId && $projectType, 404);

        $project = $this->projectService->find($projectId);
        $effectiveType = $project->project_type ?? $projectType;

        $result = $this->resolveCurrentQuestion($projectId, $project, $step);

        if ($result instanceof RedirectResponse) {
            return $result;
        }

        ['question' => $currentQuestion, 'step' => $resolvedStep, 'isEditing' => $isEditing] = $result;

        $data = $request->validated();

        if ($currentQuestion->type === 'file' && $request->hasFile('file')) {
            $filePath = $this->fileService->storeAnswerFile($request->file('file'), $projectId);
            $data['file_path'] = $filePath;
            unset($data['file']);
        }

        $this->answerService->save($projectId, $currentQuestion->id, $data);

        $this->syncProjectTypeFromAnswer($projectId, $currentQuestion);

        if ($isEditing) {
            return redirect()->route('questionnaire.summary');
        }

        $nextStep = $resolvedStep + 1;
        $visibleQuestions = $this->questionnaireService->getVisibleQuestions($projectId, $effectiveType);

        if ($nextStep > $visibleQuestions->count()) {
            return redirect()->route('questionnaire.summary');
        }

        return redirect()->route('questionnaire.step', $nextStep);
    }

    private function resolveCurrentQuestion(int $projectId, Project $project, int $step): array|RedirectResponse
    {
        $effectiveType = $project->project_type ?? session('project_type');
        $visibleQuestions = $this->questionnaireService->getVisibleQuestions($projectId, $effectiveType);
        $answeredIds = $this->answerService->getAnsweredQuestionIds($projectId);
        $isEditing = request()->query('edit') === '1';

        if ($isEditing) {
            $currentIndex = $step - 1;
            $currentQuestion = $visibleQuestions->get($currentIndex);
            abort_unless($currentQuestion, 404);

            return ['question' => $currentQuestion, 'step' => $step, 'isEditing' => true];
        }

        $unansweredIndex = $step - 1;
        while ($unansweredIndex < $visibleQuestions->count()) {
            $candidate = $visibleQuestions->get($unansweredIndex);
            if ($candidate && ! in_array($candidate->id, $answeredIds)) {
                break;
            }
            $unansweredIndex++;
        }

        if ($unansweredIndex >= $visibleQuestions->count()) {
            return redirect()->route('questionnaire.summary');
        }

        $currentQuestion = $visibleQuestions->get($unansweredIndex);
        abort_unless($currentQuestion, 404);

        return ['question' => $currentQuestion, 'step' => $unansweredIndex + 1, 'isEditing' => false];
    }

    public function previous(): RedirectResponse
    {
        $projectId = session('project_id');
        $projectType = session('project_type');

        abort_unless($projectId && $projectType, 404);

        $project = $this->projectService->find($projectId);
        $effectiveType = $project->project_type ?? $projectType;

        $previousQuestion = $this->questionnaireService->getPreviousQuestion($projectId, $effectiveType);

        if (! $previousQuestion) {
            return redirect()->route('questionnaire.wizard');
        }

        $visibleQuestions = $this->questionnaireService->getVisibleQuestions($projectId, $effectiveType);
        $previousStep = ($visibleQuestions->search(fn ($q) => $q->id === $previousQuestion->id) ?? 0) + 1;

        return redirect()->route('questionnaire.step', $previousStep);
    }

    public function summary(): View
    {
        $projectId = session('project_id');
        $projectType = session('project_type');

        abort_unless($projectId && $projectType, 404);

        $summary = $this->questionnaireService->getSummary($projectId);
        $files = $this->fileService->getForProject($projectId);
        $project = $summary['project'] ?? $this->projectService->find($projectId);
        $progress = $this->questionnaireService->getProgress($projectId, $project->project_type ?? $projectType);

        return view('questionnaire.summary', [
            'summary' => $summary,
            'files' => $files,
            'progress' => $progress,
        ]);
    }

    public function submit(): RedirectResponse
    {
        $projectId = session('project_id');

        abort_unless($projectId, 404);

        if ($this->projectService->isSubmitted($projectId)) {
            return redirect()->route('questionnaire.thankyou');
        }

        $this->questionnaireService->submitProject($projectId);

        session()->forget(['client_id', 'project_id', 'project_type']);

        return redirect()->route('questionnaire.thankyou');
    }

    public function thankyou(): View
    {
        return view('questionnaire.thankyou');
    }

    public function storeFile(Request $request): RedirectResponse
    {
        $projectId = session('project_id');

        abort_unless($projectId, 404);

        $request->validate([
            'file' => ['required', 'file', 'max:5120', 'mimes:png,jpg,jpeg,gif,pdf,svg,doc,docx,xls,xlsx,zip'],
        ]);

        $this->fileService->storeUploadedFile($request->file('file'), $projectId);

        return redirect()->route('questionnaire.summary');
    }

    public function destroyFile(int $fileId): RedirectResponse
    {
        $projectId = session('project_id');

        abort_unless($projectId, 404);

        $file = ProjectFile::find($fileId);
        abort_unless($file && $file->project_id === $projectId, 404);

        $this->fileService->delete($fileId);

        return redirect()->route('questionnaire.summary');
    }

    private function syncProjectTypeFromAnswer(int $projectId, Question $question): void
    {
        $projectTypeKeys = array_keys(config('project_types.types'));

        $optionValues = $question->options()->pluck('value')->toArray();

        if (empty(array_intersect($optionValues, $projectTypeKeys))) {
            return;
        }

        $answer = Answer::where('project_id', $projectId)
            ->where('question_id', $question->id)
            ->first();

        if (! $answer) {
            return;
        }

        $selectedValues = $answer->answerOptions
            ->map(fn ($ao) => $ao->questionOption?->value)
            ->filter()
            ->toArray();

        $projectTypes = array_values(array_intersect($selectedValues, $projectTypeKeys));

        if (! empty($projectTypes)) {
            Project::where('id', $projectId)->update(['project_type' => $projectTypes]);
            session(['project_type' => $projectTypes]);
        }
    }
}
