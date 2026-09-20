<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $commercial = auth()->user();

        $board = [
            'dueToday' => Client::assignedToCommercial($commercial)
                ->prospects()
                ->followUpDueToday()
                ->with(['assignedDeveloper', 'latestComment'])
                ->withCount('projects')
                ->orderBy('next_action_date')
                ->get(),
            'overdue' => Client::assignedToCommercial($commercial)
                ->prospects()
                ->followUpOverdue()
                ->with(['assignedDeveloper', 'latestComment'])
                ->withCount('projects')
                ->orderBy('next_action_date')
                ->get(),
            'inProgress' => Client::assignedToCommercial($commercial)
                ->prospects()
                ->with(['assignedDeveloper', 'latestComment'])
                ->withCount('projects')
                ->latest()
                ->get(),
            'converted' => Client::assignedToCommercial($commercial)
                ->clients()
                ->with(['assignedDeveloper', 'latestComment'])
                ->withCount('projects')
                ->latest('updated_at')
                ->get(),
        ];

        $recentComments = $commercial->comments()
            ->with('client')
            ->latest()
            ->take(6)
            ->get();

        return view('commercial.dashboard', compact('board', 'recentComments'));
    }
}
