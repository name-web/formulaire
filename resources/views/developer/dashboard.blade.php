<x-layouts.admin :title="'Mes suivis'">

    <section class="intro">
        <div>
            <div class="intro-label">
                <span></span>
                Espace développeur
            </div>
            <h1>Mes suivis</h1>
            <p>Prospects assignés, suivis à faire et conversions.</p>
        </div>
        <div style="color:var(--muted);font-size:13px;">{{ now()->translatedFormat('l d M Y') }}</div>
    </section>

    <section class="stats" style="grid-template-columns:repeat(4,1fr);">
        @php
            $developerStats = [
                ['label' => 'À faire aujourd\'hui', 'value' => $board['dueToday']->count(), 'info' => 'Suivis prévus ce jour'],
                ['label' => 'En retard', 'value' => $board['overdue']->count(), 'info' => 'Date de suivi dépassée'],
                ['label' => 'Prospects en cours', 'value' => $board['inProgress']->count(), 'info' => 'Mes suivis actifs'],
                ['label' => 'Convertis', 'value' => $board['converted']->count(), 'info' => 'Prospects devenus clients'],
            ];
        @endphp

        @foreach($developerStats as $i => $card)
            <article class="stat {{ $i === 0 ? 'main-stat' : '' }}">
                <div class="stat-label">{{ $card['label'] }}</div>
                <div class="stat-number">{{ $card['value'] }}</div>
                <div class="stat-info">{{ $card['info'] }}</div>
            </article>
        @endforeach
    </section>

    @include('developer._board', ['board' => $board])

    <section class="content" style="grid-template-columns:1fr;">
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Mon activité récente</div>
                    <div class="card-subtitle">Mes derniers suivis enregistrés</div>
                </div>
                <a href="{{ route('developer.prospects.index') }}" class="view-all">Voir mes prospects →</a>
            </div>

            @if($recentComments->isEmpty())
                <div class="empty-state">
                    <i class="fa-regular fa-comment-dots"></i>
                    <p>Aucun suivi enregistré pour le moment.</p>
                </div>
            @else
                <div class="table-wrap">
                    <div class="table-head" style="grid-template-columns:1.2fr 2.4fr .8fr;">
                        <div>Prospect</div>
                        <div>Suivi</div>
                        <div>Date</div>
                    </div>

                    @foreach($recentComments as $comment)
                        <div class="table-row" style="grid-template-columns:1.2fr 2.4fr .8fr;">
                            <div class="col-info" data-label="Prospect">
                                @if($comment->client)
                                    <a href="{{ route('developer.prospects.show', $comment->client) }}" style="font-weight:700;">{{ $comment->client->name }}</a>
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
