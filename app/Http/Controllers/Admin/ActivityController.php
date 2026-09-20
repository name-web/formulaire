<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Activity\ActivityFeedService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function __construct(
        private ActivityFeedService $feed,
    ) {}

    /**
     * Journal d'activité central : tout ce qui a été fait sur les prospects et
     * les clients, du plus récent au plus ancien, sans ouvrir chaque fiche.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'type', 'group', 'user_id', 'from', 'to']);

        return view('admin.activities.index', [
            'activities' => $this->feed->paginate($filters),
            'authors' => $this->feed->authors(),
        ]);
    }
}
