<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProspectController extends Controller
{
    public function index(Request $request): View
    {
        $query = Client::query()->with(['assignedDeveloper', 'latestComment'])->withCount('projects');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('developer')) {
            $query->where('assigned_developer_id', $request->developer);
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

        return view('super-admin.prospects.index', compact('clients', 'developers'));
    }

    /**
     * The Super Admin consults the same prospect detail page as the general
     * dashboard, so no information is duplicated. The `super_admin` context
     * keeps every link and action inside the private space.
     */
    public function show(Client $client): View
    {
        $client->load(['projects', 'comments.user', 'assignedDeveloper']);

        return view('admin.clients.show', [
            'client' => $client,
            'context' => 'super_admin',
        ]);
    }

    /**
     * Assign a prospect to a developer, reassign it, or remove the assignment.
     */
    public function updateAssignment(Request $request, Client $client): RedirectResponse
    {
        $validated = $request->validate([
            'assigned_developer_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where('role', 'developer'),
            ],
        ]);

        $developerId = $validated['assigned_developer_id'] ?? null;
        $previousDeveloperId = $client->assigned_developer_id;

        $client->update(['assigned_developer_id' => $developerId]);

        $message = match (true) {
            $developerId === null => "L'attribution a été retirée.",
            $previousDeveloperId === $developerId => 'Ce prospect est déjà attribué à ce développeur.',
            $previousDeveloperId === null => 'Prospect attribué.',
            default => 'Prospect réattribué.',
        };

        return back()->with('success', $message);
    }
}
