<x-layouts.admin :title="'Modifier — ' . $question->title">

    <section class="intro">
        <div>
            <div class="intro-label">
                <span></span>
                Modification
            </div>
            <h1>Modifier la question</h1>
            <p>{{ $question->title }}</p>
        </div>
        <a href="{{ route('admin.questions.index') }}" class="top-btn"><i class="fa-solid fa-arrow-left" style="margin-right:4px;"></i> Retour</a>
    </section>

    <form method="POST" action="{{ route('admin.questions.update', $question) }}">
        @csrf
        @method('PUT')

        <section class="content" style="grid-template-columns:1fr;align-items:start;">
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title">
                            <i class="fa-solid fa-pen" style="color:var(--yellow);margin-right:6px;"></i>
                            Informations de la question
                        </div>
                        <div class="card-subtitle">Modifiez les champs ci-dessous</div>
                    </div>
                </div>

                <div style="padding:22px;">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:22px;">
                        <div style="grid-column:1/-1;">
                            <label class="admin-label">Titre *</label>
                            <input type="text" name="title" class="admin-input" value="{{ old('title', $question->title) }}" required>
                        </div>

                        <div>
                            <label class="admin-label">Type *</label>
                            <select name="type" class="admin-input" required>
                                @foreach(config('question_types.types') as $key => $type)
                                    <option value="{{ $key }}" {{ old('type', $question->type) === $key ? 'selected' : '' }}>{{ $type['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="admin-label">Ordre</label>
                            <input type="number" name="sort_order" class="admin-input" value="{{ old('sort_order', $question->sort_order) }}" min="0">
                        </div>

                        <div style="grid-column:1/-1;">
                            <label class="admin-label">Description</label>
                            <textarea name="description" class="admin-input" rows="3">{{ old('description', $question->description) }}</textarea>
                        </div>

                        <div>
                            <label class="admin-label">Placeholder</label>
                            <input type="text" name="placeholder" class="admin-input" value="{{ old('placeholder', $question->placeholder) }}">
                        </div>

                        <div>
                            <label class="admin-label">Texte d'aide</label>
                            <input type="text" name="help_text" class="admin-input" value="{{ old('help_text', $question->help_text) }}">
                        </div>

                        <div>
                            <label class="admin-label">Label de groupe</label>
                            <input type="text" name="group_label" class="admin-input" value="{{ old('group_label', $question->group_label) }}">
                        </div>

                        <div>
                            <label class="admin-label">Types de projet</label>
                            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px;">
                                @foreach($projectTypes as $key => $type)
                                    <label style="display:flex;align-items:center;gap:8px;padding:8px 14px;border-radius:6px;border:1px solid var(--line);cursor:pointer;font-size:14px;color:var(--muted);transition:all .2s;">
                                        <input type="checkbox" name="project_types[]" value="{{ $key }}" {{ in_array($key, old('project_types', $question->project_types ?? [])) ? 'checked' : '' }} style="accent-color:var(--yellow);">
                                        {{ $type['label'] }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div style="display:flex;gap:22px;grid-column:1/-1;">
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;color:var(--muted);">
                                <input type="checkbox" name="is_required" value="1" {{ old('is_required', $question->is_required) ? 'checked' : '' }} style="accent-color:var(--yellow);">
                                Obligatoire
                            </label>
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;color:var(--muted);">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $question->is_active) ? 'checked' : '' }} style="accent-color:var(--yellow);">
                                Active
                            </label>
                        </div>
                    </div>

                    <div style="display:flex;gap:12px;margin-top:32px;padding-top:24px;border-top:1px solid var(--line);">
                        <button type="submit" class="admin-btn btn-primary"><i class="fa-solid fa-check"></i> Enregistrer</button>
                    </div>
                </div>
            </div>
        </section>
    </form>

    {{-- Existing Options --}}
    <section class="content" style="grid-template-columns:1fr;margin-top:18px;animation:none;">
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">
                        <i class="fa-solid fa-list" style="color:var(--yellow);margin-right:6px;"></i>
                        Options
                    </div>
                    <div class="card-subtitle">{{ $question->options->count() }} option{{ $question->options->count() > 1 ? 's' : '' }}</div>
                </div>
                <button onclick="document.getElementById('addOptionForm').style.display = document.getElementById('addOptionForm').style.display === 'none' ? 'block' : 'none'" class="admin-btn btn-secondary btn-sm"><i class="fa-solid fa-plus"></i> Ajouter</button>
            </div>

            {{-- Add Option Form --}}
            <div id="addOptionForm" style="display:none;padding:22px;border-bottom:1px solid var(--line);">
                <form method="POST" action="{{ route('admin.questions.options.store', $question) }}" style="display:flex;gap:12px;align-items:flex-end;">
                    @csrf
                    <div style="flex:2;">
                        <label class="admin-label">Label</label>
                        <input type="text" name="label" class="admin-input" required placeholder="Label de l'option">
                    </div>
                    <div style="flex:2;">
                        <label class="admin-label">Valeur</label>
                        <input type="text" name="value" class="admin-input" required placeholder="valeur_technique">
                    </div>
                    <button type="submit" class="admin-btn btn-primary"><i class="fa-solid fa-check"></i></button>
                </form>
            </div>

            @if($question->options->isEmpty())
                <div class="empty-state">
                    <i class="fa-regular fa-clipboard"></i>
                    <p>Aucune option.</p>
                </div>
            @else
                <div style="padding:14px 22px;display:flex;flex-direction:column;gap:10px;">
                    @foreach($question->options->sortBy('sort_order') as $option)
                        <div style="display:flex;align-items:center;gap:14px;padding:14px 18px;border-radius:6px;border:1px solid var(--line);">
                            <span style="font-size:13px;color:var(--muted);min-width:28px;font-weight:700;">{{ $option->sort_order }}</span>
                            <div style="flex:1;">
                                <div style="font-size:15px;font-weight:700;color:var(--black);">{{ $option->label }}</div>
                                <div style="font-size:13px;color:var(--muted);">{{ $option->value }}</div>
                            </div>
                            <span class="admin-badge {{ $option->is_active ? 'badge-active' : 'badge-inactive' }}">{{ $option->is_active ? 'Actif' : 'Inactif' }}</span>
                            <form method="POST" action="{{ route('admin.questions.options.destroy', [$question, $option]) }}" onsubmit="return confirm('Supprimer cette option ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Conditions --}}
    <section class="content" style="grid-template-columns:1fr;margin-top:18px;animation:none;">
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">
                        <i class="fa-solid fa-code-branch" style="color:var(--yellow);margin-right:6px;"></i>
                        Conditions de visibilité
                    </div>
                    <div class="card-subtitle">{{ $question->conditions->count() }} condition{{ $question->conditions->count() > 1 ? 's' : '' }}</div>
                </div>
                <button onclick="document.getElementById('addConditionForm').style.display = document.getElementById('addConditionForm').style.display === 'none' ? 'block' : 'none'" class="admin-btn btn-secondary btn-sm"><i class="fa-solid fa-plus"></i> Ajouter</button>
            </div>

            <div id="addConditionForm" style="display:none;padding:22px;border-bottom:1px solid var(--line);">
                <form method="POST" action="{{ route('admin.questions.conditions.store', $question) }}" style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:12px;align-items:flex-end;">
                    @csrf
                    <div>
                        <label class="admin-label">Dépend de la question</label>
                        <select name="depends_on_question_id" class="admin-input" required>
                            <option value="">— Sélectionner —</option>
                            @foreach($otherQuestions as $q)
                                <option value="{{ $q->id }}">{{ $q->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="admin-label">Opérateur</label>
                        <select name="operator" class="admin-input" required>
                            <option value="equals">Est égal à</option>
                            <option value="not_equals">N'est pas égal à</option>
                            <option value="contains">Contient</option>
                            <option value="not_contains">Ne contient pas</option>
                        </select>
                    </div>
                    <div>
                        <label class="admin-label">Valeur texte</label>
                        <input type="text" name="text_value" class="admin-input" placeholder="Valeur">
                    </div>
                    <div style="grid-column:1/-1;">
                        <button type="submit" class="admin-btn btn-primary"><i class="fa-solid fa-check"></i> Ajouter</button>
                    </div>
                </form>
            </div>

            @if($question->conditions->isEmpty())
                <div class="empty-state">
                    <i class="fa-solid fa-code-branch"></i>
                    <p>Aucune condition. Cette question est toujours visible.</p>
                </div>
            @else
                <div style="padding:14px 22px;display:flex;flex-direction:column;gap:10px;">
                    @foreach($question->conditions as $condition)
                        <div style="display:flex;align-items:center;gap:14px;padding:14px 18px;border-radius:6px;border:1px solid var(--line);">
                            <i class="fa-solid fa-code-branch" style="color:var(--yellow);font-size:14px;"></i>
                            <div style="flex:1;font-size:14px;color:var(--text-muted);">
                                Si <strong style="color:var(--black);">« {{ $condition->dependsOnQuestion->title ?? '???' }} »</strong>
                                <span class="admin-badge badge-in_progress" style="margin:0 6px;">{{ $condition->operator }}</span>
                                @if($condition->text_value)
                                    <strong style="color:var(--black);">« {{ $condition->text_value }} »</strong>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('admin.questions.conditions.destroy', [$question, $condition]) }}" onsubmit="return confirm('Supprimer cette condition ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

</x-layouts.admin>
