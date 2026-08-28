<x-layouts.admin :title="'Utilisateurs'">
    <div class="admin-header">
        <div>
            <h1 class="admin-title">Utilisateurs</h1>
            <p class="admin-subtitle">Gérer les comptes administrateurs</p>
        </div>
        <button onclick="document.getElementById('createUserModal').style.display='flex'" class="admin-btn btn-primary"><i class="fa-solid fa-plus"></i> Ajouter</button>
    </div>

    <div class="admin-card">
        <form method="GET" style="display:flex;gap:12px;margin-bottom:24px;">
            <div style="flex:1;">
                <input type="text" name="search" class="admin-input" placeholder="Rechercher un utilisateur..." value="{{ request('search') }}">
            </div>
            <button type="submit" class="admin-btn btn-primary"><i class="fa-solid fa-search"></i></button>
            @if(request('search'))
                <a href="{{ route('admin.users.index') }}" class="admin-btn btn-secondary"><i class="fa-solid fa-xmark"></i></a>
            @endif
        </form>

        @if($users->isEmpty())
            <p style="color:var(--text-muted);font-size:13px;text-align:center;padding:50px 0;">Aucun utilisateur trouvé.</p>
        @else
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Créé le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td style="font-weight:600;color:rgba(255,255,255,0.85);">{{ $user->name }}</td>
                            <td style="color:var(--text-secondary);">{{ $user->email }}</td>
                            <td>
                                <span class="admin-badge {{ $user->isAdmin() ? 'badge-admin' : 'badge-user' }}">{{ ucfirst($user->role) }}</span>
                            </td>
                            <td>
                                <span class="admin-badge {{ $user->is_active ? 'badge-active' : 'badge-inactive' }}">{{ $user->is_active ? 'Actif' : 'Inactif' }}</span>
                            </td>
                            <td style="font-size:12px;color:var(--text-muted);">{{ $user->created_at->format('d/m/Y') }}</td>
                            <td>
                                <div style="display:flex;gap:6px;">
                                    <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="admin-btn {{ $user->is_active ? 'btn-danger' : 'btn-primary' }} btn-sm" title="{{ $user->is_active ? 'Désactiver' : 'Activer' }}">
                                            <i class="fa-solid {{ $user->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top:20px;">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    {{-- Create User Modal --}}
    <div id="createUserModal" style="display:none;position:fixed;inset:0;z-index:999;align-items:center;justify-content:center;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);" onclick="if(event.target===this)this.style.display='none'">
        <div style="width:100%;max-width:460px;background:linear-gradient(145deg,rgba(15,40,71,0.95),rgba(5,18,36,0.98));border:1px solid rgba(255,255,255,0.1);border-radius:20px;padding:36px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
                <h3 style="font-size:18px;font-weight:800;color:white;">Nouvel utilisateur</h3>
                <button onclick="document.getElementById('createUserModal').style.display='none'" style="background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:18px;"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <div class="form-group">
                    <label class="admin-label">Nom complet</label>
                    <input type="text" name="name" class="admin-input" value="{{ old('name') }}" required>
                </div>
                <div class="form-group">
                    <label class="admin-label">Email</label>
                    <input type="email" name="email" class="admin-input" value="{{ old('email') }}" required>
                </div>
                <div class="form-group">
                    <label class="admin-label">Mot de passe</label>
                    <input type="password" name="password" class="admin-input" required>
                </div>
                <div class="form-group">
                    <label class="admin-label">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation" class="admin-input" required>
                </div>
                <div class="form-group">
                    <label class="admin-label">Rôle</label>
                    <select name="role" class="admin-input">
                        <option value="user">Utilisateur</option>
                        <option value="admin">Administrateur</option>
                    </select>
                </div>
                <div style="display:flex;gap:10px;margin-top:24px;">
                    <button type="button" onclick="document.getElementById('createUserModal').style.display='none'" class="admin-btn btn-secondary" style="flex:1;justify-content:center;">Annuler</button>
                    <button type="submit" class="admin-btn btn-primary" style="flex:1;justify-content:center;"><i class="fa-solid fa-plus"></i> Créer</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
