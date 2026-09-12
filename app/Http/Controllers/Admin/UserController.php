<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(10)->withQueryString();
        $assignableRoles = $this->assignableRoles();

        return view('admin.users.index', compact('users', 'assignableRoles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:'.implode(',', array_keys($this->assignableRoles()))],
        ]);

        $validated['is_active'] = true;

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', 'Utilisateur créé.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureSuperAdminAccountIsManagedByASuperAdmin($user);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['sometimes', 'in:'.implode(',', array_keys($this->assignableRoles()))],
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'Utilisateur mis à jour.');
    }

    public function toggleActive(User $user): RedirectResponse
    {
        $this->ensureSuperAdminAccountIsManagedByASuperAdmin($user);

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'activé' : 'désactivé';

        return redirect()->route('admin.users.index')->with('success', "Utilisateur {$status}.");
    }

    /**
     * Roles the current user may assign. Only a Super Admin can grant the
     * Super Admin role, so a general administrator can never escalate.
     *
     * @return array<string, array{label: string, class: string}>
     */
    private function assignableRoles(): array
    {
        $roles = config('user_roles.roles', []);

        if (auth()->user()?->isSuperAdmin()) {
            return $roles;
        }

        unset($roles['super_admin']);

        return $roles;
    }

    /**
     * A Super Admin account is only manageable by a Super Admin: a general
     * administrator must not be able to demote, rename or deactivate it.
     */
    private function ensureSuperAdminAccountIsManagedByASuperAdmin(User $user): void
    {
        if ($user->isSuperAdmin() && ! auth()->user()?->isSuperAdmin()) {
            abort(403, 'Seul un Super Administrateur peut gérer un compte Super Administrateur.');
        }
    }
}
