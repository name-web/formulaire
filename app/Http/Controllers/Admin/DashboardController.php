<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
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

    private function getProjectsByType()
    {
        return Project::selectRaw('project_type, COUNT(*) as count')
            ->groupBy('project_type')
            ->get()
            ->flatMap(function ($item) {
                $types = is_array($item->project_type) ? $item->project_type : [$item->project_type];

                return collect($types)->map(fn ($t) => ['type' => $t, 'count' => 1]);
            })
            ->groupBy('type')
            ->map(fn ($group) => $group->count())
            ->mapWithKeys(fn ($count, $type) => [
                $type => [
                    'label' => config("project_types.types.{$type}.label", $type),
                    'icon' => config("project_types.types.{$type}.icon", 'fa-solid fa-folder'),
                    'color' => config("project_types.types.{$type}.color", '#3b82f6'),
                    'count' => $count,
                ],
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

    private function getSubmissionsByMonth()
    {
        $months = config('dashboard.months', []);
        $monthsBack = config('dashboard.months_back', 6);

        return Project::where('created_at', '>=', Carbon::now()->subMonths($monthsBack)->startOfMonth())
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->mapWithKeys(fn ($count, $month) => [
                $months[substr($month, 5)] ?? $month => $count,
            ]);
    }
}
