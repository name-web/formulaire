<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $query = Project::with('client');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->whereRaw('project_type LIKE ?', ['%"'.$request->type.'"%']);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('client', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            })->orWhere('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $projects = $query->latest()->paginate(15)->withQueryString();

        $totalResults = $query->count();

        return view('admin.projects.index', compact('projects', 'totalResults'));
    }

    public function show(Project $project): View
    {
        $project->load(['client', 'answers.question', 'answers.answerOptions.questionOption', 'files', 'notes.user']);

        $answers = $project->answers->map(function ($answer) {
            $question = $answer->question;
            $displayValue = $answer->getDisplayValue();
            $selectValues = $answer->getSelectValues();

            return compact('answer', 'question', 'displayValue', 'selectValues');
        });

        $groupedAnswers = $answers->groupBy(fn ($item) => $item['question']->group_label ?? 'Informations');

        return view('admin.projects.show', compact('project', 'answers', 'groupedAnswers'));
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
