<x-layouts.admin :title="'Dashboard'">

    {{-- Intro --}}
    <section class="intro">
        <div>
            <div class="intro-label">
                <span></span>
                Vue générale
            </div>
            <h1>Tableau de bord</h1>
            <p>Suivez les demandes et les cahiers des charges.</p>
        </div>
        <div style="color:var(--muted);font-size:13px;">{{ now()->translatedFormat('l d M Y') }}</div>
    </section>

    {{-- Stats --}}
    @php
        $statCards = [];
        foreach(config('dashboard.stat_cards') as $cardConfig) {
            $statCards[] = [
                'icon'  => $cardConfig['icon'],
                'value' => $stats[$cardConfig['key']] ?? 0,
                'label' => $cardConfig['label'],
            ];
        }
        $total = $stats['total_projects'] ?? 0;
        $submitted = $stats['submitted_projects'] ?? 0;
        $pct = $total > 0 ? round(($submitted / $total) * 100) : 0;
    @endphp

    <section class="stats">
        @foreach($statCards as $i => $card)
            <article class="stat {{ $i === 0 ? 'main-stat' : '' }}">
                <div class="stat-label">{{ $card['label'] }}</div>
                <div class="stat-number">{{ $card['value'] }}</div>
                <div class="stat-info">
                    @if($i === 0 && $total > 0)
                        {{ $pct }}% finalisés
                    @elseif($i === 1)
                        Prêts pour développement
                    @elseif($i === 2)
                        À traiter
                    @else
                        Formulaires complets
                    @endif
                </div>
            </article>
        @endforeach
    </section>

    {{-- Content --}}
    <section class="content">

        {{-- Projects table --}}
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Dernières demandes</div>
                    <div class="card-subtitle">Les demandes les plus récentes</div>
                </div>
                <a href="{{ route('admin.projects.index') }}" class="view-all">Voir tout →</a>
            </div>

            <div class="table-head">
                <div>Client</div>
                <div>Projet</div>
                <div>Date</div>
                <div>Statut</div>
                <div></div>
            </div>

            @if($recentProjects->isEmpty())
                <div class="empty-state">
                    <i class="fa-regular fa-folder-open"></i>
                    <p>Aucune demande pour le moment.</p>
                </div>
            @else
                @foreach($recentProjects as $project)
                    <div class="table-row" style="cursor:pointer;" onclick="window.location='{{ route('admin.projects.show', $project) }}'">
                        <div class="client">
                            <div class="client-avatar">{{ strtoupper(substr($project->client->name ?? '?', 0, 2)) }}</div>
                            <div>
                                <div class="client-name">{{ $project->client->name ?? '—' }}</div>
                                <div class="client-email">{{ $project->client->email ?? '' }}</div>
                            </div>
                        </div>
                        <div class="project">{{ $project->type_labels ?: '—' }}</div>
                        <div class="date">{{ $project->created_at->format('d M') }}</div>
                        <div>
                            @php $sc = \App\Models\Project::getStatusConfig($project->status); @endphp
                            <span class="status {{ in_array($project->status, ['completed','submitted']) ? 'done' : '' }}">{{ $sc['label'] }}</span>
                        </div>
                        <a href="{{ route('admin.projects.show', $project) }}" class="open">↗</a>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- Right panel --}}
        <aside class="card">
            {{-- Progression --}}
            <div class="side-section">
                <div class="side-heading">Progression</div>
                <div class="completion-number">
                    <strong>{{ $pct }}%</strong>
                    <span>{{ $submitted }}/{{ $total }} demandes</span>
                </div>
                <div class="progress"><span style="width:{{ $pct }}%"></span></div>
            </div>

            {{-- Clients --}}
            <div class="side-section">
                <div class="side-heading">Clients récents</div>
                @if($recentClients->isEmpty())
                    <p style="color:var(--muted);font-size:13px;">Aucun client.</p>
                @else
                    @foreach($recentClients as $client)
                        <a href="{{ route('admin.clients.show', $client) }}" class="activity" style="text-decoration:none;">
                            <div class="activity-dot"></div>
                            <div>
                                <div class="activity-title">{{ $client->name }}</div>
                                <div class="activity-time">{{ $client->email }}</div>
                            </div>
                        </a>
                    @endforeach
                @endif
            </div>

            {{-- Action --}}
            <div class="side-section">
                <a href="{{ route('admin.projects.index') }}" class="quick-action">+ Voir tous les projets</a>
            </div>
        </aside>

    </section>

</x-layouts.admin>
