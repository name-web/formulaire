<x-layouts.admin :title="'Utilisateurs'">

    <section class="intro">
        <div>
            <div class="intro-label">
                <span></span>
                Gestion
            </div>
            <h1>Utilisateurs</h1>
            <p>Gérer les comptes administrateurs</p>
        </div>
        <button onclick="document.getElementById('createUserModal').style.display='flex'" class="top-btn primary"><i class="fa-solid fa-plus" style="margin-right:4px;"></i> Ajouter</button>
    </section>

    {{-- Search --}}
    <section class="stats" style="grid-template-columns:1fr;min-height:auto;">
        <div class="stat" style="border-right:0;min-height:auto;">
            <form method="GET" class="search-form" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:250px;">
                    <label class="admin-label">Recherche</label>
                    <input type="text" name="search" class="admin-input" placeholder="Rechercher un utilisateur..." value="{{ request('search') }}">
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="submit" class="admin-btn btn-primary"><i class="fa-solid fa-search"></i> Rechercher</button>
                    @if(request('search'))
                        <a href="{{ route('admin.users.index') }}" class="admin-btn btn-secondary"><i class="fa-solid fa-xmark"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </section>

    {{-- Table --}}
    <section class="content" style="grid-template-columns:1fr;margin-top:18px;">
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Tous les utilisateurs</div>
                    <div class="card-subtitle">Liste complète des comptes</div>
                </div>
            </div>

            @if($users->isEmpty())
                <div class="empty-state">
                    <i class="fa-regular fa-user"></i>
                    <p>Aucun utilisateur trouvé.</p>
                </div>
            @else
                <div class="table-wrap">
                <div class="table-head" style="grid-template-columns:1.2fr 1.4fr .8fr .8fr .8fr 80px;">
                    <div>Nom</div>
                    <div>Email</div>
                    <div>Rôle</div>
                    <div>Statut</div>
                    <div>Créé le</div>
                    <div>Actions</div>
                </div>

                @foreach($users as $user)
                    <div class="table-row" style="grid-template-columns:1.2fr 1.4fr .8fr .8fr .8fr 80px;">
                        <div class="client">
                            <div class="client-avatar">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
                            <div>
                                <div class="client-name">{{ $user->name }}</div>
                            </div>
                        </div>
                        <div class="project">{{ $user->email }}</div>
                        <div>
                            @php $roleConfig = config('user_roles.roles.' . $user->role, ['label' => ucfirst($user->role), 'class' => 'badge-user']); @endphp
                            <span class="admin-badge {{ $roleConfig['class'] }}">{{ $roleConfig['label'] }}</span>
                        </div>
                        <div>
                            <span class="admin-badge {{ $user->is_active ? 'badge-active' : 'badge-inactive' }}">{{ $user->is_active ? 'Actif' : 'Inactif' }}</span>
                        </div>
                        <div class="date">{{ $user->created_at->format('d/m/Y') }}</div>
                        <div style="display:flex;gap:6px;">
                            <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="admin-btn {{ $user->is_active ? 'btn-danger' : 'btn-primary' }} btn-sm" title="{{ $user->is_active ? 'Désactiver' : 'Activer' }}">
                                    <i class="fa-solid {{ $user->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
                </div>

                <div style="padding:18px 22px;">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </section>

    {{-- Create User Modal --}}
    <div id="createUserModal" style="display:none;position:fixed;inset:0;z-index:999;align-items:center;justify-content:center;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);" onclick="if(event.target===this)this.style.display='none'">
        <div class="modal-content" style="width:100%;max-width:480px;background:white;border:1px solid var(--line);border-radius:var(--radius);padding:36px;box-shadow:var(--shadow-lg);">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;">
                <h3 style="font-size:20px;font-weight:800;color:var(--black);">Nouvel utilisateur</h3>
                <button onclick="document.getElementById('createUserModal').style.display='none'" style="background:none;border:none;color:var(--muted);cursor:pointer;font-size:20px;transition:transform .2s ease;" onmouseover="this.style.transform='rotate(90deg)'" onmouseout="this.style.transform='rotate(0)'"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <div style="margin-bottom:18px;">
                    <label class="admin-label">Nom complet</label>
                    <input type="text" name="name" class="admin-input" value="{{ old('name') }}" required>
                </div>
                <div style="margin-bottom:18px;">
                    <label class="admin-label">Email</label>
                    <input type="email" name="email" class="admin-input" value="{{ old('email') }}" required>
                </div>
                <div style="margin-bottom:18px;">
                    <label class="admin-label">Mot de passe</label>
                    <input type="password" name="password" class="admin-input" required>
                </div>
                <div style="margin-bottom:18px;">
                    <label class="admin-label">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation" class="admin-input" required>
                </div>
                <div style="margin-bottom:24px;">
                    <label class="admin-label">Rôle</label>
                    <select name="role" class="admin-input">
                        @foreach(config('user_roles.roles') as $key => $role)
                            <option value="{{ $key }}">{{ $role['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex;gap:12px;">
                    <button type="button" onclick="document.getElementById('createUserModal').style.display='none'" class="admin-btn btn-secondary" style="flex:1;justify-content:center;">Annuler</button>
                    <button type="submit" class="admin-btn btn-primary" style="flex:1;justify-content:center;"><i class="fa-solid fa-plus"></i> Créer</button>
                </div>
            </form>
        </div>
    </div>

</x-layouts.admin>
