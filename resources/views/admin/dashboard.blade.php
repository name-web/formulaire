<x-layouts.admin :title="'Dashboard'">
    <div class="admin-header">
        <div>
            <h1 class="admin-title">Dashboard</h1>
            <p class="admin-subtitle">Vue d'ensemble de votre activité</p>
        </div>
    </div>

    {{-- Stats cards - dynamically built from config --}}
    @php
        $statCards = [];
        foreach(config('dashboard.stat_cards') as $cardConfig) {
            if (($stats[$cardConfig['key']] ?? 0) > 0) {
                $statCards[] = [
                    'icon'  => $cardConfig['icon'],
                    'color' => $cardConfig['color'],
                    'bg'    => $cardConfig['bg'],
                    'value' => $stats[$cardConfig['key']],
                    'label' => $cardConfig['label'],
                ];
            }
        }
    @endphp

    @if(count($statCards) > 0)
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:16px; margin-bottom:28px;">
            @foreach($statCards as $card)
                <div class="stat-card" style="transition:transform 0.2s,box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 30px rgba(0,0,0,0.2)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                    <div class="stat-icon" style="background:{{ $card['bg'] }};color:{{ $card['color'] }};"><i class="{{ $card['icon'] }}"></i></div>
                    <div class="stat-value">{{ $card['value'] }}</div>
                    <div class="stat-label">{{ $card['label'] }}</div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Charts row: Status breakdown + Type breakdown + Monthly submissions --}}
    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px; margin-bottom:24px;">
        {{-- Status Breakdown --}}
        <div class="admin-card">
            <h3 style="font-size:14px;font-weight:800;color:white;margin-bottom:18px;">
                <i class="fa-solid fa-chart-pie" style="color:var(--blue-light);margin-right:8px;"></i>
                Répartition par statut
            </h3>
            @php $hasStatusData = $projectsByStatus->some(fn($d) => $d['count'] > 0); @endphp
            @if(!$hasStatusData)
                <p style="color:var(--text-muted);font-size:12px;text-align:center;padding:24px 0;">Aucune donnée disponible.</p>
            @else
                <div style="display:flex;flex-direction:column;gap:12px;">
                    @foreach($projectsByStatus as $status => $data)
                        @if($data['count'] > 0)
                            @php
                                $total = max($stats['total_projects'], 1);
                                $pct = round(($data['count'] / $total) * 100);
                            @endphp
                            <div>
                                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px;">
                                    <span style="font-size:12px;color:rgba(255,255,255,0.7);display:flex;align-items:center;gap:6px;">
                                        <i class="fa-solid {{ $data['icon'] }}" style="font-size:10px;"></i>
                                        {{ $data['label'] }}
                                    </span>
                                    <span style="font-size:11px;color:var(--blue-light);font-weight:700;">{{ $data['count'] }}</span>
                                </div>
                                <div style="height:5px;border-radius:20px;background:rgba(255,255,255,0.06);overflow:hidden;">
                                    <div style="height:100%;border-radius:20px;background:linear-gradient(90deg,#e6a400,#ffc72c);width:{{ $pct }}%;transition:width 0.6s ease;"></div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Type Breakdown --}}
        <div class="admin-card">
            <h3 style="font-size:14px;font-weight:800;color:white;margin-bottom:18px;">
                <i class="fa-solid fa-tags" style="color:var(--blue-light);margin-right:8px;"></i>
                Répartition par type
            </h3>
            @if($projectsByType->isEmpty())
                <p style="color:var(--text-muted);font-size:12px;text-align:center;padding:24px 0;">Aucune donnée disponible.</p>
            @else
                <div style="display:flex;flex-direction:column;gap:12px;">
                    @foreach($projectsByType as $type => $data)
                        @php
                            $total = max($stats['total_projects'], 1);
                            $pct = round(($data['count'] / $total) * 100);
                        @endphp
                        <div>
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px;">
                                <span style="font-size:12px;color:rgba(255,255,255,0.7);display:flex;align-items:center;gap:6px;">
                                    <i class="{{ $data['icon'] }}" style="color:{{ $data['color'] }};font-size:10px;"></i>
                                    {{ $data['label'] }}
                                </span>
                                <span style="font-size:11px;color:var(--blue-light);font-weight:700;">{{ $data['count'] }}</span>
                            </div>
                            <div style="height:5px;border-radius:20px;background:rgba(255,255,255,0.06);overflow:hidden;">
                                <div style="height:100%;border-radius:20px;background:linear-gradient(90deg,{{ $data['color'] }},{{ $data['color'] }}88);width:{{ $pct }}%;transition:width 0.6s ease;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Monthly Submissions --}}
        <div class="admin-card">
            <h3 style="font-size:14px;font-weight:800;color:white;margin-bottom:18px;">
                <i class="fa-solid fa-chart-bar" style="color:var(--blue-light);margin-right:8px;"></i>
                Soumissions mensuelles
            </h3>
            @if($submissionsByMonth->isEmpty())
                <p style="color:var(--text-muted);font-size:12px;text-align:center;padding:24px 0;">Aucune donnée disponible.</p>
            @else
                @php
                    $maxMonth = max($submissionsByMonth->values()->toArray());
                @endphp
                <div style="display:flex;align-items:flex-end;gap:8px;height:120px;padding-top:10px;">
                    @foreach($submissionsByMonth as $month => $count)
                        @php
                            $barHeight = $maxMonth > 0 ? max(($count / $maxMonth) * 100, 8) : 8;
                        @endphp
                        <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;">
                            <span style="font-size:10px;color:var(--blue-light);font-weight:700;">{{ $count }}</span>
                            <div style="width:100%;height:{{ $barHeight }}%;border-radius:6px 6px 0 0;background:linear-gradient(180deg,#ffb703,#e6a400);transition:height 0.5s ease;"></div>
                            <span style="font-size:9px;color:var(--text-muted);text-transform:uppercase;">{{ $month }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Recent projects + Recent clients --}}
    <div style="display:grid; grid-template-columns:2fr 1fr; gap:24px;">
        <div class="admin-card">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                <h3 style="font-size:14px;font-weight:800;color:white;">Derniers projets</h3>
                <a href="{{ route('admin.projects.index') }}" class="admin-btn btn-secondary btn-sm">Voir tout</a>
            </div>
            @if($recentProjects->isEmpty())
                <div style="text-align:center;padding:32px 0;">
                    <i class="fa-solid fa-folder-open" style="font-size:28px;color:var(--text-muted);margin-bottom:10px;display:block;"></i>
                    <p style="color:var(--text-muted);font-size:12px;">Aucun projet pour le moment.</p>
                </div>
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
                            <tr style="cursor:pointer;" onclick="window.location='{{ route('admin.projects.show', $project) }}'">
                                <td>
                                    <span style="color:rgba(255,255,255,0.85);font-weight:600;">
                                        {{ $project->client->name ?? '—' }}
                                    </span>
                                </td>
                                <td>
                                    @php $typeLabels = $project->type_labels; @endphp
                                    <span style="font-size:11px;color:var(--text-secondary);">{{ $typeLabels ?: '—' }}</span>
                                </td>
                                <td>
                                    @php $sc = \App\Models\Project::getStatusConfig($project->status); @endphp
                                    <span class="admin-badge {{ $sc['class'] }}">{{ $sc['label'] }}</span>
                                </td>
                                <td style="font-size:11px;color:var(--text-muted);">{{ $project->created_at->format('d/m/Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="admin-card">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                <h3 style="font-size:14px;font-weight:800;color:white;">Derniers clients</h3>
                <a href="{{ route('admin.clients.index') }}" class="admin-btn btn-secondary btn-sm">Voir tout</a>
            </div>
            @if($recentClients->isEmpty())
                <div style="text-align:center;padding:32px 0;">
                    <i class="fa-solid fa-users" style="font-size:28px;color:var(--text-muted);margin-bottom:10px;display:block;"></i>
                    <p style="color:var(--text-muted);font-size:12px;">Aucun client pour le moment.</p>
                </div>
            @else
                <div style="display:flex;flex-direction:column;gap:8px;">
                    @foreach($recentClients as $client)
                        <a href="{{ route('admin.clients.show', $client) }}" style="display:flex;align-items:center;gap:12px;padding:12px;border-radius:12px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);text-decoration:none;transition:all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.06)'" onmouseout="this.style.background='rgba(255,255,255,0.03)'">
                            <div style="width:36px;height:36px;border-radius:10px;background:rgba(139,92,246,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:13px;color:#a78bfa;font-weight:800;">
                                {{ strtoupper(substr($client->name, 0, 1)) }}
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:13px;font-weight:700;color:rgba(255,255,255,0.85);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $client->name }}</div>
                                <div style="font-size:11px;color:var(--text-muted);">{{ $client->email }}</div>
                            </div>
                            <div style="text-align:right;">
                                <div style="font-size:11px;color:var(--text-muted);">{{ $client->projects_count }} projet{{ $client->projects_count > 1 ? 's' : '' }}</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
