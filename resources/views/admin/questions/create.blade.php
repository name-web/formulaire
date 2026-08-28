<x-layouts.admin :title="'Nouvelle question'">

    <section class="intro">
        <div>
            <div class="intro-label">
                <span></span>
                Création
            </div>
            <h1>Nouvelle question</h1>
            <p>Ajouter une question au questionnaire</p>
        </div>
        <a href="{{ route('admin.questions.index') }}" class="top-btn"><i class="fa-solid fa-arrow-left" style="margin-right:4px;"></i> Retour</a>
    </section>

    <section class="content" style="grid-template-columns:1fr;align-items:start;">
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">
                        <i class="fa-solid fa-plus-circle" style="color:var(--yellow);margin-right:6px;"></i>
                        Formulaire de création
                    </div>
                    <div class="card-subtitle">Remplissez les informations de la question</div>
                </div>
            </div>

            <div style="padding:22px;">
                <form method="POST" action="{{ route('admin.questions.store') }}">
                    @csrf

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:22px;">
                        <div style="grid-column:1/-1;">
                            <label class="admin-label">Titre de la question *</label>
                            <input type="text" name="title" class="admin-input" value="{{ old('title') }}" required placeholder="Ex: Quel est votre budget ?">
                        </div>

                        <div>
                            <label class="admin-label">Type *</label>
                            <select name="type" class="admin-input" required>
                                <option value="">— Sélectionner —</option>
                                @foreach(config('question_types.types') as $key => $type)
                                    <option value="{{ $key }}" {{ old('type') === $key ? 'selected' : '' }}>{{ $type['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="admin-label">Ordre d'affichage</label>
                            <input type="number" name="sort_order" class="admin-input" value="{{ old('sort_order', $questions_max_order ?? 1) }}" min="0">
                        </div>

                        <div style="grid-column:1/-1;">
                            <label class="admin-label">Description (optionnel)</label>
                            <textarea name="description" class="admin-input" rows="3" placeholder="Description ou aide pour cette question...">{{ old('description') }}</textarea>
                        </div>

                        <div>
                            <label class="admin-label">Placeholder</label>
                            <input type="text" name="placeholder" class="admin-input" value="{{ old('placeholder') }}" placeholder="Ex: Décrivez votre projet...">
                        </div>

                        <div>
                            <label class="admin-label">Texte d'aide</label>
                            <input type="text" name="help_text" class="admin-input" value="{{ old('help_text') }}" placeholder="Texte d'information affiché sous le champ">
                        </div>

                        <div>
                            <label class="admin-label">Label de groupe</label>
                            <input type="text" name="group_label" class="admin-input" value="{{ old('group_label') }}" placeholder="Ex: Votre projet">
                        </div>

                        <div>
                            <label class="admin-label">Types de projet</label>
                            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px;">
                                @foreach($projectTypes as $key => $type)
                                    <label style="display:flex;align-items:center;gap:8px;padding:8px 14px;border-radius:6px;border:1px solid var(--line);cursor:pointer;font-size:14px;color:var(--muted);transition:all .2s;">
                                        <input type="checkbox" name="project_types[]" value="{{ $key }}" {{ in_array($key, old('project_types', [])) ? 'checked' : '' }} style="accent-color:var(--yellow);">
                                        {{ $type['label'] }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div style="display:flex;gap:22px;grid-column:1/-1;">
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;color:var(--muted);">
                                <input type="checkbox" name="is_required" value="1" {{ old('is_required') ? 'checked' : '' }} style="accent-color:var(--yellow);">
                                Question obligatoire
                            </label>
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;color:var(--muted);">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} style="accent-color:var(--yellow);">
                                Active
                            </label>
                        </div>
                    </div>

                    {{-- Options Section --}}
                    <div style="margin-top:32px;padding-top:24px;border-top:1px solid var(--line);">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                            <h3 style="font-size:18px;font-weight:800;color:var(--black);">
                                <i class="fa-solid fa-list" style="color:var(--yellow);margin-right:8px;"></i>
                                Options
                            </h3>
                            <button type="button" onclick="addOption()" class="admin-btn btn-secondary btn-sm"><i class="fa-solid fa-plus"></i> Ajouter</button>
                        </div>
                        <p style="font-size:14px;color:var(--text-muted);margin-bottom:18px;">Ajoutez des options pour les types sélection, multi-sélection.</p>

                        <div id="optionsContainer" style="display:flex;flex-direction:column;gap:12px;"></div>
                    </div>

                    <div style="display:flex;gap:12px;margin-top:32px;padding-top:24px;border-top:1px solid var(--line);">
                        <a href="{{ route('admin.questions.index') }}" class="admin-btn btn-secondary">Annuler</a>
                        <button type="submit" class="admin-btn btn-primary"><i class="fa-solid fa-check"></i> Créer la question</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <script>
    let optionIndex = 0;
    function addOption() {
        const container = document.getElementById('optionsContainer');
        const div = document.createElement('div');
        div.style.cssText = 'display:flex;gap:10px;align-items:center;';
        div.innerHTML = `
            <input type="text" name="options[${optionIndex}][label]" class="admin-input" placeholder="Label" required style="flex:2;">
            <input type="text" name="options[${optionIndex}][value]" class="admin-input" placeholder="Valeur" required style="flex:2;">
            <input type="number" name="options[${optionIndex}][sort_order]" class="admin-input" placeholder="Ordre" style="flex:0.5;">
            <button type="button" onclick="this.parentElement.remove()" class="admin-btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
        `;
        container.appendChild(div);
        optionIndex++;
    }
    addOption();
    </script>
</x-layouts.admin>
