<x-layouts.admin :title="'Super Admin'">

    {{-- Intro --}}
    <section class="intro">
        <div>
            <div class="intro-label">
                <span></span>
                Espace privé
            </div>
            <h1>Super Admin</h1>
            <p>Vision globale des prospects, des développeurs et des suivis.</p>
        </div>
        <div style="color:var(--muted);font-size:13px;">{{ now()->translatedFormat('l d M Y') }}</div>
    </section>

    {{-- Global stats --}}
    <section class="stats" style="grid-template-columns:repeat(4,1fr);">
        @foreach([
            ['label' => 'Développeurs', 'value' => $stats['developers'], 'info' => 'Comptes actifs'],
            ['label' => 'Prospects', 'value' => $stats['prospects'], 'info' => 'En cours de suivi'],
            ['label' => 'Clients', 'value' => $stats['clients'], 'info' => 'Prospects convertis'],
            ['label' => 'Sans développeur', 'value' => $stats['unassigned_prospects'], 'info' => 'À attribuer'],
        ] as $i => $card)
            <article class="stat {{ $i === 0 ? 'main-stat' : '' }}">
                <div class="stat-label">{{ $card['label'] }}</div>
                <div class="stat-number">{{ $card['value'] }}</div>
                <div class="stat-info">{{ $card['info'] }}</div>
            </article>
        @endforeach
    </section>

    <section class="stats" style="grid-template-columns:repeat(3,1fr);margin-top:18px;">
        <article class="stat">
            <div class="stat-label">Suivis aujourd'hui</div>
            <div class="stat-number">{{ $stats['due_today'] }}</div>
            <div class="stat-info">Actions prévues ce jour</div>
        </article>
        <article class="stat">
            <div class="stat-label">Suivis en retard</div>
            <div class="stat-number">{{ $stats['overdue'] }}</div>
            <div class="stat-info">Date de suivi dépassée</div>
        </article>
        <article class="stat">
            <div class="stat-label">Commentaires</div>
            <div class="stat-number">{{ $stats['comments'] }}</div>
            <div class="stat-info">Total des suivis enregistrés</div>
        </article>
    </section>

    {{-- Content --}}
    <section class="content">

        <div style="display:flex;flex-direction:column;gap:18px;">

            {{-- Developers --}}
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title">Développeurs</div>
                        <div class="card-subtitle">Charge de suivi par développeur</div>
                    </div>
                    <a href="{{ route('super_admin.developers.index') }}" class="view-all">Gérer →</a>
                </div>

                @if($developers->isEmpty())
                    <div class="empty-state">
                        <i class="fa-regular fa-laptop-code"></i>
                        <p>Aucun développeur créé pour le moment.</p>
                    </div>
                @else
                    <div class="table-wrap">
                        <div class="table-head" style="grid-template-columns:1.4fr .8fr .8fr .8fr 52px;">
                            <div>Développeur</div>
                            <div>Prospects</div>
                            <div>Clients</div>
                            <div>En retard</div>
                            <div></div>
                        </div>

                        @foreach($developers as $developer)
                            <div class="table-row" style="grid-template-columns:1.4fr .8fr .8fr .8fr 52px;cursor:pointer;" onclick="window.location='{{ route('super_admin.developers.show', $developer) }}'">
                                <div class="client">
                                    <div class="client-avatar">{{ strtoupper(substr($developer->name, 0, 2)) }}</div>
                                    <div>
                                        <div class="client-name">{{ $developer->name }}</div>
                                    </div>
                                </div>
                                <div class="col-info" data-label="Prospects">
                                    <span class="admin-badge badge-in_progress">{{ $developer->prospects_count }}</span>
                                </div>
                                <div class="col-info" data-label="Clients">
                                    <span class="admin-badge badge-client">{{ $developer->clients_count }}</span>
                                </div>
                                <div class="col-info" data-label="En retard">
                                    <span class="admin-badge {{ $developer->overdue_count > 0 ? 'badge-submitted' : 'badge-user' }}">{{ $developer->overdue_count }}</span>
                                </div>
                                <a href="{{ route('super_admin.developers.show', $developer) }}" class="open" onclick="event.stopPropagation();">↗</a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Prochaines actions --}}
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title">Prochaines actions</div>
                        <div class="card-subtitle">Suivis planifiés, tous développeurs confondus</div>
                    </div>
                    <a href="{{ route('super_admin.prospects.index') }}" class="view-all">Attribuer →</a>
                </div>

                @if($upcomingFollowUps->isEmpty())
                    <div class="empty-state">
                        <i class="fa-regular fa-calendar"></i>
                        <p>Aucune prochaine action planifiée.</p>
                    </div>
                @else
                    <div class="table-wrap">
                        <div class="table-head" style="grid-template-columns:1.4fr 1.1fr 1fr .8fr;">
                            <div>Prospect</div>
                            <div>Action</div>
                            <div>Développeur</div>
                            <div>Date</div>
                        </div>

                        @foreach($upcomingFollowUps as $prospect)
                            <div class="table-row" style="grid-template-columns:1.4fr 1.1fr 1fr .8fr;">
                                <div class="client">
                                    <div class="client-avatar">{{ strtoupper(substr($prospect->name, 0, 2)) }}</div>
                                    <div>
                                        <div class="client-name">{{ $prospect->name }}</div>
                                    </div>
                                </div>
                                <div class="col-info" data-label="Action">{{ $prospect->next_action ?? '—' }}</div>
                                <div class="col-meta" data-label="Développeur">{{ $prospect->assignedDeveloper->name ?? 'Non attribué' }}</div>
                                <div>
                                    <span class="admin-badge {{ $prospect->isFollowUpOverdue() ? 'badge-submitted' : 'badge-pending' }}">
                                        {{ $prospect->next_action_date->format('d/m/Y') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

        {{-- Right column --}}
        <aside style="display:flex;flex-direction:column;gap:18px;">

            <div class="card">
                <div class="side-section">
                    <div class="side-heading">Prospects non attribués</div>
                    @if($unassignedProspects->isEmpty())
                        <p style="color:var(--muted);font-size:13px;">Tous les prospects sont attribués.</p>
                    @else
                        @foreach($unassignedProspects as $prospect)
                            <a href="{{ route('super_admin.prospects.show', $prospect) }}" class="activity" style="text-decoration:none;">
                                <div class="activity-dot"></div>
                                <div>
                                    <div class="activity-title">{{ $prospect->name }}</div>
                                    <div class="activity-time">{{ $prospect->company_name ?? $prospect->phone ?? '—' }}</div>
                                </div>
                            </a>
                        @endforeach
                    @endif
                </div>

                <div class="side-section">
                    <div class="side-heading">Conversions récentes</div>
                    @if($recentConversions->isEmpty())
                        <p style="color:var(--muted);font-size:13px;">Aucune conversion pour le moment.</p>
                    @else
                        @foreach($recentConversions as $client)
                            <a href="{{ route('super_admin.prospects.show', $client) }}" class="activity" style="text-decoration:none;">
                                <div class="activity-dot" style="background:#176139;"></div>
                                <div>
                                    <div class="activity-title client-name-is-client">{{ $client->name }}</div>
                                    <div class="activity-time">{{ $client->assignedDeveloper->name ?? 'Non attribué' }}</div>
                                </div>
                            </a>
                        @endforeach
                    @endif
                </div>

                <div class="side-section">
                    <a href="{{ route('admin.dashboard') }}" class="quick-action">+ Dashboard général</a>
                </div>
            </div>

            <div class="card">
                <div class="side-section">
                    <div class="side-heading">Derniers suivis</div>
                    @if($recentComments->isEmpty())
                        <p style="color:var(--muted);font-size:13px;">Aucun commentaire enregistré.</p>
                    @else
                        @foreach($recentComments as $comment)
                            <a href="{{ route('super_admin.prospects.show', $comment->client) }}" class="activity" style="text-decoration:none;">
                                <div class="activity-dot"></div>
                                <div style="min-width:0;">
                                    <div class="activity-title">{{ $comment->user->name ?? '—' }} · {{ $comment->client->name ?? '—' }}</div>
                                    <div class="activity-time">{{ Str::limit($comment->content, 60) }} — {{ $comment->created_at->format('d/m/Y H:i') }}</div>
                                </div>
                            </a>
                        @endforeach
                    @endif
                </div>
            </div>

        </aside>

    </section>

</x-layouts.admin>
