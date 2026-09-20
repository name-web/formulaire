<x-layouts.admin :title="'Clients convertis'">

    <section class="intro">
        <div>
            <div class="intro-label">
                <span></span>
                Espace commercial
            </div>
            <h1>Clients convertis</h1>
            <p>{{ $clients->total() }} client{{ $clients->total() !== 1 ? 's' : '' }} converti{{ $clients->total() !== 1 ? 's' : '' }} par vos soins.</p>
        </div>
        <a href="{{ route('commercial.dashboard') }}" class="top-btn"><i class="fa-solid fa-arrow-left" style="margin-right:4px;"></i> Tableau de bord</a>
    </section>

    {{-- Filtres --}}
    <section class="stats" style="grid-template-columns:1fr;min-height:auto;">
        <div class="stat" style="border-right:0;min-height:auto;">
            <form method="GET" class="search-form" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:0;">
                    <label class="admin-label">Recherche</label>
                    <input type="text" name="search" class="admin-input" placeholder="Nom, entreprise..." value="{{ request('search') }}">
                </div>
                <div>
                    <button type="submit" class="admin-btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Filtrer</button>
                </div>
            </form>
        </div>
    </section>

    {{-- Clients table --}}
    <section class="content" style="grid-template-columns:1fr;">
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Mes clients convertis</div>
                    <div class="card-subtitle">{{ $clients->total() }} résultat{{ $clients->total() !== 1 ? 's' : '' }}</div>
                </div>
            </div>

            @if($clients->isEmpty())
                <div class="empty-state">
                    <i class="fa-regular fa-user-check"></i>
                    <p>Aucun client converti pour le moment.</p>
                </div>
            @else
                <div class="table-wrap">
                    <div class="table-head" style="grid-template-columns:1.5fr 1.2fr .8fr .7fr .7fr 52px;">
                        <div>Client</div>
                        <div>Entreprise</div>
                        <div>Date conversion</div>
                        <div>Projets</div>
                        <div>Dernier suivi</div>
                        <div></div>
                    </div>

                    @foreach($clients as $client)
                        <div class="table-row" style="grid-template-columns:1.5fr 1.2fr .8fr .7fr .7fr 52px;cursor:pointer;" onclick="window.location='{{ route('commercial.prospects.show', $client) }}'">
                            <div class="client">
                                <div class="client-avatar client-avatar-is-client">{{ strtoupper(substr($client->name, 0, 2)) }}</div>
                                <div>
                                    <div class="client-name client-name-is-client">{{ $client->name }}</div>
                                    <div class="col-meta">{{ $client->phone }}</div>
                                </div>
                            </div>
                            <div class="col-meta" data-label="Entreprise">{{ $client->company_name ?? '—' }}</div>
                            <div data-label="Date conversion">{{ $client->updated_at->format('d/m/Y') }}</div>
                            <div class="col-info" data-label="Projets">{{ $client->projects_count }}</div>
                            <div class="date" data-label="Dernier suivi">{{ $client->last_activity_at?->format('d/m/Y') ?? '—' }}</div>
                            <a href="{{ route('commercial.prospects.show', $client) }}" class="open" onclick="event.stopPropagation();">↗</a>
                        </div>
                    @endforeach
                </div>

                <div style="padding:0 22px;">
                    {{ $clients->links() }}
                </div>
            @endif
        </div>
    </section>

</x-layouts.admin>