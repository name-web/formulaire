<x-layouts.admin :title="'Projets'">
    <div class="admin-header">
        <div>
            <h1 class="admin-title">Projets</h1>
            <p class="admin-subtitle">Gérer les projets soumis par les clients</p>
        </div>
    </div>

    <div class="admin-card">
        <form method="GET" style="display:flex;gap:12px;margin-bottom:24px;flex-wrap:wrap;">
            <div style="flex:1;min-width:200px;">
                <input type="text" name="search" class="admin-input" placeholder="Rechercher un client, un projet..." value="{{ request('search') }}">
            </div>
            <div style="min-width:150px;">
                <select name="status" class="admin-input">
                    <option value="">Tous les statuts</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En cours</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Terminé</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulé</option>
                </select>
            </div>
            <div style="min-width:150px;">
                <select name="type" class="admin-input">
                    <option value="">Tous les types</option>
                    @foreach(config('project_types.types') as $key => $type)
                        <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $type['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="admin-btn btn-primary"><i class="fa-solid fa-search"></i> Filtrer</button>
            @if(request()->hasAny(['search', 'status', 'type']))
                <a href="{{ route('admin.projects.index') }}" class="admin-btn btn-secondary"><i class="fa-solid fa-xmark"></i> Réinitialiser</a>
            @endif
        </form>

        @if($projects->isEmpty())
            <p style="color:var(--text-muted);font-size:13px;text-align:center;padding:50px 0;">Aucun projet trouvé.</p>
        @else
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Client</th>
                        <th>Type de projet</th>
                        <th>Statut</th>
                        <th>Soumis le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projects as $project)
                        <tr>
                            <td style="font-weight:700;color:rgba(255,255,255,0.5);">{{ $project->id }}</td>
                            <td>
                                <div style="font-weight:600;color:rgba(255,255,255,0.85);">{{ $project->client->name ?? '—' }}</div>
                                <div style="font-size:11px;color:var(--text-muted);">{{ $project->client->email ?? '' }}</div>
                            </td>
                            <td>
                                @php
                                    $types = is_array($project->project_type) ? $project->project_type : [$project->project_type];
                                    $typeLabels = collect($types)->map(fn($t) => config("project_types.types.{$t}.label", $t));
                                @endphp
                                <div style="display:flex;flex-wrap:wrap;gap:4px;">
                                    @foreach($typeLabels as $label)
                                        <span class="admin-badge badge-in_progress" style="font-size:10px;">{{ $label }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                @php
                                    $statusClass = match($project->status) {
                                        'pending' => 'badge-pending',
                                        'in_progress' => 'badge-in_progress',
                                        'completed' => 'badge-completed',
                                        default => 'badge-cancelled',
                                    };
                                    $statusLabel = match($project->status) {
                                        'pending' => 'En attente',
                                        'in_progress' => 'En cours',
                                        'completed' => 'Terminé',
                                        default => 'Annulé',
                                    };
                                @endphp
                                <span class="admin-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>
                            <td style="font-size:12px;color:var(--text-muted);">{{ $project->submitted_at ? $project->submitted_at->format('d/m/Y H:i') : $project->created_at->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('admin.projects.show', $project) }}" class="admin-btn btn-secondary btn-sm"><i class="fa-solid fa-eye"></i></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top:20px;">
                {{ $projects->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin>
