<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $query = Project::with('client')->withCount('answers');

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
                        ->orWhereRaw('email LIKE ? ESCAPE ?', ["%{$search}%", '\\'])
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

        return view('admin.projects.index', compact('projects', 'totalResults'));
    }

    public function show(Request $request, Project $project): View
    {
        $project->load(['client', 'answers.question', 'answers.answerOptions.questionOption', 'files', 'notes.user']);

        $perPage = 10;
        $page = $request->input('answers_page', 1);

        $allAnswers = $project->answers->map(function ($answer) {
            $question = $answer->question;
            $displayValue = $answer->getDisplayValue();
            $selectValues = $answer->getSelectValues();

            return compact('answer', 'question', 'displayValue', 'selectValues');
        });

        $total = $allAnswers->count();
        $answers = $allAnswers->forPage($page, $perPage);
        $groupedAnswers = $answers->groupBy(fn ($item) => $item['question']->group_label ?? 'Informations');

        $answersPaginator = new Paginator(
            $answers,
            $total,
            $perPage,
            $page,
            [
                'path' => route('admin.projects.show', $project),
                'pageName' => 'answers_page',
            ]
        );

        return view('admin.projects.show', compact('project', 'answers', 'groupedAnswers', 'answersPaginator'));
    }

    public function updateStatus(Request $request, Project $project): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:'.implode(',', array_keys(config('project_status.statuses')))],
        ]);

        $project->update(['status' => $request->status]);

        return redirect()->route('admin.projects.show', $project)->with('success', 'Statut mis à jour.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()->route('admin.projects.index')->with('success', 'Projet supprimé.');
    }
}
