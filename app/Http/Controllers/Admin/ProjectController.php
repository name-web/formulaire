<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\Activity\ActivityJournal;
use App\Services\Devis\DevisAnswersPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(
        private DevisAnswersPresenter $answersPresenter,
        private ActivityJournal $journal,
    ) {}

    public function index(Request $request): View
    {
        $query = Project::with('client')->withCount('answers')
            ->where('status', '!=', 'draft');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $type = str_replace(['%', '_'], ['\\%', '\\_'], $request->type);
            $query->whereRaw('project_type LIKE ? ESCAPE ?', ['%"'.$type.'"%', '\\']);
        }

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('client', function ($client) use ($search) {
                    $client->whereRaw('name LIKE ? ESCAPE ?', ["%{$search}%", '\\'])
                        ->orWhereRaw('company_name LIKE ? ESCAPE ?', ["%{$search}%", '\\']);
                })->orWhereRaw('title LIKE ? ESCAPE ?', ["%{$search}%", '\\'])
                    ->orWhereRaw('description LIKE ? ESCAPE ?', ["%{$search}%", '\\']);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $totalResults = $query->count();
        $projects = $query->latest()->paginate(10)->withQueryString();

        /*
         * Le compteur de réponses suit la génération du projet : nombre
         * d'informations du nouveau formulaire, ou réponses historiques de
         * l'ancien questionnaire.
         */
        $projects->getCollection()->transform(function (Project $project): Project {
            $project->setAttribute('display_answers_count', $project->usesCurrentForm()
                ? $this->answersPresenter->count($project->form_data ?? [])
                : (int) $project->answers_count);

            return $project;
        });

        return view('admin.projects.index', compact('projects', 'totalResults'));
    }

    public function show(Request $request, Project $project): View
    {
        $project->load(['client', 'files', 'notes.user']);

        /*
         * Deux générations cohabitent :
         *  - nouveau formulaire : réponses structurées dans form_data ;
         *  - ancien questionnaire : réponses historiques questions / answers,
         *    inchangées et toujours consultables.
         */
        if ($project->usesCurrentForm()) {
            $answers = $project->form_data ?? [];
            $formSections = $this->answersPresenter->sections($answers);

            return view('admin.projects.show', [
                'project' => $project,
                'formSections' => $formSections,
                'formAnswerCount' => $this->answersPresenter->count($answers),
                'provisionalQuote' => $project->provisionalQuote,
                'groupedAnswers' => collect(),
                'answersPaginator' => new Paginator(
                    collect(),
                    0,
                    10,
                    1,
                    [
                        'path' => route('admin.projects.show', $project),
                        'pageName' => 'answers_page',
                    ]
                ),
            ]);
        }

        $projectTypes = is_array($project->project_type) ? $project->project_type : [$project->project_type];

        $answers = $project->answers()
            ->with(['question', 'answerOptions.questionOption'])
            ->whereHas('question', function ($q) use ($projectTypes) {
                foreach ($projectTypes as $type) {
                    $q->orWhereRaw('JSON_CONTAINS(project_types, ?)', ['"'.$type.'"']);
                }
            })
            ->get();

        $perPage = 10;
        $page = $request->input('answers_page', 1);

        $allAnswers = $answers->map(function ($answer) {
            $question = $answer->question;
            $displayValue = $answer->getDisplayValue();
            $selectValues = $answer->getSelectValues();

            return compact('answer', 'question', 'displayValue', 'selectValues');
        })->sortBy('question.sort_order')->values();

        $total = $allAnswers->count();
        $paginatedAnswers = $allAnswers->forPage($page, $perPage);
        $groupedAnswers = $paginatedAnswers->groupBy(fn ($item) => $item['question']->group_label ?? 'Informations');

        $answersPaginator = new Paginator(
            $paginatedAnswers,
            $total,
            $perPage,
            $page,
            [
                'path' => route('admin.projects.show', $project),
                'pageName' => 'answers_page',
            ]
        );

        return view('admin.projects.show', [
            'project' => $project,
            'formSections' => null,
            'formAnswerCount' => 0,
            'provisionalQuote' => null,
            'groupedAnswers' => $groupedAnswers,
            'answersPaginator' => $answersPaginator,
        ]);
    }

    public function updateStatus(Request $request, Project $project): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:'.implode(',', array_keys(config('project_status.statuses')))],
        ]);

        $previousStatus = $project->status;

        $project->update(['status' => $request->status]);

        /*
         * L'évolution d'un devis fait partie de la vie du prospect : elle est
         * journalisée sur la fiche qui porte le projet, sans créer de doublon.
         */
        if ($project->client !== null && $previousStatus !== $project->status) {
            $this->journal->projectStatusChanged($project->client, $request->user(), $project, $previousStatus, $project->status);
        }

        return redirect()->route('admin.projects.show', $project)->with('success', 'Statut mis à jour.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()->route('admin.projects.index')->with('success', 'Projet supprimé.');
    }
}
