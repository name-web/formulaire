<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Services\FollowUp\FollowUpBoardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly FollowUpBoardService $boardService) {}

    public function index(): View
    {
        $developer = auth()->user();

        $board = $this->boardService->forDeveloper($developer);

        $recentComments = $developer->comments()
            ->with('client')
            ->latest()
            ->take(6)
            ->get();

        return view('developer.dashboard', compact('board', 'recentComments'));
    }
}
