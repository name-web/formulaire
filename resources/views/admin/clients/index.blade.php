<x-layouts.admin :title="'Clients'">

    <section class="intro">
        <div>
            <div class="intro-label">
                <span></span>
                Gestion
            </div>
            <h1>Clients</h1>
            <p>Tous les clients ayant soumis un projet</p>
        </div>
    </section>

    {{-- Search --}}
    <section class="stats" style="grid-template-columns:1fr;min-height:auto;">
        <div class="stat" style="border-right:0;min-height:auto;">
            <form method="GET" class="search-form" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:250px;">
                    <label class="admin-label">Recherche</label>
                    <input type="text" name="search" class="admin-input" placeholder="Rechercher par nom, email, entreprise..." value="{{ request('search') }}">
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="submit" class="admin-btn btn-primary"><i class="fa-solid fa-search"></i> Rechercher</button>
                    @if(request('search'))
                        <a href="{{ route('admin.clients.index') }}" class="admin-btn btn-secondary"><i class="fa-solid fa-xmark"></i></a>
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
                    <div class="card-title">Tous les clients</div>
                    <div class="card-subtitle">Liste complète des clients</div>
                </div>
            </div>

            @if($clients->isEmpty())
                <div class="empty-state">
                    <i class="fa-regular fa-user"></i>
                    <p>Aucun client trouvé.</p>
                </div>
            @else
                <div class="table-wrap">
                <div class="table-head" style="grid-template-columns:1.3fr 1.3fr 1fr 1fr .6fr .8fr 52px;">
                    <div>Nom</div>
                    <div>Email</div>
                    <div>Entreprise</div>
                    <div>Localisation</div>
                    <div>Projets</div>
                    <div>Inscrit le</div>
                    <div></div>
                </div>

                @foreach($clients as $client)
                    <div class="table-row" style="grid-template-columns:1.3fr 1.3fr 1fr 1fr .6fr .8fr 52px;cursor:pointer;" onclick="window.location='{{ route('admin.clients.show', $client) }}'">
                        <div class="client">
                            <div class="client-avatar">{{ strtoupper(substr($client->name, 0, 2)) }}</div>
                            <div>
                                <div class="client-name">{{ $client->name }}</div>
                            </div>
                        </div>
                        <div class="project">{{ $client->email }}</div>
                        <div class="project">{{ $client->company_name ?? '—' }}</div>
                        <div class="date">{{ $client->city }}{{ $client->city && $client->country ? ', ' : '' }}{{ $client->country }}</div>
                        <div><span class="admin-badge badge-in_progress">{{ $client->projects_count }}</span></div>
                        <div class="date">{{ $client->created_at->format('d/m/Y') }}</div>
                        <a href="{{ route('admin.clients.show', $client) }}" class="open" onclick="event.stopPropagation();">↗</a>
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
