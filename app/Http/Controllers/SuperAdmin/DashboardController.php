<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientComment;
use App\Models\User;
use App\Services\Activity\ActivityFeedService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private ActivityFeedService $activityFeed,
    ) {}

    public function index(): View
    {
        $stats = [
            'developers' => User::where('role', 'developer')->count(),
            'commercials' => User::where('role', 'commercial')->count(),
            'prospects' => Client::prospects()->count(),
            'clients' => Client::clients()->count(),
            'unassigned_prospects' => Client::prospects()->whereNull('assigned_developer_id')->whereNull('assigned_commercial_id')->count(),
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

        $commercials = User::where('role', 'commercial')
            ->withCount([
                'assignedCommercialClients as prospects_count' => fn ($query) => $query->where('status', 'prospect'),
                'assignedCommercialClients as clients_count' => fn ($query) => $query->where('status', 'client'),
                'assignedCommercialClients as overdue_count' => fn ($query) => $query->where('status', 'prospect')
                    ->whereDate('next_action_date', '<', today()),
            ])
            ->orderBy('name')
            ->get();

        $unassignedProspects = Client::prospects()
            ->whereNull('assigned_developer_id')
            ->whereNull('assigned_commercial_id')
            ->latest()
            ->take(5)
            ->get();

        $upcomingFollowUps = Client::prospects()
            ->whereNotNull('next_action_date')
            ->with(['assignedDeveloper', 'assignedCommercial'])
            ->orderBy('next_action_date')
            ->take(6)
            ->get();

        $recentComments = ClientComment::with(['client', 'user'])
            ->latest()
            ->take(6)
            ->get();

        $recentConversions = Client::clients()
            ->where(function ($q) {
                $q->whereNotNull('assigned_developer_id')
                    ->orWhereNotNull('assigned_commercial_id');
            })
            ->with(['assignedDeveloper', 'assignedCommercial'])
            ->latest('updated_at')
            ->take(6)
            ->get();

        /*
         * Le Super Admin suit les actions des commerciaux et l'évolution des
         * prospects attribués directement depuis sa vue globale.
         */
        $recentActivities = $this->activityFeed->recent();

        return view('super-admin.dashboard', compact(
            'stats',
            'developers',
            'commercials',
            'unassignedProspects',
            'upcomingFollowUps',
            'recentComments',
            'recentConversions',
            'recentActivities',
        ));
    }
}
