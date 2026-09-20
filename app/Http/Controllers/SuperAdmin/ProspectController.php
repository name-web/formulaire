<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use App\Services\Activity\ActivityJournal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProspectController extends Controller
{
    public function __construct(
        private ActivityJournal $journal,
    ) {}

    public function index(Request $request): View
    {
        $query = Client::query()->with(['assignedDeveloper', 'assignedCommercial', 'latestComment'])->withCount('projects');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('developer')) {
            $query->where('assigned_developer_id', $request->developer);
        }

        if ($request->filled('commercial')) {
            $query->where('assigned_commercial_id', $request->commercial);
        }

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('name LIKE ? ESCAPE ?', ["%{$search}%", '\\'])
                    ->orWhereRaw('company_name LIKE ? ESCAPE ?', ["%{$search}%", '\\']);
            });
        }

        $clients = $query->latest()->paginate(15)->withQueryString();

        $developers = User::where('role', 'developer')->orderBy('name')->get();
        $commercials = User::where('role', 'commercial')->orderBy('name')->get();

        return view('super-admin.prospects.index', compact('clients', 'developers', 'commercials'));
    }

    /**
     * The Super Admin consults the same prospect detail page as the general
     * dashboard, so no information is duplicated. The `super_admin` context
     * keeps every link and action inside the private space.
     */
    public function show(Client $client): View
    {
        $client->load(['projects', 'comments.user', 'assignedDeveloper', 'assignedCommercial']);

        return view('admin.clients.show', [
            'client' => $client,
            'context' => 'super_admin',
        ]);
    }

    /**
     * Assign a prospect to a developer and/or commercial, reassign, or remove assignments.
     */
    public function updateAssignment(Request $request, Client $client): RedirectResponse
    {
        $validated = $request->validate([
            'assigned_developer_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where('role', 'developer'),
            ],
            'assigned_commercial_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where('role', 'commercial'),
            ],
        ]);

        $developerId = $validated['assigned_developer_id'] ?? null;
        $commercialId = $validated['assigned_commercial_id'] ?? null;
        $previousDeveloperId = $client->assigned_developer_id;
        $previousCommercialId = $client->assigned_commercial_id;
        $previousDeveloper = $client->assignedDeveloper;
        $previousCommercial = $client->assignedCommercial;

        $client->update([
            'assigned_developer_id' => $developerId,
            'assigned_commercial_id' => $commercialId,
        ]);

        /*
         * L'attribution reste la prérogative du Super Admin : c'est la seule
         * action du journal qui lui est propre. Rien n'est écrit si la
         * sélection n'a pas changé.
         */
        $this->journal->assignmentChanged(
            $client,
            $request->user(),
            $previousDeveloper,
            $developerId !== null ? User::find($developerId) : null,
            $previousCommercial,
            $commercialId !== null ? User::find($commercialId) : null,
        );

        $parts = [];
        if ($developerId !== $previousDeveloperId) {
            $parts[] = match (true) {
                $developerId === null => 'Attribution développeur retirée.',
                $previousDeveloperId === null => 'Prospect attribué à un développeur.',
                default => 'Développeur réattribué.',
            };
        }
        if ($commercialId !== $previousCommercialId) {
            $parts[] = match (true) {
                $commercialId === null => 'Attribution commercial retirée.',
                $previousCommercialId === null => 'Prospect attribué à un commercial.',
                default => 'Commercial réattribué.',
            };
        }

        $message = $parts !== [] ? implode(' ', $parts) : 'Aucun changement.';

        return back()->with('success', $message);
    }
}
