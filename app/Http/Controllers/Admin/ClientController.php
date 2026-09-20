<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\Activity\ActivityJournal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function __construct(
        private ActivityJournal $journal,
    ) {}

    public function index(Request $request): View
    {
        $query = Client::prospects()
            ->withCount('projects')
            ->whereHas('projects', fn ($q) => $q->where('status', 'submitted'));

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('name LIKE ? ESCAPE ?', ["%{$search}%", '\\'])
                    ->orWhereRaw('company_name LIKE ? ESCAPE ?', ["%{$search}%", '\\']);
            });
        }

        $clients = $query->latest()->paginate(10)->withQueryString();

        return view('admin.clients.index', compact('clients'));
    }

    public function show(Client $client): View
    {
        $client->load(['projects', 'comments.user', 'assignedDeveloper', 'assignedCommercial']);

        return view('admin.clients.show', [
            'client' => $client,
            'context' => 'admin',
        ]);
    }

    public function convert(Client $client): RedirectResponse
    {
        if ($client->isClient()) {
            return back()->with('error', 'Ce prospect est déjà un client.');
        }

        $client->update(['status' => 'client']);

        $this->journal->converted($client, auth()->user());

        return redirect()->route('admin.client-clients.show', $client)
            ->with('success', 'Le prospect a été converti en client.');
    }
}
