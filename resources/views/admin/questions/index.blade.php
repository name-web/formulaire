<x-layouts.admin :title="'Questions'">
    <div class="admin-header">
        <div>
            <h1 class="admin-title">Questions</h1>
            <p class="admin-subtitle">Gérer les questions du questionnaire</p>
        </div>
        <a href="{{ route('admin.questions.create') }}" class="admin-btn btn-primary"><i class="fa-solid fa-plus"></i> Nouvelle question</a>
    </div>

    <div class="admin-card">
        <form method="GET" style="display:flex;gap:12px;margin-bottom:24px;flex-wrap:wrap;">
            <div style="flex:1;min-width:200px;">
                <input type="text" name="search" class="admin-input" placeholder="Rechercher une question..." value="{{ request('search') }}">
            </div>
            <div style="min-width:150px;">
                <select name="type" class="admin-input">
                    <option value="">Tous les types</option>
                    @foreach(config('question_types.types') as $key => $type)
                        <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $type['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width:130px;">
                <select name="status" class="admin-input">
                    <option value="">Tous</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactif</option>
                </select>
            </div>
            <button type="submit" class="admin-btn btn-primary"><i class="fa-solid fa-search"></i> Filtrer</button>
            @if(request()->hasAny(['search', 'type', 'status']))
                <a href="{{ route('admin.questions.index') }}" class="admin-btn btn-secondary"><i class="fa-solid fa-xmark"></i> Réinitialiser</a>
            @endif
        </form>

        @if($questions->isEmpty())
            <p style="color:var(--text-muted);font-size:13px;text-align:center;padding:50px 0;">Aucune question trouvée.</p>
        @else
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width:50px;">#</th>
                        <th>Titre</th>
                        <th>Type</th>
                        <th>Options</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($questions as $question)
                        <tr>
                            <td style="font-weight:700;color:rgba(255,255,255,0.5);">{{ $question->sort_order }}</td>
                            <td>
                                <div style="font-weight:600;color:rgba(255,255,255,0.85);">{{ $question->title }}</div>
                                @if($question->group_label)
                                    <div style="font-size:11px;color:var(--text-muted);">{{ $question->group_label }}</div>
                                @endif
                            </td>
                            <td>
                                @php
                                    $typeLabel = config("question_types.types.{$question->type}.label", $question->type);
                                @endphp
                                <span class="admin-badge badge-in_progress">{{ $typeLabel }}</span>
                            </td>
                            <td style="font-size:12px;color:var(--text-secondary);">{{ $question->options_count }}</td>
                            <td>
                                <span class="admin-badge {{ $question->is_active ? 'badge-active' : 'badge-inactive' }}">{{ $question->is_active ? 'Actif' : 'Inactif' }}</span>
                            </td>
                            <td>
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
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top:20px;">
                {{ $questions->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin>
