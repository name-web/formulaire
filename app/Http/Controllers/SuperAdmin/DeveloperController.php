<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FollowUp\FollowUpBoardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DeveloperController extends Controller
{
    public function __construct(private readonly FollowUpBoardService $boardService) {}

    public function index(): View
    {
        $developers = User::where('role', 'developer')
            ->withCount([
                'assignedClients as prospects_count' => fn ($query) => $query->where('status', 'prospect'),
                'assignedClients as clients_count' => fn ($query) => $query->where('status', 'client'),
                'assignedClients as overdue_count' => fn ($query) => $query->where('status', 'prospect')
                    ->whereDate('next_action_date', '<', today()),
            ])
            ->orderBy('name')
            ->get();

        return view('super-admin.developers.index', compact('developers'));
    }

    /**
     * Create a developer from a name only: the email address and the password
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
                Rule::unique('users', 'name')->where('role', 'developer'),
            ],
        ]);

        $password = Str::password(16);

        $developer = User::create([
            'name' => $validated['name'],
            'email' => $this->generateEmail($validated['name']),
            'password' => $password,
            'role' => 'developer',
            'is_active' => true,
        ]);

        return redirect()->route('super_admin.developers.index')
            ->with('success', "Développeur « {$developer->name} » créé.")
            ->with('generated_password', $password)
            ->with('generated_password_for', $developer->name);
    }

    public function show(User $developer): View
    {
        abort_unless($developer->isDeveloper(), 404);

        $board = $this->boardService->forDeveloper($developer);

        $comments = $developer->comments()
            ->with('client')
            ->latest()
            ->take(10)
            ->get();

        return view('super-admin.developers.show', compact('developer', 'board', 'comments'));
    }

    /**
     * Build a unique technical email address: developers never receive mail,
     * they only sign in with their name.
     */
    private function generateEmail(string $name): string
    {
        $base = Str::slug($name) ?: 'developpeur';
        $email = $base.'@developpeur.local';
        $suffix = 1;

        while (User::where('email', $email)->exists()) {
            $email = $base.'-'.(++$suffix).'@developpeur.local';
        }

        return $email;
    }
}
