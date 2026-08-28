<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_projects' => Project::count(),
            'submitted_projects' => Project::where('status', 'submitted')->count(),
            'pending_projects' => Project::where('status', 'pending')->count(),
            'in_progress_projects' => Project::where('status', 'in_progress')->count(),
            'completed_projects' => Project::where('status', 'completed')->count(),
            'cancelled_projects' => Project::where('status', 'cancelled')->count(),
            'total_clients' => Client::count(),
            'total_users' => User::count(),
        ];

        $recentProjects = Project::with('client')
            ->whereIn('status', array_keys(collect(config('project_status.statuses'))->filter()->toArray()))
            ->latest()
            ->take(config('dashboard.recent_projects_count', 8))
            ->get();

        $recentClients = Client::withCount('projects')
            ->whereHas('projects', fn ($q) => $q->where('status', 'submitted'))
            ->latest()
            ->take(config('dashboard.recent_clients_count', 5))
            ->get();

        $projectsByType = $this->getProjectsByType();

        $projectsByStatus = $this->getProjectsByStatus();

        $submissionsByMonth = $this->getSubmissionsByMonth();

        return view('admin.dashboard', compact(
            'stats',
            'recentProjects',
            'recentClients',
            'projectsByType',
            'projectsByStatus',
            'submissionsByMonth',
        ));
    }

    private function getProjectsByType(): Collection
    {
        return Project::get(['project_type'])
            ->flatMap(fn (Project $project) => is_array($project->project_type) ? $project->project_type : [$project->project_type])
            ->countBy()
            ->map(fn (int $count, string $type) => [
                'type' => $type,
                'label' => config("project_types.types.{$type}.label", $type),
                'icon' => config("project_types.types.{$type}.icon", 'fa-solid fa-folder'),
                'color' => config("project_types.types.{$type}.color", '#3b82f6'),
                'count' => $count,
            ])
            ->sortByDesc('count')
            ->values();
    }

    private function getProjectsByStatus()
    {
        $results = Project::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return collect(Project::getStatuses())
            ->mapWithKeys(fn ($config, $key) => [
                $key => [
                    'count' => $results->get($key, 0),
                    'label' => $config['label'],
                    'class' => $config['class'],
                    'icon' => $config['icon'],
                ],
            ]);
    }

    private function getSubmissionsByMonth(): Collection
    {
        $months = config('dashboard.months', []);
        $monthsBack = config('dashboard.months_back', 6);

        return Project::where('created_at', '>=', Carbon::now()->subMonths($monthsBack)->startOfMonth())
            ->get(['created_at'])
            ->countBy(fn (Project $project) => $project->created_at->format('Y-m'))
            ->sortKeys()
            ->mapWithKeys(fn (int $count, string $month) => [
                $months[substr($month, 5)] ?? $month => $count,
            ]);
    }
}
