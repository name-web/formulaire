<x-layouts.admin :title="'Commentaires'">

    <section class="intro">
        <div>
            <div class="intro-label">
                <span></span>
                Suivi commercial
            </div>
            <h1>Commentaires</h1>
            <p>Vue globale de tous les commentaires de suivi</p>
        </div>
    </section>

    {{-- Filters --}}
    <section class="stats" style="grid-template-columns:1fr;min-height:auto;">
        <div class="stat" style="border-right:0;min-height:auto;">
            <form method="GET" class="search-form" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:0;">
                    <label class="admin-label">Recherche</label>
                    <input type="text" name="search" class="admin-input" placeholder="Rechercher par nom de client..." value="{{ request('search') }}">
                </div>
                <div style="min-width:140px;">
                    <label class="admin-label">Type</label>
                    <select name="type" class="admin-input">
                        <option value="">Tous</option>
                        <option value="prospect" {{ request('type') === 'prospect' ? 'selected' : '' }}>Prospect</option>
                        <option value="client" {{ request('type') === 'client' ? 'selected' : '' }}>Client</option>
                    </select>
                </div>
                <div style="min-width:160px;">
                    <label class="admin-label">Auteur</label>
                    <select name="user_id" class="admin-input">
                        <option value="">Tous</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="min-width:140px;">
                    <label class="admin-label">Du</label>
                    <input type="date" name="from" class="admin-input" value="{{ request('from') }}">
                </div>
                <div style="min-width:140px;">
                    <label class="admin-label">Au</label>
                    <input type="date" name="to" class="admin-input" value="{{ request('to') }}">
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="submit" class="admin-btn btn-primary"><i class="fa-solid fa-search"></i> Filtrer</button>
                    @if(request()->hasAny(['search', 'type', 'user_id', 'from', 'to']))
                        <a href="{{ route('admin.comments.index') }}" class="admin-btn btn-secondary"><i class="fa-solid fa-xmark"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </section>

    {{-- Comments list --}}
    <section class="content" style="grid-template-columns:1fr;margin-top:18px;">
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">
                        <i class="fa-solid fa-comments" style="color:var(--yellow);margin-right:6px;"></i>
                        Tous les commentaires
                    </div>
                    <div class="card-subtitle">{{ $comments->total() }} commentaire{{ $comments->total() > 1 ? 's' : '' }}</div>
                </div>
            </div>

            @if($comments->isEmpty())
                <div class="empty-state">
                    <i class="fa-regular fa-comment-dots"></i>
                    <p>Aucun commentaire trouvé.</p>
                </div>
            @else
                <div style="padding:0;">
                    @foreach($comments as $comment)
                        @php
                            $isProspect = $comment->client->isProspect();
                        @endphp
                        <div style="display:flex;gap:14px;padding:18px 22px;border-bottom:1px solid var(--line);cursor:pointer;transition:background .15s ease;" onclick="window.location='{{ $isProspect ? route('admin.clients.show', $comment->client) : route('admin.client-clients.show', $comment->client) }}'" onmouseover="this.style.background='#fffdf3'" onmouseout="this.style.background=''">
                            <div style="flex-shrink:0;padding-top:2px;">
                                <div style="width:36px;height:36px;border-radius:50%;background:{{ $isProspect ? 'var(--yellow)' : '#176139' }};display:flex;align-items:center;justify-content:center;">
                                    <span style="font-size:12px;font-weight:800;color:{{ $isProspect ? 'var(--text)' : '#fff' }};">{{ strtoupper(substr($comment->client->name, 0, 2)) }}</span>
                                </div>
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:4px;">
                                    <span style="font-size:14px;font-weight:700;color:var(--text);">{{ $comment->client->name }}</span>
                                    <span class="admin-badge {{ $isProspect ? 'badge-pending' : 'badge-client' }}" style="font-size:10px;padding:2px 8px;">{{ $isProspect ? 'PROSPECT' : 'CLIENT' }}</span>
                                    <span style="font-size:12px;color:var(--muted);">par {{ $comment->user->name }}</span>
                                    <span style="font-size:12px;color:var(--muted);margin-left:auto;">{{ $comment->created_at->format('d/m/Y à H:i') }}</span>
                                </div>
                                <div style="font-size:13px;color:var(--text-secondary);line-height:1.6;white-space:pre-wrap;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">{{ $comment->content }}</div>
                            </div>
                            <div style="flex-shrink:0;display:flex;align-items:center;">
                                <span style="color:var(--muted);font-size:13px;">↗</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div style="padding:18px 22px;">
                    {{ $comments->links() }}
                </div>
            @endif
        </div>
    </section>

</x-layouts.admin>
