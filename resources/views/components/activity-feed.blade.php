@props([
    'activities',
    'authors',
    'indexRoute',
    'context' => 'admin',
])

@php
    /*
     * Le même journal est affiché dans deux espaces privés : seules les
     * destinations des liens changent. L'espace Super Admin reste dans son
     * espace, l'espace Admin dans le dashboard général.
     */
    $clientUrl = function (\App\Models\ClientActivity $activity) use ($context): ?string {
        $client = $activity->client;

        if ($client === null) {
            return null;
        }

        if ($context === 'super_admin') {
            return route('super_admin.prospects.show', $client);
        }

        return $client->isClient()
            ? route('admin.client-clients.show', $client)
            : route('admin.clients.show', $client);
    };

    $hasFilters = request()->hasAny(['search', 'type', 'group', 'user_id', 'from', 'to']);
@endphp

{{-- Filtres --}}
<section class="stats" style="grid-template-columns:1fr;min-height:auto;">
    <div class="stat" style="border-right:0;min-height:auto;">
        <form method="GET" class="search-form" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
            <div style="flex:1;min-width:0;">
                <label class="admin-label" for="activity-search">Recherche</label>
                <input type="text" id="activity-search" name="search" class="admin-input" placeholder="Rechercher par nom de prospect ou client..." value="{{ request('search') }}">
            </div>
            <div style="min-width:150px;">
                <label class="admin-label" for="activity-group">Famille</label>
                <select id="activity-group" name="group" class="admin-input">
                    <option value="">Toutes</option>
                    @foreach(\App\Models\ClientActivity::groups() as $group => $label)
                        <option value="{{ $group }}" {{ request('group') === $group ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width:180px;">
                <label class="admin-label" for="activity-type">Type</label>
                <select id="activity-type" name="type" class="admin-input">
                    <option value="">Tous</option>
                    @foreach(\App\Models\ClientActivity::typeConfigs() as $type => $config)
                        <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ $config['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width:170px;">
                <label class="admin-label" for="activity-author">Auteur</label>
                <select id="activity-author" name="user_id" class="admin-input">
                    <option value="">Tous</option>
                    @foreach($authors as $author)
                        <option value="{{ $author->id }}" {{ (string) request('user_id') === (string) $author->id ? 'selected' : '' }}>{{ $author->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width:140px;">
                <label class="admin-label" for="activity-from">Du</label>
                <input type="date" id="activity-from" name="from" class="admin-input" value="{{ request('from') }}">
            </div>
            <div style="min-width:140px;">
                <label class="admin-label" for="activity-to">Au</label>
                <input type="date" id="activity-to" name="to" class="admin-input" value="{{ request('to') }}">
            </div>
            <div style="display:flex;gap:8px;">
                <button type="submit" class="admin-btn btn-primary"><i class="fa-solid fa-search"></i> Filtrer</button>
                @if($hasFilters)
                    <a href="{{ route($indexRoute) }}" class="admin-btn btn-secondary" data-tooltip="Réinitialiser"><i class="fa-solid fa-xmark"></i></a>
                @endif
            </div>
        </form>
    </div>
</section>

{{-- Journal --}}
<section class="content" style="grid-template-columns:1fr;margin-top:18px;">
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">
                    <i class="fa-solid fa-clock-rotate-left" style="color:var(--yellow);margin-right:6px;"></i>
                    Journal d'activité
                </div>
                <div class="card-subtitle">{{ $activities->total() }} activité{{ $activities->total() > 1 ? 's' : '' }} enregistrée{{ $activities->total() > 1 ? 's' : '' }}</div>
            </div>
        </div>

        @if($activities->isEmpty())
            <div class="empty-state">
                <i class="fa-regular fa-clock"></i>
                <p>Aucune activité enregistrée pour ces critères.</p>
            </div>
        @else
            <div style="padding:0;">
                @foreach($activities as $activity)
                    @php
                        $url = $clientUrl($activity);
                        $isClient = $activity->client?->isClient() ?? false;
                    @endphp
                    <div class="activity-row" style="display:flex;gap:14px;padding:18px 22px;border-bottom:1px solid var(--line);{{ $url ? 'cursor:pointer;' : '' }}transition:background .15s ease;"
                        @if($url) onclick="window.location='{{ $url }}'" onmouseover="this.style.background='#fffdf3'" onmouseout="this.style.background=''" @endif>
                        <div style="flex-shrink:0;padding-top:2px;">
                            <div class="client-avatar {{ $isClient ? 'client-avatar-is-client' : '' }}" style="width:36px;height:36px;border-radius:50%;">
                                <span style="font-size:12px;font-weight:800;">{{ strtoupper(substr($activity->client->name ?? '?', 0, 2)) }}</span>
                            </div>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:4px;">
                                <span class="client-name {{ $isClient ? 'client-name-is-client' : '' }}" style="font-size:14px;font-weight:700;">{{ $activity->client->name ?? '—' }}</span>
                                <span class="admin-badge {{ $isClient ? 'badge-client' : 'badge-pending' }}" style="font-size:10px;padding:2px 8px;">{{ $isClient ? 'CLIENT' : 'PROSPECT' }}</span>
                                <span class="admin-badge {{ $activity->type_badge }}" style="font-size:10px;padding:2px 8px;">
                                    <i class="{{ $activity->type_icon }}" style="font-size:10px;"></i> {{ $activity->type_label }}
                                </span>
                                <span style="font-size:12px;color:var(--muted);">par {{ $activity->user->name ?? 'compte supprimé' }}</span>
                                <span style="font-size:12px;color:var(--muted);margin-left:auto;">{{ $activity->created_at->format('d/m/Y à H:i') }}</span>
                            </div>
                            <div style="font-size:13px;color:var(--text-secondary);line-height:1.6;overflow-wrap:break-word;">{{ $activity->description }}</div>
                        </div>
                        @if($url)
                            <div style="flex-shrink:0;display:flex;align-items:center;">
                                <span style="color:var(--muted);font-size:13px;">↗</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div style="padding:18px 22px;">
                {{ $activities->links() }}
            </div>
        @endif
    </div>
</section>
