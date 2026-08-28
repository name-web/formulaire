<x-layouts.admin :title="'Clients'">
    <div class="admin-header">
        <div>
            <h1 class="admin-title">Clients</h1>
            <p class="admin-subtitle">Tous les clients ayant soumis un projet</p>
        </div>
    </div>

    <div class="admin-card">
        <form method="GET" style="display:flex;gap:12px;margin-bottom:24px;">
            <div style="flex:1;">
                <input type="text" name="search" class="admin-input" placeholder="Rechercher par nom, email, entreprise..." value="{{ request('search') }}">
            </div>
            <button type="submit" class="admin-btn btn-primary"><i class="fa-solid fa-search"></i></button>
            @if(request('search'))
                <a href="{{ route('admin.clients.index') }}" class="admin-btn btn-secondary"><i class="fa-solid fa-xmark"></i></a>
            @endif
        </form>

        @if($clients->isEmpty())
            <p style="color:var(--text-muted);font-size:13px;text-align:center;padding:50px 0;">Aucun client trouvé.</p>
        @else
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Entreprise</th>
                        <th>Localisation</th>
                        <th>Projets</th>
                        <th>Inscrit le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clients as $client)
                        <tr>
                            <td style="font-weight:600;color:rgba(255,255,255,0.85);">{{ $client->name }}</td>
                            <td style="color:var(--text-secondary);">{{ $client->email }}</td>
                            <td style="color:var(--text-secondary);">{{ $client->company_name ?? '—' }}</td>
                            <td style="color:var(--text-secondary);font-size:12px;">{{ $client->city }}{{ $client->city && $client->country ? ', ' : '' }}{{ $client->country }}</td>
                            <td><span class="admin-badge badge-in_progress">{{ $client->projects_count }}</span></td>
                            <td style="font-size:12px;color:var(--text-muted);">{{ $client->created_at->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('admin.clients.show', $client) }}" class="admin-btn btn-secondary btn-sm"><i class="fa-solid fa-eye"></i></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top:20px;">
                {{ $clients->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin>
