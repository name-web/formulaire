<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\Activity\ActivityJournal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProspectController extends Controller
{
    public function __construct(
        private ActivityJournal $journal,
    ) {}

    public function index(Request $request): View
    {
        $query = Client::assignedToCommercial($request->user())
            ->with(['assignedDeveloper', 'latestComment'])
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

        return view('commercial.prospects.index', compact('clients'));
    }

    public function show(Request $request, int $client): View
    {
        $prospect = $this->findOwnedProspect($request, $client);

        /*
         * Chaque ouverture de fiche est journalisée côté serveur. Le service
         * ignore les rechargements trop rapprochés pour ne pas noyer le journal.
         */
        $this->journal->view($prospect, $request->user());

        return view('commercial.prospects.show', compact('prospect'));
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

        $this->journal->commentAdded($prospect, $request->user(), $validated['content']);

        return back()->with('success', 'Suivi ajouté.');
    }

    public function updateFollowUp(Request $request, int $client): RedirectResponse
    {
        $prospect = $this->findOwnedProspect($request, $client);

        $validated = $request->validate([
            'next_action' => ['nullable', 'string', 'max:255'],
            'next_action_date' => ['nullable', 'date'],
        ]);

        $previousAction = $prospect->next_action;
        $previousDate = $prospect->next_action_date?->format('Y-m-d');
        $hadFollowUp = $previousAction !== null || $previousDate !== null;

        $prospect->update([
            'next_action' => $validated['next_action'] ?? null,
            'next_action_date' => $validated['next_action_date'] ?? null,
        ]);

        $newAction = $prospect->next_action;
        $newDate = $prospect->next_action_date?->format('Y-m-d');
        $hasFollowUp = $newAction !== null || $newDate !== null;

        /*
         * Une seule activité par enregistrement, dans cet ordre de priorité :
         * relance clôturée, relance (re)programmée, autre modification.
         * Rien n'est écrit si le formulaire n'a rien changé.
         */
        if ($hadFollowUp && ! $hasFollowUp) {
            $this->journal->followUpCompleted($prospect, $request->user(), $previousAction);
        } elseif ($newDate !== null && $newDate !== $previousDate) {
            $this->journal->followUpScheduled($prospect, $request->user(), $newAction, $newDate);
        } elseif ($previousAction !== $newAction || $previousDate !== $newDate) {
            $this->journal->updated(
                $prospect,
                $request->user(),
                'Prochaine action mise à jour : « '.$newAction.' »',
                ['action' => $newAction, 'date' => $newDate],
            );
        }

        return back()->with('success', 'Prochaine action enregistrée.');
    }

    public function convert(Request $request, int $client): RedirectResponse
    {
        $prospect = $this->findOwnedProspect($request, $client);

        if ($prospect->isClient()) {
            return back()->with('error', 'Ce prospect est déjà un client.');
        }

        $prospect->update(['status' => 'client']);

        $this->journal->converted($prospect, $request->user());

        return redirect()->route('commercial.prospects.show', $prospect)
            ->with('success', 'Le prospect a été converti en client. Son historique est conservé.');
    }

    public function converted(Request $request): View
    {
        $query = Client::assignedToCommercial($request->user())
            ->clients()
            ->with(['assignedDeveloper', 'latestComment'])
            ->withCount('projects');

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('name LIKE ? ESCAPE ?', ["%{$search}%", '\\'])
                    ->orWhereRaw('company_name LIKE ? ESCAPE ?', ["%{$search}%", '\\']);
            });
        }

        $clients = $query->latest('updated_at')->paginate(10)->withQueryString();

        return view('commercial.prospects.converted', compact('clients'));
    }

    /**
     * Resolve a prospect from the prospects assigned to the current commercial.
     *
     * Scoping the lookup on `assigned_commercial_id` guarantees that a commercial
     * can never reach another commercial's prospect, even by forging the URL.
     */
    private function findOwnedProspect(Request $request, int $clientId): Client
    {
        return Client::query()
            ->assignedToCommercial($request->user())
            ->with(['projects', 'comments.user', 'assignedDeveloper', 'assignedCommercial'])
            ->findOrFail($clientId);
    }
}
