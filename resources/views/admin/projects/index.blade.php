<x-layouts.admin :title="'Projets'">

    <section class="intro">
        <div>
            <div class="intro-label">
                <span></span>
                Gestion
            </div>
            <h1>Projets</h1>
            <p>{{ $totalResults }} résultat{{ $totalResults !== 1 ? 's' : '' }} — projets soumis par les clients</p>
        </div>
        <div style="display:flex;gap:6px;">
            <form method="POST" action="{{ route('admin.projects.index') }}" style="display:inline;">
                @csrf
                <button type="submit" class="top-btn primary">Exporter CSV</button>
            </form>
        </div>
    </section>

    {{-- Filtres --}}
    <section class="stats" style="grid-template-columns:1fr;min-height:auto;">
        <div class="stat" style="border-right:0;min-height:auto;">
            <form method="GET" class="search-form" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:200px;">
                    <label class="admin-label">Recherche</label>
                    <input type="text" name="search" class="admin-input" placeholder="Client, email, projet..." value="{{ request('search') }}">
                </div>
                <div style="min-width:140px;">
                    <label class="admin-label">Statut</label>
                    <select name="status" class="admin-input">
                        <option value="">Tous les statuts</option>
                        @foreach(config('project_status.statuses') as $key => $sc)
                            @if($key !== 'draft')
                                <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $sc['label'] }}</option>
                            @endif
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
        </div>
    </section>

    {{-- Table --}}
    <section class="content" style="grid-template-columns:1fr;margin-top:16px;">
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Tous les projets</div>
                    <div class="card-subtitle">Liste complète des projets</div>
                </div>
                @if(request()->hasAny(['search', 'status', 'type', 'date_from', 'date_to']))
                    <a href="{{ route('admin.projects.index') }}" class="view-all">Réinitialiser les filtres</a>
                @endif
            </div>

            @if($projects->isEmpty())
                <div class="empty-state">
                    <i class="fa-regular fa-folder-open"></i>
                    <p>Aucun projet trouvé.</p>
                </div>
            @else
                <div class="table-wrap">
                <div class="table-head" style="grid-template-columns:.5fr 1.4fr 1.2fr .6fr .7fr .8fr 52px;">
                    <div>N°</div>
                    <div>Client</div>
                    <div>Type de projet</div>
                    <div>Réponses</div>
                    <div>Statut</div>
                    <div>Soumis le</div>
                    <div></div>
                </div>

                @foreach($projects as $project)
                    <div class="table-row" style="grid-template-columns:.5fr 1.4fr 1.2fr .6fr .7fr .8fr 52px;cursor:pointer;" onclick="window.location='{{ route('admin.projects.show', $project) }}'">
                        <div class="date" style="font-weight:700;">{{ $project->id }}</div>
                        <div class="client">
                            <div class="client-avatar">{{ strtoupper(substr($project->client->name ?? '?', 0, 2)) }}</div>
                            <div>
                                <div class="client-name">{{ $project->client->name ?? '—' }}</div>
                                <div class="client-email">{{ $project->client->email ?? '' }}</div>
                            </div>
                        </div>
                        <div>
                            @php $typeLabels = $project->type_labels ?? ''; @endphp
                            <div style="display:flex;flex-wrap:wrap;gap:4px;">
                                @if($typeLabels)
                                    @foreach(explode(', ', $typeLabels) as $label)
                                        @if($label)
                                            <span class="admin-badge badge-in_progress">{{ $label }}</span>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <div class="project">{{ $project->answers_count }} réponse{{ $project->answers_count > 1 ? 's' : '' }}</div>
                        <div>
                            @php $sc = \App\Models\Project::getStatusConfig($project->status); @endphp
                            <span class="status {{ in_array($project->status, ['completed','submitted']) ? 'done' : '' }}">{{ $sc['label'] }}</span>
                        </div>
                        <div class="date">{{ $project->submitted_at ? $project->submitted_at->format('d/m/Y H:i') : $project->created_at->format('d/m/Y') }}</div>
                        <a href="{{ route('admin.projects.show', $project) }}" class="open" onclick="event.stopPropagation();">↗</a>
                    </div>
                @endforeach
                </div>

                <div style="padding:16px 19px;">
                    {{ $projects->links() }}
                </div>
            @endif
        </div>
    </section>

</x-layouts.admin>
