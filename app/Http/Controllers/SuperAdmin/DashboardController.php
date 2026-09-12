<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientComment;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'developers' => User::where('role', 'developer')->count(),
            'prospects' => Client::prospects()->count(),
            'clients' => Client::clients()->count(),
            'unassigned_prospects' => Client::prospects()->whereNull('assigned_developer_id')->count(),
            'due_today' => Client::prospects()->followUpDueToday()->count(),
            'overdue' => Client::prospects()->followUpOverdue()->count(),
            'comments' => ClientComment::count(),
        ];

        $developers = User::where('role', 'developer')
            ->withCount([
                'assignedClients as prospects_count' => fn ($query) => $query->where('status', 'prospect'),
                'assignedClients as clients_count' => fn ($query) => $query->where('status', 'client'),
                'assignedClients as overdue_count' => fn ($query) => $query->where('status', 'prospect')
                    ->whereDate('next_action_date', '<', today()),
            ])
            ->orderBy('name')
            ->get();

        $unassignedProspects = Client::prospects()
            ->whereNull('assigned_developer_id')
            ->latest()
            ->take(5)
            ->get();

        $upcomingFollowUps = Client::prospects()
            ->whereNotNull('next_action_date')
            ->with('assignedDeveloper')
            ->orderBy('next_action_date')
            ->take(6)
            ->get();

        $recentComments = ClientComment::with(['client', 'user'])
            ->latest()
            ->take(6)
            ->get();

        $recentConversions = Client::clients()
            ->whereNotNull('assigned_developer_id')
            ->with('assignedDeveloper')
            ->latest('updated_at')
            ->take(6)
            ->get();

        return view('super-admin.dashboard', compact(
            'stats',
            'developers',
            'unassignedProspects',
            'upcomingFollowUps',
            'recentComments',
            'recentConversions',
        ));
    }
}
