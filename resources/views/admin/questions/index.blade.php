<x-layouts.admin :title="'Questions'">

    <section class="intro">
        <div>
            <div class="intro-label">
                <span></span>
                Gestion
            </div>
            <h1>Questions</h1>
            <p>Gérer les questions du questionnaire</p>
        </div>
        <a href="{{ route('admin.questions.create') }}" class="top-btn primary"><i class="fa-solid fa-plus" style="margin-right:4px;"></i> Nouvelle question</a>
    </section>

    {{-- Filters --}}
    <section class="stats" style="grid-template-columns:1fr;min-height:auto;">
        <div class="stat" style="border-right:0;min-height:auto;">
            <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:200px;">
                    <label class="admin-label">Recherche</label>
                    <input type="text" name="search" class="admin-input" placeholder="Rechercher une question..." value="{{ request('search') }}">
                </div>
                <div style="min-width:150px;">
                    <label class="admin-label">Type</label>
                    <select name="type" class="admin-input">
                        <option value="">Tous les types</option>
                        @foreach(config('question_types.types') as $key => $type)
                            <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $type['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="min-width:130px;">
                    <label class="admin-label">Statut</label>
                    <select name="status" class="admin-input">
                        <option value="">Tous</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="submit" class="admin-btn btn-primary"><i class="fa-solid fa-search"></i> Filtrer</button>
                    @if(request()->hasAny(['search', 'type', 'status']))
                        <a href="{{ route('admin.questions.index') }}" class="admin-btn btn-secondary"><i class="fa-solid fa-xmark"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </section>

    {{-- Table --}}
    <section class="content" style="grid-template-columns:1fr;margin-top:18px;">
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Toutes les questions</div>
                    <div class="card-subtitle">Liste complète des questions</div>
                </div>
            </div>

            @if($questions->isEmpty())
                <div class="empty-state">
                    <i class="fa-regular fa-clipboard"></i>
                    <p>Aucune question trouvée.</p>
                </div>
            @else
                <div class="table-head" style="grid-template-columns:.5fr 2fr .8fr .6fr .7fr 100px;">
                    <div>#</div>
                    <div>Titre</div>
                    <div>Type</div>
                    <div>Options</div>
                    <div>Statut</div>
                    <div>Actions</div>
                </div>

                @foreach($questions as $question)
                    <div class="table-row" style="grid-template-columns:.5fr 2fr .8fr .6fr .7fr 100px;">
                        <div class="date" style="font-weight:700;">{{ $question->sort_order }}</div>
                        <div>
                            <div class="client-name" style="font-size:15px;">{{ $question->title }}</div>
                            @if($question->group_label)
                                <div class="client-email">{{ $question->group_label }}</div>
                            @endif
                        </div>
                        <div>
                            @php
                                $typeLabel = config("question_types.types.{$question->type}.label", $question->type);
                            @endphp
                            <span class="admin-badge badge-in_progress">{{ $typeLabel }}</span>
                        </div>
                        <div class="project">{{ $question->options_count }}</div>
                        <div>
                            <span class="admin-badge {{ $question->is_active ? 'badge-active' : 'badge-inactive' }}">{{ $question->is_active ? 'Actif' : 'Inactif' }}</span>
                        </div>
                        <div style="display:flex;gap:6px;">
                            <a href="{{ route('admin.questions.edit', $question) }}" class="admin-btn btn-secondary btn-sm"><i class="fa-solid fa-pen"></i></a>
                            <form method="POST" action="{{ route('admin.questions.toggle', $question) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="admin-btn btn-secondary btn-sm" title="{{ $question->is_active ? 'Désactiver' : 'Activer' }}">
                                    <i class="fa-solid {{ $question->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.questions.destroy', $question) }}" onsubmit="return confirm('Supprimer cette question ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                @endforeach

                <div style="padding:18px 22px;">
                    {{ $questions->links() }}
                </div>
            @endif
        </div>
    </section>

</x-layouts.admin>
