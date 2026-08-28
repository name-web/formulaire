<x-layouts.admin :title="'Projets'">
    <div class="admin-header">
        <div>
            <h1 class="admin-title">Projets</h1>
            <p class="admin-subtitle">Gérer les projets soumis par les clients</p>
        </div>
        <div style="display:flex;align-items:center;gap:10px;">
            <span style="font-size:12px;color:var(--text-muted);">{{ $totalResults }} résultat{{ $totalResults !== 1 ? 's' : '' }}</span>
        </div>
    </div>

    <div class="admin-card">
        <form method="GET" style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;align-items:flex-end;">
            <div style="flex:1;min-width:200px;">
                <label class="admin-label">Recherche</label>
                <input type="text" name="search" class="admin-input" placeholder="Client, email, projet..." value="{{ request('search') }}">
            </div>
            <div style="min-width:140px;">
                <label class="admin-label">Statut</label>
                <select name="status" class="admin-input">
                    <option value="">Tous les statuts</option>
                    @foreach(config('project_status.statuses') as $key => $sc)
                        <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $sc['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width:140px;">
                <label class="admin-label">Type</label>
                <select name="type" class="admin-input">
                    <option value="">Tous les types</option>
                    @foreach(config('project_types.types') as $key => $type)
                        <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $type['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width:130px;">
                <label class="admin-label">Date début</label>
                <input type="date" name="date_from" class="admin-input" value="{{ request('date_from') }}">
            </div>
            <div style="min-width:130px;">
                <label class="admin-label">Date fin</label>
                <input type="date" name="date_to" class="admin-input" value="{{ request('date_to') }}">
            </div>
            <div style="display:flex;gap:6px;">
                <button type="submit" class="admin-btn btn-primary"><i class="fa-solid fa-search"></i> Filtrer</button>
                @if(request()->hasAny(['search', 'status', 'type', 'date_from', 'date_to']))
                    <a href="{{ route('admin.projects.index') }}" class="admin-btn btn-secondary"><i class="fa-solid fa-xmark"></i></a>
                @endif
            </div>
        </form>

        @if($projects->isEmpty())
            <div style="text-align:center;padding:48px 0;">
                <i class="fa-solid fa-folder-open" style="font-size:36px;color:var(--text-muted);margin-bottom:12px;display:block;"></i>
                <p style="color:var(--text-muted);font-size:13px;">Aucun projet trouvé.</p>
                @if(request()->hasAny(['search', 'status', 'type', 'date_from', 'date_to']))
                    <a href="{{ route('admin.projects.index') }}" class="admin-btn btn-secondary" style="margin-top:12px;">Réinitialiser les filtres</a>
                @endif
            </div>
        @else
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width:50px;">#</th>
                        <th>Client</th>
                        <th>Type de projet</th>
                        <th>Réponses</th>
                        <th>Statut</th>
                        <th>Soumis le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projects as $project)
                        <tr style="cursor:pointer;" onclick="window.location='{{ route('admin.projects.show', $project) }}'">
                            <td style="font-weight:700;color:rgba(255,255,255,0.4);">{{ $project->id }}</td>
                            <td>
                                <div style="font-weight:600;color:rgba(255,255,255,0.85);">{{ $project->client->name ?? '—' }}</div>
                                <div style="font-size:11px;color:var(--text-muted);">{{ $project->client->email ?? '' }}</div>
                            </td>
                            <td>
                                @php $typeLabels = $project->type_labels; @endphp
                                <div style="display:flex;flex-wrap:wrap;gap:4px;">
                                    @foreach(explode(', ', $typeLabels) as $label)
                                        <span class="admin-badge badge-in_progress" style="font-size:10px;">{{ $label }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <span style="font-size:12px;color:var(--text-secondary);">{{ $project->answers_count }} réponse{{ $project->answers_count > 1 ? 's' : '' }}</span>
                            </td>
                            <td>
                                @php $sc = \App\Models\Project::getStatusConfig($project->status); @endphp
                                <span class="admin-badge {{ $sc['class'] }}">{{ $sc['label'] }}</span>
                            </td>
                            <td style="font-size:11px;color:var(--text-muted);">{{ $project->submitted_at ? $project->submitted_at->format('d/m/Y H:i') : $project->created_at->format('d/m/Y') }}</td>
                            <td onclick="event.stopPropagation();">
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
