<x-layouts.admin :title="'Mes prospects'">

    <section class="intro">
        <div>
            <div class="intro-label">
                <span></span>
                Espace développeur
            </div>
            <h1>Mes prospects</h1>
            <p>Uniquement les prospects qui vous sont assignés.</p>
        </div>
        <a href="{{ route('developer.dashboard') }}" class="top-btn"><i class="fa-solid fa-arrow-left" style="margin-right:4px;"></i> Mes suivis</a>
    </section>

    {{-- Filters --}}
    <section class="stats" style="grid-template-columns:1fr;min-height:auto;">
        <div class="stat" style="border-right:0;min-height:auto;">
            <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:220px;">
                    <label class="admin-label" for="my-prospect-search">Recherche</label>
                    <input type="text" id="my-prospect-search" name="search" class="admin-input" placeholder="Nom, entreprise..." value="{{ request('search') }}">
                </div>
                <div style="min-width:160px;">
                    <label class="admin-label" for="my-prospect-status">Statut</label>
                    <select id="my-prospect-status" name="status" class="admin-input">
                        <option value="">Tous</option>
                        <option value="prospect" @selected(request('status') === 'prospect')>Prospects</option>
                        <option value="client" @selected(request('status') === 'client')>Clients</option>
                    </select>
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="submit" class="admin-btn btn-primary"><i class="fa-solid fa-search"></i> Filtrer</button>
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('developer.prospects.index') }}" class="admin-btn btn-secondary"><i class="fa-solid fa-xmark"></i></a>
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
                    <div class="card-title">Mes prospects assignés</div>
                    <div class="card-subtitle">{{ $clients->total() }} prospect{{ $clients->total() > 1 ? 's' : '' }}</div>
                </div>
            </div>

            @if($clients->isEmpty())
                <div class="empty-state">
                    <i class="fa-regular fa-user"></i>
                    <p>Aucun prospect ne vous est assigné pour le moment.</p>
                </div>
            @else
                <div class="table-wrap">
                    <div class="table-head" style="grid-template-columns:1.2fr 1fr .6fr 1.1fr .7fr .5fr 52px;">
                        <div>Prospect</div>
                        <div>Entreprise</div>
                        <div>Statut</div>
                        <div>Prochaine action</div>
                        <div>Dernier suivi</div>
                        <div>Projets</div>
                        <div></div>
                    </div>

                    @foreach($clients as $client)
                        <div class="table-row" style="grid-template-columns:1.2fr 1fr .6fr 1.1fr .7fr .5fr 52px;cursor:pointer;" onclick="window.location='{{ route('developer.prospects.show', $client) }}'">
                            <div class="client">
                                <div class="client-avatar {{ $client->isClient() ? 'client-avatar-is-client' : '' }}">{{ strtoupper(substr($client->name, 0, 2)) }}</div>
                                <div>
                                    <div class="client-name {{ $client->isClient() ? 'client-name-is-client' : '' }}">{{ $client->name }}</div>
                                </div>
                            </div>
                            <div class="col-meta" data-label="Entreprise">{{ $client->company_name ?? '—' }}</div>
                            <div data-label="Statut">
                                @if($client->isClient())
                                    <span class="admin-badge badge-client">CLIENT</span>
                                @else
                                    <span class="admin-badge badge-pending">PROSPECT</span>
                                @endif
                            </div>
                            <div data-label="Prochaine action">
                                @if($client->next_action_date)
                                    <span class="admin-badge {{ $client->isFollowUpOverdue() ? 'badge-submitted' : 'badge-in_progress' }}">{{ $client->next_action_date->format('d/m/Y') }}</span>
                                    <div style="color:var(--muted);font-size:12px;margin-top:4px;">{{ $client->next_action ?? 'Action non précisée' }}</div>
                                @else
                                    <span style="color:var(--muted);font-size:13px;">Non planifiée</span>
                                @endif
                            </div>
                            <div class="date">{{ $client->last_activity_at?->format('d/m/Y') ?? '—' }}</div>
                            <div class="col-info" data-label="Projets">
                                <span class="admin-badge badge-user">{{ $client->projects_count }}</span>
                            </div>
                            <a href="{{ route('developer.prospects.show', $client) }}" class="open" onclick="event.stopPropagation();">↗</a>
                        </div>
                    @endforeach
                </div>

                <div style="padding:18px 22px;">
                    {{ $clients->links() }}
                </div>
            @endif
        </div>
    </section>

</x-layouts.admin>
