<x-layouts.admin :title="'Dashboard'">
    <div class="admin-header">
        <div>
            <h1 class="admin-title">Dashboard</h1>
            <p class="admin-subtitle">Vue d'ensemble de votre activité</p>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:20px; margin-bottom:32px;">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(59,130,246,0.12);color:#3b82f6;"><i class="fa-solid fa-folder-open"></i></div>
            <div class="stat-value">{{ $stats['total_projects'] }}</div>
            <div class="stat-label">Projets totaux</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(245,158,11,0.12);color:#fbbf24;"><i class="fa-solid fa-clock"></i></div>
            <div class="stat-value">{{ $stats['pending_projects'] }}</div>
            <div class="stat-label">En attente</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(59,130,246,0.12);color:#60a5fa;"><i class="fa-solid fa-spinner"></i></div>
            <div class="stat-value">{{ $stats['in_progress_projects'] }}</div>
            <div class="stat-label">En cours</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(16,185,129,0.12);color:#34d399;"><i class="fa-solid fa-check-circle"></i></div>
            <div class="stat-value">{{ $stats['completed_projects'] }}</div>
            <div class="stat-label">Terminés</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(139,92,246,0.12);color:#a78bfa;"><i class="fa-solid fa-users"></i></div>
            <div class="stat-value">{{ $stats['total_clients'] }}</div>
            <div class="stat-label">Clients</div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:2fr 1fr; gap:24px;">
        <div class="admin-card">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
                <h3 style="font-size:15px;font-weight:800;color:white;">Derniers projets</h3>
                <a href="{{ route('admin.projects.index') }}" class="admin-btn btn-secondary btn-sm">Voir tout</a>
            </div>
            @if($recentProjects->isEmpty())
                <p style="color:var(--text-muted);font-size:13px;text-align:center;padding:30px 0;">Aucun projet pour le moment.</p>
            @else
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Type</th>
                            <th>Statut</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentProjects as $project)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.projects.show', $project) }}" style="color:rgba(255,255,255,0.85);text-decoration:none;font-weight:600;">
                                        {{ $project->client->name ?? '—' }}
                                    </a>
                                </td>
                                <td>
                                    @php
                                        $types = is_array($project->project_type) ? $project->project_type : [$project->project_type];
                                        $typeLabels = collect($types)->map(fn($t) => config("project_types.types.{$t}.label", $t))->implode(', ');
                                    @endphp
                                    <span style="font-size:12px;color:var(--text-secondary);">{{ $typeLabels ?: '—' }}</span>
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
                                <td style="font-size:12px;color:var(--text-muted);">{{ $project->created_at->format('d/m/Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="admin-card">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
                <h3 style="font-size:15px;font-weight:800;color:white;">Répartition par type</h3>
            </div>
            @if($projectsByType->isEmpty())
                <p style="color:var(--text-muted);font-size:13px;text-align:center;padding:30px 0;">Aucune donnée.</p>
            @else
                <div style="display:flex;flex-direction:column;gap:14px;">
                    @foreach($projectsByType as $type => $data)
                        @php
                            $total = $stats['total_projects'] ?: 1;
                            $pct = round(($data['count'] / $total) * 100);
                        @endphp
                        <div>
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                                <span style="font-size:13px;color:rgba(255,255,255,0.75);">{{ $data['label'] }}</span>
                                <span style="font-size:12px;color:var(--blue-light);font-weight:700;">{{ $data['count'] }}</span>
                            </div>
                            <div style="height:6px;border-radius:20px;background:rgba(255,255,255,0.06);overflow:hidden;">
                                <div style="height:100%;border-radius:20px;background:linear-gradient(90deg,#2563eb,#60a5fa);width:{{ $pct }}%;transition:width 0.6s;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="admin-card" style="margin-top:24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
            <h3 style="font-size:15px;font-weight:800;color:white;">Derniers clients</h3>
            <a href="{{ route('admin.clients.index') }}" class="admin-btn btn-secondary btn-sm">Voir tout</a>
        </div>
        @if($recentClients->isEmpty())
            <p style="color:var(--text-muted);font-size:13px;text-align:center;padding:30px 0;">Aucun client pour le moment.</p>
        @else
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Entreprise</th>
                        <th>Projets</th>
                        <th>Inscrit le</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentClients as $client)
                        <tr>
                            <td>
                                <a href="{{ route('admin.clients.show', $client) }}" style="color:rgba(255,255,255,0.85);text-decoration:none;font-weight:600;">
                                    {{ $client->name }}
                                </a>
                            </td>
                            <td style="color:var(--text-secondary);">{{ $client->email }}</td>
                            <td style="color:var(--text-secondary);">{{ $client->company_name ?? '—' }}</td>
                            <td><span class="admin-badge badge-in_progress">{{ $client->projects_count }}</span></td>
                            <td style="font-size:12px;color:var(--text-muted);">{{ $client->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-layouts.admin>
