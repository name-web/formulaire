<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProspectController extends Controller
{
    public function index(Request $request): View
    {
        $query = Client::query()
            ->assignedTo($request->user())
            ->with('latestComment')
            ->withCount('projects');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('name LIKE ? ESCAPE ?', ["%{$search}%", '\\'])
                    ->orWhereRaw('company_name LIKE ? ESCAPE ?', ["%{$search}%", '\\']);
            });
        }

        $clients = $query->latest()->paginate(10)->withQueryString();

        return view('developer.prospects.index', compact('clients'));
    }

    public function show(Request $request, int $client): View
    {
        $prospect = $this->findOwnedProspect($request, $client);

        return view('developer.prospects.show', compact('prospect'));
    }

    public function storeComment(Request $request, int $client): RedirectResponse
    {
        $prospect = $this->findOwnedProspect($request, $client);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
        ]);

        $prospect->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'Suivi ajouté.');
    }

    public function updateFollowUp(Request $request, int $client): RedirectResponse
    {
        $prospect = $this->findOwnedProspect($request, $client);

        $validated = $request->validate([
            'next_action' => ['nullable', 'string', 'max:255'],
            'next_action_date' => ['nullable', 'date'],
        ]);

        $prospect->update([
            'next_action' => $validated['next_action'] ?? null,
            'next_action_date' => $validated['next_action_date'] ?? null,
        ]);

        return back()->with('success', 'Prochaine action enregistrée.');
    }

    public function convert(Request $request, int $client): RedirectResponse
    {
        $prospect = $this->findOwnedProspect($request, $client);

        if ($prospect->isClient()) {
            return back()->with('error', 'Ce prospect est déjà un client.');
        }

        $prospect->update(['status' => 'client']);

        return redirect()->route('developer.prospects.show', $prospect)
            ->with('success', 'Le prospect a été converti en client. Son historique est conservé.');
    }

    /**
     * Resolve a prospect from the prospects assigned to the current developer.
     *
     * Scoping the lookup on `assigned_developer_id` guarantees that a developer
     * can never reach another developer's prospect, even by forging the URL.
     */
    private function findOwnedProspect(Request $request, int $clientId): Client
    {
        return Client::query()
            ->assignedTo($request->user())
            ->with(['projects', 'comments.user', 'assignedDeveloper'])
            ->findOrFail($clientId);
    }
}
