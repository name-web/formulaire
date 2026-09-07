<?php

namespace App\Http\Controllers;

use App\Http\Requests\StartQuestionnaireRequest;
use App\Http\Requests\SubmitAnswerRequest;
use App\Models\PendingSubmission;
use App\Models\Question;
use App\Services\Geo\IpGeolocationService;
use App\Services\Questionnaire\FileService;
use App\Services\Questionnaire\PendingSubmissionService;
use App\Services\Questionnaire\QuestionnaireService;
use App\Services\Questionnaire\QuestionService;
use App\Services\Submission\SubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class QuestionnaireController extends Controller
{
    public function __construct(
        private QuestionnaireService $questionnaireService,
        private QuestionService $questionService,
        private PendingSubmissionService $pendingSubmissionService,
        private SubmissionService $submissionService,
        private FileService $fileService,
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

        return array_keys(config('project_types.types'));
    }

    public function start(StartQuestionnaireRequest $request, IpGeolocationService $geoService): RedirectResponse
    {
        $validated = $request->validated();
        $projectTypes = $validated['project_types'];

        $clientData = collect($validated)
            ->except(['project_types'])
            ->toArray();

        $clientData['country'] = $geoService->getCountry($request->ip());

        $pending = $this->pendingSubmissionService->createDraft(
            $clientData,
            $projectTypes
        );

        session([
            'pending_submission_uuid' => $pending->uuid,
            'project_type' => $pending->project_data['project_types'],
        ]);

        return redirect()->route('questionnaire.wizard');
    }

    public function wizard(): View
    {
        $pending = $this->resolvePendingSubmission();

        $visibleQuestions = $this->questionnaireService->getVisibleQuestionsForPending($pending);
        $answers = $this->pendingSubmissionService->getAnswersCollection($pending);

        $steps = $this->buildSteps($visibleQuestions);

        return view('questionnaire.wizard', [
            'pending' => $pending,
            'steps' => $steps,
            'answers' => $answers,
        ]);
    }

    private function buildSteps(Collection $questions): Collection
    {
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
        $pending = $this->resolvePendingSubmission();

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
                $filePath = $this->pendingSubmissionService->storeAnswerFile(
                    $pending,
                    $request->file("answers.{$questionId}.file"),
                    $questionId
                );
                $questionData['file_path'] = $filePath;
                unset($questionData['file']);
            }

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
                $this->pendingSubmissionService->saveAnswer($pending, $questionId, $questionData);
            }
        }

        return redirect()->route('questionnaire.summary');
    }

    public function show(int $step): View|RedirectResponse
    {
        $pending = $this->resolvePendingSubmission();

        $result = $this->resolveCurrentQuestionForPending($pending, $step);

        if ($result instanceof RedirectResponse) {
            return $result;
        }

        ['question' => $currentQuestion, 'step' => $resolvedStep, 'isEditing' => $isEditing] = $result;

        $existingAnswer = $this->pendingSubmissionService->getAnswer($pending, $currentQuestion->id);
        $progress = $this->questionnaireService->getProgressForPending($pending);

        return view('questionnaire.step', [
            'question' => $currentQuestion,
            'options' => $this->questionService->getOptions($currentQuestion->id),
            'answer' => $existingAnswer,
            'step' => $resolvedStep,
            'totalSteps' => $progress['total'],
            'progress' => $progress,
            'pending' => $pending,
            'visibleQuestions' => $this->questionnaireService->getVisibleQuestionsForPending($pending),
            'answeredIds' => $this->pendingSubmissionService->getAnsweredQuestionIds($pending),
            'isEditing' => $isEditing,
        ]);
    }

    public function store(SubmitAnswerRequest $request, int $step): RedirectResponse
    {
        $pending = $this->resolvePendingSubmission();

        $result = $this->resolveCurrentQuestionForPending($pending, $step);

        if ($result instanceof RedirectResponse) {
            return $result;
        }

        ['question' => $currentQuestion, 'step' => $resolvedStep, 'isEditing' => $isEditing] = $result;

        $data = $request->validated();

        if ($currentQuestion->type === 'file' && $request->hasFile('file')) {
            $filePath = $this->pendingSubmissionService->storeAnswerFile(
                $pending,
                $request->file('file'),
                $currentQuestion->id
            );
            $data['file_path'] = $filePath;
            unset($data['file']);
        }

        $this->pendingSubmissionService->saveAnswer($pending, $currentQuestion->id, $data);

        if ($isEditing) {
            return redirect()->route('questionnaire.summary');
        }

        $nextStep = $resolvedStep + 1;
        $visibleQuestions = $this->questionnaireService->getVisibleQuestionsForPending($pending);

        if ($nextStep > $visibleQuestions->count()) {
            return redirect()->route('questionnaire.summary');
        }

        return redirect()->route('questionnaire.step', $nextStep);
    }

    private function resolveCurrentQuestionForPending(PendingSubmission $pending, int $step): array|RedirectResponse
    {
        $visibleQuestions = $this->questionnaireService->getVisibleQuestionsForPending($pending);
        $answeredIds = $this->pendingSubmissionService->getAnsweredQuestionIds($pending);
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
        $pending = $this->resolvePendingSubmission();

        $previousQuestion = $this->questionnaireService->getPreviousQuestionForPending($pending);

        if (! $previousQuestion) {
            return redirect()->route('questionnaire.wizard');
        }

        $visibleQuestions = $this->questionnaireService->getVisibleQuestionsForPending($pending);
        $previousStep = ($visibleQuestions->search(fn ($q) => $q->id === $previousQuestion->id) ?? 0) + 1;

        return redirect()->route('questionnaire.step', $previousStep);
    }

    public function summary(): View
    {
        $pending = $this->resolvePendingSubmission();

        $summary = $this->questionnaireService->getSummaryForPending($pending);
        $files = $this->pendingSubmissionService->getFiles($pending);
        $progress = $this->questionnaireService->getProgressForPending($pending);

        return view('questionnaire.summary', [
            'summary' => $summary,
            'files' => $files,
            'progress' => $progress,
        ]);
    }

    public function submit(): RedirectResponse
    {
        $pending = $this->resolvePendingSubmission();

        if ($pending->status !== 'collecting') {
            return redirect()->route('questionnaire.thankyou');
        }

        $this->submissionService->submitPendingSubmission($pending);

        session()->forget(['pending_submission_uuid', 'project_type']);

        return redirect()->route('questionnaire.thankyou');
    }

    public function thankyou(): View
    {
        return view('questionnaire.thankyou');
    }

    public function storeFile(Request $request): RedirectResponse
    {
        $pending = $this->resolvePendingSubmission();

        $request->validate([
            'file' => ['required', 'file', 'max:5120', 'mimes:png,jpg,jpeg,gif,pdf,svg,doc,docx,xls,xlsx,zip'],
        ]);

        $this->pendingSubmissionService->storeFile($pending, $request->file('file'));

        return redirect()->route('questionnaire.summary');
    }

    public function destroyFile(int $fileIndex): RedirectResponse
    {
        $pending = $this->resolvePendingSubmission();

        $this->pendingSubmissionService->deleteFile($pending, $fileIndex);

        return redirect()->route('questionnaire.summary');
    }

    private function resolvePendingSubmission(): PendingSubmission
    {
        $uuid = session('pending_submission_uuid');
        abort_unless($uuid, 404);

        $pending = $this->pendingSubmissionService->find($uuid);
        abort_unless($pending, 404);

        return $pending;
    }
}
