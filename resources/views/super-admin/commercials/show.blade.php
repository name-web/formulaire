<x-layouts.admin :title="'Commercial — ' . $commercial->name">

    <section class="intro">
        <div>
            <div class="intro-label">
                <span></span>
                Super Admin
            </div>
            <h1>{{ $commercial->name }}</h1>
            <p>{{ $commercial->email }} — {{ $prospects->count() }} prospect{{ $prospects->count() > 1 ? 's' : '' }}, {{ $clients->count() }} client{{ $clients->count() > 1 ? 's' : '' }}</p>
        </div>
        <a href="{{ route('super_admin.commercials.index') }}" class="top-btn"><i class="fa-solid fa-arrow-left" style="margin-right:4px;"></i> Commerciaux</a>
    </section>

    <section class="stats" style="grid-template-columns:repeat(3,1fr);">
        <article class="stat">
            <div class="stat-label">Prospects</div>
            <div class="stat-number">{{ $prospects->count() }}</div>
        </article>
        <article class="stat">
            <div class="stat-label">Clients</div>
            <div class="stat-number">{{ $clients->count() }}</div>
        </article>
        <article class="stat main-stat">
            <div class="stat-label">Retards</div>
            <div class="stat-number">{{ $prospects->filter(fn ($p) => $p->isFollowUpOverdue())->count() }}</div>
        </article>
    </section>

    <section class="content" style="grid-template-columns:1fr;">
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Prospects assignés</div>
                    <div class="card-subtitle">{{ $prospects->count() }} prospect{{ $prospects->count() > 1 ? 's' : '' }}</div>
                </div>
            </div>

            @if($prospects->isEmpty())
                <div class="empty-state">
                    <i class="fa-regular fa-handshake"></i>
                    <p>Aucun prospect n'est assigné à ce commercial.</p>
                </div>
            @else
                <div class="table-wrap">
                    <div class="table-head" style="grid-template-columns:1.5fr 1fr .8fr .7fr .7fr 52px;">
                        <div>Prospect</div>
                        <div>Entreprise</div>
                        <div>Statut</div>
                        <div>Dernier suivi</div>
                        <div>Prochaine action</div>
                        <div></div>
                    </div>

                    @foreach($prospects as $prospect)
                        <div class="table-row" style="grid-template-columns:1.5fr 1fr .8fr .7fr .7fr 52px;">
                            <div class="client">
                                <div class="client-avatar {{ $prospect->isClient() ? 'client-avatar-is-client' : '' }}">{{ strtoupper(substr($prospect->name, 0, 2)) }}</div>
                                <div>
                                    <div class="client-name {{ $prospect->isClient() ? 'client-name-is-client' : '' }}">{{ $prospect->name }}</div>
                                    <div class="col-meta">{{ $prospect->phone }}</div>
                                </div>
                            </div>
                            <div class="col-meta" data-label="Entreprise">{{ $prospect->company_name ?? '—' }}</div>
                            <div data-label="Statut">
                                @if($prospect->isClient())
                                    <span class="admin-badge badge-client">Client</span>
                                @else
                                    <span class="admin-badge badge-pending">Prospect</span>
                                @endif
                            </div>
                            <div class="date" data-label="Dernier suivi">{{ $prospect->last_activity_at?->format('d/m/Y') ?? '—' }}</div>
                            <div class="date" data-label="Prochaine action">
                                @if($prospect->isFollowUpOverdue())
                                    <span style="color:#dc2626;font-weight:600;">{{ $prospect->next_action_date->format('d/m/Y') }}</span>
                                @elseif($prospect->isFollowUpDueToday())
                                    <span style="color:var(--yellow);font-weight:600;">Aujourd'hui</span>
                                @elseif($prospect->hasFollowUp())
                                    {{ $prospect->next_action_date->format('d/m/Y') }}
                                @else
                                    —
                                @endif
                            </div>
                            <a href="{{ route('super_admin.prospects.show', $prospect) }}" class="open">↗</a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Recent comments --}}
    <section class="content" style="grid-template-columns:1fr;">
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Activité récente</div>
                    <div class="card-subtitle">Derniers suivis enregistrés</div>
                </div>
            </div>

            @if($comments->isEmpty())
                <div class="empty-state">
                    <i class="fa-regular fa-comment-dots"></i>
                    <p>Aucun suivi enregistré.</p>
                </div>
            @else
                <div class="table-wrap">
                    <div class="table-head" style="grid-template-columns:1.2fr 2.4fr .8fr;">
                        <div>Prospect</div>
                        <div>Suivi</div>
                        <div>Date</div>
                    </div>

                    @foreach($comments as $comment)
                        <div class="table-row" style="grid-template-columns:1.2fr 2.4fr .8fr;">
                            <div class="col-info" data-label="Prospect">
                                @if($comment->client)
                                    <a href="{{ route('super_admin.prospects.show', $comment->client) }}" style="font-weight:700;">{{ $comment->client->name }}</a>
                                @else
                                    —
                                @endif
                            </div>
                            <div class="col-meta" data-label="Suivi">{{ Str::limit($comment->content, 110) }}</div>
                            <div class="date">{{ $comment->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

</x-layouts.admin>
