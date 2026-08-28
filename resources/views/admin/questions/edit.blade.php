<x-layouts.admin :title="'Modifier — ' . $question->title">
    <div class="admin-header">
        <div>
            <h1 class="admin-title">Modifier la question</h1>
            <p class="admin-subtitle">{{ $question->title }}</p>
        </div>
        <a href="{{ route('admin.questions.index') }}" class="admin-btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Retour</a>
    </div>

    <form method="POST" action="{{ route('admin.questions.update', $question) }}">
        @csrf
        @method('PUT')

        <div class="admin-card">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
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
                    <textarea name="description" class="admin-input" rows="2">{{ old('description', $question->description) }}</textarea>
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
                    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:6px;">
                        @foreach($projectTypes as $key => $type)
                            <label style="display:flex;align-items:center;gap:6px;padding:6px 12px;border-radius:8px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);cursor:pointer;font-size:12px;color:rgba(255,255,255,0.7);">
                                <input type="checkbox" name="project_types[]" value="{{ $key }}" {{ in_array($key, old('project_types', $question->project_types ?? [])) ? 'checked' : '' }} style="accent-color:#ffb703;">
                                {{ $type['label'] }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div style="display:flex;gap:20px;grid-column:1/-1;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:rgba(255,255,255,0.7);">
                        <input type="checkbox" name="is_required" value="1" {{ old('is_required', $question->is_required) ? 'checked' : '' }} style="accent-color:#ffb703;">
                        Obligatoire
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:rgba(255,255,255,0.7);">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $question->is_active) ? 'checked' : '' }} style="accent-color:#ffb703;">
                        Active
                    </label>
                </div>
            </div>

            <div style="display:flex;gap:10px;margin-top:28px;">
                <button type="submit" class="admin-btn btn-primary"><i class="fa-solid fa-check"></i> Enregistrer</button>
            </div>
        </div>
    </form>

    {{-- Existing Options --}}
    <div class="admin-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
            <h3 style="font-size:15px;font-weight:800;color:white;">
                <i class="fa-solid fa-list" style="color:var(--blue-light);margin-right:8px;"></i>
                Options ({{ $question->options->count() }})
            </h3>
            <button onclick="document.getElementById('addOptionForm').style.display = document.getElementById('addOptionForm').style.display === 'none' ? 'block' : 'none'" class="admin-btn btn-secondary btn-sm"><i class="fa-solid fa-plus"></i> Ajouter</button>
        </div>

        {{-- Add Option Form --}}
        <div id="addOptionForm" style="display:none;margin-bottom:20px;padding:16px;border-radius:12px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);">
            <form method="POST" action="{{ route('admin.questions.options.store', $question) }}" style="display:flex;gap:10px;align-items:end;">
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
            <p style="color:var(--text-muted);font-size:13px;text-align:center;padding:30px 0;">Aucune option.</p>
        @else
            <div style="display:flex;flex-direction:column;gap:8px;">
                @foreach($question->options->sortBy('sort_order') as $option)
                    <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:12px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);">
                        <span style="font-size:11px;color:var(--text-muted);min-width:24px;">{{ $option->sort_order }}</span>
                        <div style="flex:1;">
                            <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,0.85);">{{ $option->label }}</div>
                            <div style="font-size:11px;color:var(--text-muted);">{{ $option->value }}</div>
                        </div>
                        <span class="admin-badge {{ $option->is_active ? 'badge-active' : 'badge-inactive' }}" style="font-size:9px;">{{ $option->is_active ? 'Actif' : 'Inactif' }}</span>
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

    {{-- Conditions --}}
    <div class="admin-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
            <h3 style="font-size:15px;font-weight:800;color:white;">
                <i class="fa-solid fa-code-branch" style="color:var(--blue-light);margin-right:8px;"></i>
                Conditions de visibilité ({{ $question->conditions->count() }})
            </h3>
            <button onclick="document.getElementById('addConditionForm').style.display = document.getElementById('addConditionForm').style.display === 'none' ? 'block' : 'none'" class="admin-btn btn-secondary btn-sm"><i class="fa-solid fa-plus"></i> Ajouter</button>
        </div>

        <div id="addConditionForm" style="display:none;margin-bottom:20px;padding:16px;border-radius:12px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);">
            <form method="POST" action="{{ route('admin.questions.conditions.store', $question) }}" style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:10px;align-items:end;">
                @csrf
                <div>
                    <label class="admin-label">Dépend de la question</label>
                    <select name="depends_on_question_id" class="admin-input" required>
                        <option value="">— Sélectionner —</option>
                        @foreach(\App\Models\Question::where('id', '!=', $question->id)->orderBy('sort_order')->get() as $q)
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
            <p style="color:var(--text-muted);font-size:13px;text-align:center;padding:30px 0;">Aucune condition. Cette question est toujours visible.</p>
        @else
            <div style="display:flex;flex-direction:column;gap:8px;">
                @foreach($question->conditions as $condition)
                    <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:12px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);">
                        <i class="fa-solid fa-code-branch" style="color:var(--blue-light);font-size:13px;"></i>
                        <div style="flex:1;font-size:13px;color:rgba(255,255,255,0.75);">
                            Si <strong style="color:rgba(255,255,255,0.9);">« {{ $condition->dependsOnQuestion->title ?? '???' }} »</strong>
                            <span class="admin-badge badge-in_progress" style="font-size:9px;margin:0 4px;">{{ $condition->operator }}</span>
                            @if($condition->text_value)
                                <strong style="color:rgba(255,255,255,0.9);">« {{ $condition->text_value }} »</strong>
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
</x-layouts.admin>
