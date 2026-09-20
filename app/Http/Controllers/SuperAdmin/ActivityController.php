<?php

namespace App\Http\Controllers\SuperAdmin;

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
     * Journal global : le Super Admin suit les actions des commerciaux et
     * l'évolution des prospects attribués, sur exactement les mêmes lignes que
     * l'espace Admin.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'type', 'group', 'user_id', 'from', 'to']);

        return view('super-admin.activities.index', [
            'activities' => $this->feed->paginate($filters),
            'authors' => $this->feed->authors(),
        ]);
    }
}
