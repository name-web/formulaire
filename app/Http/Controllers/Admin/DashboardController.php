<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_projects' => Project::count(),
            'pending_projects' => Project::where('status', 'pending')->count(),
            'in_progress_projects' => Project::where('status', 'in_progress')->count(),
            'completed_projects' => Project::where('status', 'completed')->count(),
            'total_clients' => Client::count(),
            'total_users' => User::count(),
        ];

        $recentProjects = Project::with('client')
            ->latest()
            ->take(5)
            ->get();

        $recentClients = Client::withCount('projects')
            ->latest()
            ->take(5)
            ->get();

        $projectsByType = Project::selectRaw('project_type, COUNT(*) as count')
            ->get()
            ->flatMap(function ($item) {
                $types = is_array($item->project_type) ? $item->project_type : [$item->project_type];

                return collect($types)->map(fn ($t) => ['type' => $t, 'count' => 1]);
            })
            ->groupBy('type')
            ->map(fn ($group) => $group->count())
            ->mapWithKeys(fn ($count, $type) => [
                $type => ['label' => config("project_types.types.{$type}.label", $type), 'count' => $count],
            ]);

        return view('admin.dashboard', compact('stats', 'recentProjects', 'recentClients', 'projectsByType'));
    }
}
