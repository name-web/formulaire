<x-layouts.admin :title="'Suivi — ' . $developer->name">

    <section class="intro">
        <div>
            <div class="intro-label">
                <span></span>
                Espace développeur
            </div>
            <h1>{{ $developer->name }}</h1>
            <p>Suivi complet de ses prospects assignés (lecture seule).</p>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
            <a href="{{ route('super_admin.prospects.index', ['developer' => $developer->id]) }}" class="top-btn"><i class="fa-solid fa-user-clock" style="margin-right:4px;"></i> Attribuer des prospects</a>
            <a href="{{ route('super_admin.developers.index') }}" class="top-btn"><i class="fa-solid fa-arrow-left" style="margin-right:4px;"></i> Retour</a>
        </div>
    </section>

    <section class="stats" style="grid-template-columns:repeat(4,1fr);">
        @php
            $developerStats = [
                ['label' => 'À faire aujourd\'hui', 'value' => $board['dueToday']->count(), 'info' => 'Suivis prévus ce jour'],
                ['label' => 'En retard', 'value' => $board['overdue']->count(), 'info' => 'Date de suivi dépassée'],
                ['label' => 'Prospects en cours', 'value' => $board['inProgress']->count(), 'info' => 'Suivis actifs'],
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

    @include('developer._board', ['board' => $board, 'prospectRoute' => 'super_admin.prospects.show'])

    <section class="content" style="grid-template-columns:1fr;">
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Derniers suivis enregistrés</div>
                    <div class="card-subtitle">Commentaires ajoutés par ce développeur</div>
                </div>
            </div>

            @if($comments->isEmpty())
                <div class="empty-state">
                    <i class="fa-regular fa-comment-dots"></i>
                    <p>Aucun suivi enregistré par ce développeur.</p>
                </div>
            @else
                <div class="table-wrap">
                    <div class="table-head" style="grid-template-columns:1.2fr 2fr .9fr;">
                        <div>Prospect</div>
                        <div>Commentaire</div>
                        <div>Date</div>
                    </div>

                    @foreach($comments as $comment)
                        <div class="table-row" style="grid-template-columns:1.2fr 2fr .9fr;">
                            <div class="col-info" data-label="Prospect">
                                @if($comment->client)
                                    <a href="{{ route('super_admin.prospects.show', $comment->client) }}" style="font-weight:700;">{{ $comment->client->name }}</a>
                                @else
                                    —
                                @endif
                            </div>
                            <div class="col-meta" data-label="Commentaire">{{ Str::limit($comment->content, 120) }}</div>
                            <div class="date">{{ $comment->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

</x-layouts.admin>
