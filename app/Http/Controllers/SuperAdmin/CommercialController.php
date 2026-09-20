<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CommercialController extends Controller
{
    public function index(): View
    {
        $commercials = User::where('role', 'commercial')
            ->withCount([
                'assignedCommercialClients as prospects_count' => fn ($query) => $query->where('status', 'prospect'),
                'assignedCommercialClients as clients_count' => fn ($query) => $query->where('status', 'client'),
                'assignedCommercialClients as overdue_count' => fn ($query) => $query->where('status', 'prospect')
                    ->whereDate('next_action_date', '<', today()),
            ])
            ->orderBy('name')
            ->get();

        return view('super-admin.commercials.index', compact('commercials'));
    }

    /**
     * Create a commercial from a name only: the email address and the password
     * are generated. The plain password is flashed once so the Super Admin can
     * hand it over; only its hash is persisted.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'name')->where('role', 'commercial'),
            ],
        ]);

        $password = Str::password(16);

        $commercial = User::create([
            'name' => $validated['name'],
            'email' => $this->generateEmail($validated['name']),
            'password' => $password,
            'role' => 'commercial',
            'is_active' => true,
        ]);

        return redirect()->route('super_admin.commercials.index')
            ->with('success', "Commercial « {$commercial->name} » créé.")
            ->with('generated_password', $password)
            ->with('generated_password_for', $commercial->name);
    }

    public function show(User $commercial): View
    {
        abort_unless($commercial->isCommercial(), 404);

        $prospects = $commercial->assignedCommercialClients()
            ->prospects()
            ->with(['assignedDeveloper', 'latestComment'])
            ->withCount('projects')
            ->latest()
            ->get();

        $clients = $commercial->assignedCommercialClients()
            ->clients()
            ->with(['assignedDeveloper', 'latestComment'])
            ->withCount('projects')
            ->latest('updated_at')
            ->get();

        $comments = $commercial->comments()
            ->with('client')
            ->latest()
            ->take(10)
            ->get();

        return view('super-admin.commercials.show', compact('commercial', 'prospects', 'clients', 'comments'));
    }

    /**
     * Build a unique technical email address: commercials never receive mail,
     * they only sign in with their name.
     */
    private function generateEmail(string $name): string
    {
        $base = Str::slug($name) ?: 'commercial';
        $email = $base.'@commercial.local';
        $suffix = 1;

        while (User::where('email', $email)->exists()) {
            $email = $base.'-'.(++$suffix).'@commercial.local';
        }

        return $email;
    }
}
