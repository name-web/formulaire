<?php

namespace App\Http\Controllers;

use App\Http\Requests\StartQuestionnaireRequest;
use App\Http\Requests\SubmitAnswerRequest;
use App\Models\Answer;
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
        return view('questionnaire.index', [
            'projectTypes' => config('project_types.types'),
        ]);
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

        $qType = Question::where('title', 'Quelle solution souhaitez-vous créer ?')->first();
        if ($qType && ! empty($projectTypes)) {
            $optionIds = $qType->options()
                ->whereIn('value', $projectTypes)
                ->pluck('id')
                ->toArray();
            if (! empty($optionIds)) {
                $this->answerService->save($project->id, $qType->id, [
                    'option_ids' => $optionIds,
                ]);
            }
        }

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

        $visibleQuestions = $this->questionnaireService->getVisibleQuestions($projectId, $projectType);
        $answeredAnswers = Answer::where('project_id', $projectId)
            ->with('answerOptions')
            ->get()
            ->keyBy('question_id');

        $steps = $this->buildSteps($visibleQuestions, $projectType);

        return view('questionnaire.wizard', [
            'project' => $project,
            'steps' => $steps,
            'answers' => $answeredAnswers,
        ]);
    }

    private function buildSteps(Collection $questions, string|array $projectType): Collection
    {
        // Each question becomes its own wizard step (one question per page)
        // Skip Q7 "Quelle solution" — already answered by the start form
        $qType = Question::where('title', 'Quelle solution souhaitez-vous créer ?')->first();

        return $questions
            ->filter(fn (Question $q) => $qType && $q->id !== $qType->id)
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

        $answers = $request->input('answers', []);

        DB::transaction(function () use ($projectId, $answers) {
            foreach ($answers as $questionId => $data) {
                $questionId = (int) $questionId;
                $question = Question::find($questionId);
                if (! $question) {
                    continue;
                }

                $questionData = $data;

                if ($question->type === 'file' && $request->hasFile("answers.{$questionId}.file")) {
                    $filePath = $this->fileService->storeAnswerFile(
                        $request->file("answers.{$questionId}.file"),
                        $projectId
                    );
                    $questionData['file_path'] = $filePath;
                    unset($questionData['file']);
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

        $visibleQuestions = $this->questionnaireService->getVisibleQuestions($projectId, $projectType);
        $answeredIds = $this->answerService->getAnsweredQuestionIds($projectId);
        $isEditing = request()->query('edit') === '1';

        if ($isEditing) {
            // Edit mode: show the question at this exact position (no skip)
            $currentIndex = $step - 1;
            $currentQuestion = $visibleQuestions->get($currentIndex);
            abort_unless($currentQuestion, 404);
        } else {
            // Normal flow: skip answered questions
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
            $step = $unansweredIndex + 1;
        }

        $existingAnswer = $this->answerService->getForQuestion($projectId, $currentQuestion->id);
        $progress = $this->questionnaireService->getProgress($projectId, $projectType);

        return view('questionnaire.step', [
            'question' => $currentQuestion,
            'options' => $this->questionService->getOptions($currentQuestion->id),
            'answer' => $existingAnswer,
            'step' => $step,
            'totalSteps' => $progress['total'],
            'progress' => $progress,
            'project' => $project,
            'visibleQuestions' => $visibleQuestions,
            'answeredIds' => $answeredIds,
            'isEditing' => $isEditing,
        ]);
    }

    public function store(SubmitAnswerRequest $request, int $step): RedirectResponse
    {
        $projectId = session('project_id');
        $projectType = session('project_type');

        abort_unless($projectId && $projectType, 404);

        $visibleQuestions = $this->questionnaireService->getVisibleQuestions($projectId, $projectType);
        $answeredIds = $this->answerService->getAnsweredQuestionIds($projectId);
        $isEditing = request()->query('edit') === '1';

        if ($isEditing) {
            $currentIndex = $step - 1;
            $currentQuestion = $visibleQuestions->get($currentIndex);
            abort_unless($currentQuestion, 404);
        } else {
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
            $step = $unansweredIndex + 1;
        }

        $data = $request->validated();

        if ($currentQuestion->type === 'file' && $request->hasFile('file')) {
            $filePath = $this->fileService->storeAnswerFile($request->file('file'), $projectId);
            $data['file_path'] = $filePath;
            unset($data['file']);
        }

        $this->answerService->save($projectId, $currentQuestion->id, $data);

        if ($isEditing) {
            return redirect()->route('questionnaire.summary');
        }

        $nextStep = $step + 1;

        if ($nextStep > $visibleQuestions->count()) {
            return redirect()->route('questionnaire.summary');
        }

        return redirect()->route('questionnaire.step', $nextStep);
    }

    public function previous(): RedirectResponse
    {
        $projectId = session('project_id');
        $projectType = session('project_type');

        abort_unless($projectId && $projectType, 404);

        $previousQuestion = $this->questionnaireService->getPreviousQuestion($projectId, $projectType);

        if (! $previousQuestion) {
            return redirect()->route('questionnaire.wizard');
        }

        $visibleQuestions = $this->questionnaireService->getVisibleQuestions($projectId, $projectType);
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
        $progress = $this->questionnaireService->getProgress($projectId, $projectType);

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
}
