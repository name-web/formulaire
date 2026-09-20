@php
    $prospectRoute = $prospectRoute ?? 'commercial.prospects.show';

    $boardColumns = [
        [
            'key' => 'dueToday',
            'title' => "Aujourd'hui",
            'icon' => 'fa-solid fa-bell',
            'badge' => 'badge-pending',
            'dot' => 'var(--yellow)',
            'empty' => "Aucun rappel prévu aujourd'hui.",
        ],
        [
            'key' => 'overdue',
            'title' => 'En retard',
            'icon' => 'fa-solid fa-triangle-exclamation',
            'badge' => 'badge-submitted',
            'dot' => '#dc2626',
            'empty' => 'Aucun rappel en retard.',
        ],
        [
            'key' => 'inProgress',
            'title' => 'Mes prospects',
            'icon' => 'fa-solid fa-handshake',
            'badge' => 'badge-in_progress',
            'dot' => 'var(--text)',
            'empty' => 'Aucun prospect assigné.',
        ],
        [
            'key' => 'converted',
            'title' => 'Convertis',
            'icon' => 'fa-solid fa-user-check',
            'badge' => 'badge-client',
            'dot' => '#176139',
            'empty' => 'Aucune conversion pour le moment.',
        ],
    ];
@endphp

<div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;margin-top:18px;">

    @foreach($boardColumns as $column)

        @php $items = $board[$column['key']]; @endphp

        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">
                        <i class="{{ $column['icon'] }}" style="color:var(--yellow);margin-right:6px;"></i>
                        {{ $column['title'] }}
                    </div>
                    <div class="card-subtitle">
                        {{ $items->count() }} prospect{{ $items->count() > 1 ? 's' : '' }}
                    </div>
                </div>
                <span class="admin-badge {{ $column['badge'] }}">{{ $items->count() }}</span>
            </div>

            @if($items->isEmpty())
                <div class="empty-state" style="padding:28px 22px;">
                    <i class="fa-regular fa-circle-check"></i>
                    <p>{{ $column['empty'] }}</p>
                </div>
            @else
                <div class="side-section">
                    @foreach($items as $prospect)
                        <a href="{{ route($prospectRoute, $prospect) }}" class="activity" style="text-decoration:none;">
                            <div class="activity-dot" style="background:{{ $column['dot'] }};"></div>
                            <div style="min-width:0;">
                                <div class="activity-title {{ $prospect->isClient() ? 'client-name-is-client' : '' }}">
                                    {{ $prospect->name }}
                                </div>
                                <div class="activity-time">
                                    {{ $prospect->next_action ? $prospect->next_action.' · ' : '' }}{{ $prospect->next_action_date?->format('d/m/Y') ?? 'Aucune date de suivi' }}
                                    · Dernier suivi : {{ $prospect->last_activity_at?->format('d/m/Y') ?? '—' }}
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

    @endforeach

</div>
