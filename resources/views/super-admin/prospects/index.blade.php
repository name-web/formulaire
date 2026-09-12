<x-layouts.admin :title="'Prospects & attribution'">

    <section class="intro">
        <div>
            <div class="intro-label">
                <span></span>
                Super Admin
            </div>
            <h1>Prospects & attribution</h1>
            <p>Attribuez ou réattribuez chaque prospect à un développeur.</p>
        </div>
        <a href="{{ route('super_admin.dashboard') }}" class="top-btn"><i class="fa-solid fa-arrow-left" style="margin-right:4px;"></i> Vue globale</a>
    </section>

    {{-- Filters --}}
    <section class="stats" style="grid-template-columns:1fr;min-height:auto;">
        <div class="stat" style="border-right:0;min-height:auto;">
            <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:220px;">
                    <label class="admin-label" for="prospect-search">Recherche</label>
                    <input type="text" id="prospect-search" name="search" class="admin-input" placeholder="Nom, entreprise..." value="{{ request('search') }}">
                </div>
                <div style="min-width:160px;">
                    <label class="admin-label" for="prospect-status">Statut</label>
                    <select id="prospect-status" name="status" class="admin-input">
                        <option value="">Tous</option>
                        <option value="prospect" @selected(request('status') === 'prospect')>Prospects</option>
                        <option value="client" @selected(request('status') === 'client')>Clients</option>
                    </select>
                </div>
                <div style="min-width:180px;">
                    <label class="admin-label" for="prospect-developer">Développeur</label>
                    <select id="prospect-developer" name="developer" class="admin-input">
                        <option value="">Tous</option>
                        @foreach($developers as $developer)
                            <option value="{{ $developer->id }}" @selected((string) request('developer') === (string) $developer->id)>{{ $developer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="submit" class="admin-btn btn-primary"><i class="fa-solid fa-search"></i> Filtrer</button>
                    @if(request()->hasAny(['search', 'status', 'developer']))
                        <a href="{{ route('super_admin.prospects.index') }}" class="admin-btn btn-secondary"><i class="fa-solid fa-xmark"></i></a>
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
                    <div class="card-title">Tous les prospects</div>
                    <div class="card-subtitle">{{ $clients->total() }} enregistrement{{ $clients->total() > 1 ? 's' : '' }}</div>
                </div>
            </div>

            @if($clients->isEmpty())
                <div class="empty-state">
                    <i class="fa-regular fa-user"></i>
                    <p>Aucun prospect trouvé.</p>
                </div>
            @else
                <div class="table-wrap">
                    <div class="table-head" style="grid-template-columns:1.2fr 1fr .6fr 1.3fr 1fr .7fr 52px;">
                        <div>Prospect</div>
                        <div>Entreprise</div>
                        <div>Statut</div>
                        <div>Développeur responsable</div>
                        <div>Prochaine action</div>
                        <div>Dernier suivi</div>
                        <div></div>
                    </div>

                    @foreach($clients as $client)
                        <div class="table-row" style="grid-template-columns:1.2fr 1fr .6fr 1.3fr 1fr .7fr 52px;">
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
                            <div data-label="Développeur">
                                <form method="POST" action="{{ route('super_admin.prospects.assignment.update', $client) }}" style="display:flex;gap:6px;align-items:center;">
                                    @csrf
                                    @method('PATCH')
                                    <select name="assigned_developer_id" class="admin-input" style="height:34px;padding:0 30px 0 12px;font-size:13px;" aria-label="Développeur responsable">
                                        <option value="">Non attribué</option>
                                        @foreach($developers as $developer)
                                            <option value="{{ $developer->id }}" @selected($client->assigned_developer_id === $developer->id)>{{ $developer->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="admin-btn btn-primary btn-sm" style="padding:6px 10px;" title="Enregistrer l'attribution">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                </form>
                            </div>
                            <div class="col-meta" data-label="Prochaine action">
                                {{ $client->next_action ?? '—' }}
                                @if($client->next_action_date)
                                    <br><span style="color:var(--muted);font-size:12px;">{{ $client->next_action_date->format('d/m/Y') }}</span>
                                @endif
                            </div>
                            <div class="date">{{ $client->last_activity_at?->format('d/m/Y') ?? '—' }}</div>
                            <a href="{{ route('super_admin.prospects.show', $client) }}" class="open">↗</a>
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
