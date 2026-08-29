<x-layouts.app :title="$question->title ?? 'Questionnaire'">

    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <img src="{{ asset('IMAGES/Logo dominium.png') }}" alt="DOMINIUM">
                <span>DOMINIUM TECH</span>
            </div>
            @if(!empty($project->project_type))
                @php
                    $types = is_array($project->project_type) ? $project->project_type : [$project->project_type];
                    $typeLabels = collect($types)->map(fn($t) => config("project_types.types.{$t}.label", $t))->implode(', ');
                @endphp
                <div class="project-badge">
                    <div class="project-badge-icon"><i class="fa-solid fa-building"></i></div>
                    <div>
                        <div class="project-badge-label">{{ __('Type de projet') }}</div>
                        <div class="project-badge-value">{{ $typeLabels }}</div>
                    </div>
                </div>
            @endif
        </div>

        <div class="sidebar-label">{{ __('VOTRE PROGRESSION') }}</div>

        <nav class="step-list">
            @foreach($visibleQuestions as $i => $q)
                @php
                    $idx = $i + 1;
                    $isAnswered = in_array($q->id, $answeredIds);
                    $isCurrent = ($idx === $step);
                    $state = $isCurrent ? 'active' : ($isAnswered ? 'done' : 'pending');
                @endphp
                <div class="step-item {{ $state }}">
                    <div class="step-dot {{ $state }}">
                        @if($state === 'done')
                            <i class="fa-solid fa-check"></i>
                        @else
                            {{ str_pad($idx, 2, '0', STR_PAD_LEFT) }}
                        @endif
                    </div>
                    <span class="step-label">{{ $q->title }}</span>
                    @if($state === 'active')
                        <i class="fa-solid fa-chevron-right step-arrow"></i>
                    @endif
                </div>
            @endforeach
        </nav>

        <div class="progress-section">
            <div class="progress-header">
                <span class="progress-label">{{ __('Progression') }}</span>
                <span class="progress-value">{{ $progress['percentage'] }}%</span>
            </div>
            <div class="progress-bar-track">
                <div class="progress-bar-fill" style="width: {{ $progress['percentage'] }}%"></div>
            </div>
            <div class="progress-step-text">{{ __('Étape') }} <strong>{{ $step }}</strong> {{ __('sur') }} <strong>{{ $totalSteps }}</strong></div>
        </div>
    </aside>


    <main class="main-content">
        <div class="content-card anim-scale">

            @if($errors->any())
                <div class="error-box anim-up d1">
                    <div class="error-header"><i class="fa-solid fa-circle-exclamation"></i><span>{{ __('Erreur') }}</span></div>
                    @foreach($errors->all() as $error)
                        <div class="error-msg">{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="card-progress-top">
                <div class="card-progress-top-fill" style="width: {{ $progress['percentage'] }}%"></div>
            </div>

            <div class="top">
                <div class="eyebrow">{{ __('PARLONS DE VOTRE PROJET') }}</div>
                <div class="counter">{{ str_pad($step, 2, '0', STR_PAD_LEFT) }} / {{ str_pad($totalSteps, 2, '0', STR_PAD_LEFT) }}</div>
            </div>

            <h1 class="question-title anim-up d1">
                {{ $question->title }}
                @if($question->is_required)<span class="req">*</span>@endif
            </h1>
            @if(!empty($question->description))
                <p class="question-desc anim-up d2">{{ $question->description }}</p>
            @endif

            <form action="{{ route('questionnaire.step.store', $step) }}{{ ($isEditing ?? false) ? '?edit=1' : '' }}" method="POST" enctype="multipart/form-data" id="stepForm">
                @csrf
                @php $otherOption = null; @endphp

                @if($question->type === 'text')
                    <div class="anim-up d2">
                        <div class="input-icon-wrap">
                            <i class="fa-solid fa-pen input-icon"></i>
                            <input type="text" name="value" class="glass-input" placeholder="{{ $question->placeholder ?? __('Votre réponse…') }}" value="{{ old('value', $answer->text_value ?? '') }}" {{ $question->is_required ? 'required' : '' }}>
                        </div>
                        @if(!empty($question->help_text))
                            <div style="font-size:12px;color:var(--text-muted);margin-top:8px;display:flex;align-items:center;gap:6px;">
                                <i class="fa-solid fa-circle-info" style="font-size:10px;"></i> {{ $question->help_text }}
                            </div>
                        @endif
                    </div>

                @elseif($question->type === 'textarea')
                    <div class="anim-up d2">
                        <div class="textarea-wrap">
                            <textarea name="value" class="glass-input" rows="5" placeholder="{{ $question->placeholder ?? __('Décrivez en détail…') }}" {{ $question->is_required ? 'required' : '' }}>{{ old('value', $answer->text_value ?? '') }}</textarea>
                            <span class="char-counter"><span class="current">0</span>/500</span>
                        </div>
                        @if(!empty($question->help_text))
                            <div style="font-size:12px;color:var(--text-muted);margin-top:8px;display:flex;align-items:center;gap:6px;">
                                <i class="fa-solid fa-circle-info" style="font-size:10px;"></i> {{ $question->help_text }}
                            </div>
                        @endif
                    </div>

                @elseif($question->type === 'number')
                    <div class="anim-up d2">
                        <div class="input-icon-wrap">
                            <i class="fa-solid fa-hashtag input-icon"></i>
                            <input type="number" name="value" class="glass-input" placeholder="{{ $question->placeholder ?? '0' }}" value="{{ old('value', $answer->text_value ?? '') }}" {{ $question->is_required ? 'required' : '' }}>
                        </div>
                        @if(!empty($question->help_text))
                            <div style="font-size:12px;color:var(--text-muted);margin-top:8px;display:flex;align-items:center;gap:6px;">
                                <i class="fa-solid fa-circle-info" style="font-size:10px;"></i> {{ $question->help_text }}
                            </div>
                        @endif
                    </div>

                @elseif($question->type === 'date')
                    <div class="anim-up d2">
                        <div class="input-icon-wrap">
                            <i class="fa-solid fa-calendar input-icon"></i>
                            <input type="date" name="value" class="glass-input" value="{{ old('value', $answer->text_value ?? '') }}" {{ $question->is_required ? 'required' : '' }}>
                        </div>
                        @if(!empty($question->help_text))
                            <div style="font-size:12px;color:var(--text-muted);margin-top:8px;display:flex;align-items:center;gap:6px;">
                                <i class="fa-solid fa-circle-info" style="font-size:10px;"></i> {{ $question->help_text }}
                            </div>
                        @endif
                    </div>

                @elseif($question->type === 'yes_no')
                    @php $currentVal = old('value', $answer->text_value ?? ''); @endphp
                    <input type="hidden" name="value" id="yesnoHidden" value="{{ $currentVal }}">
                    <div class="option-grid anim-up d2">
                        <label class="opt-card yesno-card {{ $currentVal === 'yes' ? 'selected' : '' }}" data-group="value" data-value="yes">
                            <div class="opt-check"><i class="fa-solid fa-check" style="font-size:10px;"></i></div>
                            <div class="yesno-icon" style="background:rgba(255,183,3,0.12);color:#ffb703;">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div class="opt-label">{{ __('Oui') }}</div>
                        </label>
                        <label class="opt-card yesno-card {{ $currentVal === 'no' ? 'selected' : '' }}" data-group="value" data-value="no">
                            <div class="opt-check" style="background:#f87171;"><i class="fa-solid fa-xmark" style="font-size:10px;"></i></div>
                            <div class="yesno-icon" style="background:rgba(255,183,3,0.12);color:#ffb703;">
                                <i class="fa-solid fa-xmark"></i>
                            </div>
                            <div class="opt-label">{{ __('Non') }}</div>
                        </label>
                    </div>

                @elseif($question->type === 'select')
                    @php
                        $selectedId = old('option_ids.0');
                        if($selectedId === null && $answer) {
                            $selectedId = $answer->answerOptions->first()?->question_option_id;
                        }
                        $otherOption = $options->firstWhere('value', 'autre');
                        $showOther = $selectedId && $otherOption && $selectedId == $otherOption->id;
                    @endphp
                    <div style="display:flex;flex-direction:column;gap:12px;" class="anim-up d2">
                        <div class="input-icon-wrap">
                            <i class="fa-solid fa-list input-icon"></i>
                            <select name="option_ids[]" class="glass-input" style="appearance:none;" {{ $question->is_required ? 'required' : '' }}>
                                <option value="">{{ __('— Sélectionnez —') }}</option>
                                @foreach($options as $option)
                                    <option value="{{ $option->id }}" {{ $selectedId == $option->id ? 'selected' : '' }}>{{ $option->label }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if($otherOption)
                            <div class="other-field" id="otherFieldSelect" style="display:{{ $showOther ? 'block' : 'none' }};">
                                <div class="input-icon-wrap">
                                    <i class="fa-solid fa-pen input-icon"></i>
                                    <input type="text" name="other_value" class="glass-input" placeholder="{{ __('Précisez votre réponse…') }}" value="{{ old('other_value', $answer->other_value ?? '') }}">
                                </div>
                            </div>
                        @endif
                        @if(!empty($question->help_text))
                            <div style="font-size:12px;color:var(--text-muted);display:flex;align-items:center;gap:6px;">
                                <i class="fa-solid fa-circle-info" style="font-size:10px;"></i> {{ $question->help_text }}
                            </div>
                        @endif
                    </div>

                @elseif($question->type === 'multiselect')
                    @php
                        $selectedValues = old('option_ids');
                        if(!is_array($selectedValues) && $answer) {
                            $selectedValues = $answer->answerOptions->pluck('question_option_id')->toArray();
                        }
                        $selectedValues = $selectedValues ?? [];
                        $otherOption = $options->firstWhere('value', 'autre');
                        $showOther = $otherOption && in_array($otherOption->id, $selectedValues);
                    @endphp
                    <div style="display:flex;flex-direction:column;gap:12px;" class="anim-up d2">
                        <div class="option-grid">
                            @foreach($options as $option)
                                @php $sel = in_array($option->id, $selectedValues); @endphp
                                <label class="opt-card {{ $sel ? 'selected' : '' }}" data-group="option_ids[]" data-toggle="true" data-value="{{ $option->id }}">
                                    <div class="opt-check"><i class="fa-solid fa-check" style="font-size:9px;"></i></div>
                                    <div class="opt-label">{{ $option->label }}</div>
                                    <input type="checkbox" name="option_ids[]" value="{{ $option->id }}" {{ $sel ? 'checked' : '' }} class="hidden">
                                </label>
                            @endforeach
                        </div>
                        @if($otherOption)
                            <div class="other-field" id="otherFieldMulti" style="display:{{ $showOther ? 'block' : 'none' }};">
                                <div class="input-icon-wrap">
                                    <i class="fa-solid fa-pen input-icon"></i>
                                    <input type="text" name="other_value" class="glass-input" placeholder="{{ __('Précisez votre réponse…') }}" value="{{ old('other_value', $answer->other_value ?? '') }}">
                                </div>
                            </div>
                        @endif
                        @if(!empty($question->help_text))
                            <div style="font-size:12px;color:var(--text-muted);display:flex;align-items:center;gap:6px;">
                                <i class="fa-solid fa-circle-info" style="font-size:10px;"></i> {{ $question->help_text }}
                            </div>
                        @endif
                    </div>

                @elseif($question->type === 'file')
                    <div class="anim-up d2">
                        <div class="dropzone" id="dropzone">
                            <input type="file" name="file" id="fileInput" class="hidden">
                            <div class="dropzone-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                            <div class="dropzone-title">{{ __('Glissez-déposez votre fichier ici') }}</div>
                            <div class="dropzone-sub">{{ __('ou cliquez pour parcourir') }}</div>
                            <div class="dropzone-formats">
                                <span class="format-tag">PNG</span>
                                <span class="format-tag">JPG</span>
                                <span class="format-tag">PDF</span>
                                <span class="format-tag">DOC</span>
                                <span class="format-tag">ZIP</span>
                            </div>
                        </div>
                        <div id="fileList" style="display:flex;flex-direction:column;gap:8px;margin-top:8px;"></div>
                        @if(!empty($answer) && !empty($answer->file_path))
                            <div style="padding:12px 16px;display:flex;align-items:center;gap:10px;font-size:13px;margin-top:8px;border-radius:var(--radius-xs);background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.15);">
                                <i class="fa-solid fa-file-check" style="color:#ffb703;"></i>
                                <span style="color:var(--text-secondary);">{{ __('Fichier déjà envoyé') }}</span>
                            </div>
                        @endif
                        @if(!empty($question->help_text))
                            <div style="font-size:12px;color:var(--text-muted);margin-top:8px;display:flex;align-items:center;gap:6px;">
                                <i class="fa-solid fa-circle-info" style="font-size:10px;"></i> {{ $question->help_text }}
                            </div>
                        @endif
                    </div>
                @endif

                <div class="card-bottom anim-up d5">
                    @if($step > 1)
                        <a href="{{ route('questionnaire.step', $step - 1) }}{{ ($isEditing ?? false) ? '?edit=1' : '' }}" class="btn-back" style="text-decoration:none;">
                            <i class="fa-solid fa-arrow-left"></i> {{ __('Retour') }}
                        </a>
                    @else
                        <a href="{{ ($isEditing ?? false) ? route('questionnaire.summary') : route('questionnaire.index') }}" class="btn-back" style="text-decoration:none;">
                            <i class="fa-solid fa-arrow-left"></i> {{ __('Retour') }}
                        </a>
                    @endif
                    <button type="submit" class="btn-continue">
                        {{ __('Continuer') }} <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </form>
        </div>
    </main>


    <script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.opt-card[data-group="value"]').forEach(card => {
            card.addEventListener('click', () => {
                document.querySelectorAll('.opt-card[data-group="value"]').forEach(c => {
                    c.classList.remove('selected');
                    const ch = c.querySelector('.opt-check');
                    if(ch) { ch.style.opacity = '0'; ch.style.transform = 'scale(0)'; }
                });
                card.classList.add('selected');
                const ch = card.querySelector('.opt-check');
                if(ch) { ch.style.opacity = '1'; ch.style.transform = 'scale(1)'; }
                const inp = document.getElementById('yesnoHidden');
                if(inp) inp.value = card.dataset.value;
            });
        });

        document.querySelectorAll('.opt-card[data-toggle="true"]').forEach(card => {
            card.addEventListener('click', (e) => {
                e.preventDefault();
                card.classList.toggle('selected');
                const cb = card.querySelector('input[type=checkbox]');
                if(cb) cb.checked = card.classList.contains('selected');
                const check = card.querySelector('.opt-check');
                if(check) {
                    check.style.opacity = card.classList.contains('selected') ? '1' : '0';
                    check.style.transform = card.classList.contains('selected') ? 'scale(1)' : 'scale(0)';
                }
                const otherOption = @json($otherOption?->id);
                if(parseInt(card.dataset.value) === otherOption) {
                    const field = document.getElementById('otherFieldMulti');
                    if(field) field.style.display = card.classList.contains('selected') ? 'block' : 'none';
                }
            });
        });

        const selectEl = document.querySelector('select[name="option_ids[]"]');
        if(selectEl) {
            const otherOption = @json($otherOption?->id);
            selectEl.addEventListener('change', () => {
                const field = document.getElementById('otherFieldSelect');
                if(field) field.style.display = selectEl.value == otherOption ? 'block' : 'none';
            });
        }

        document.querySelectorAll('.textarea-wrap textarea').forEach(ta => {
            const counter = ta.parentElement.querySelector('.char-counter .current');
            if(!counter) return;
            const update = () => { counter.textContent = ta.value.length; };
            ta.addEventListener('input', update);
            update();
        });

        const dz = document.getElementById('dropzone');
        const fi = document.getElementById('fileInput');
        const fl = document.getElementById('fileList');
        if(dz && fi) {
            dz.addEventListener('click', () => fi.click());
            dz.addEventListener('dragover', (e) => { e.preventDefault(); dz.classList.add('drag-over'); });
            dz.addEventListener('dragleave', () => dz.classList.remove('drag-over'));
            dz.addEventListener('drop', (e) => {
                e.preventDefault();
                dz.classList.remove('drag-over');
                fi.files = e.dataTransfer.files;
                showFiles(fi.files);
            });
            fi.addEventListener('change', () => showFiles(fi.files));
        }
        function showFiles(files) {
            if(!fl) return;
            fl.innerHTML = '';
            Array.from(files).forEach(f => {
                const div = document.createElement('div');
                div.style.cssText = 'padding:10px 14px;display:flex;align-items:center;gap:10px;font-size:13px;border-radius:12px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);min-width:0;';
                div.innerHTML = '<i class="fa-solid fa-file" style="color:#ffc72c;flex-shrink:0;"></i><span style="color:white;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0;">' + f.name + '</span><span style="margin-left:auto;color:var(--text-muted);font-size:11px;flex-shrink:0;">' + (f.size/1024).toFixed(0) + ' KB</span>';
                fl.appendChild(div);
            });
        }
    });
    </script>
</x-layouts.app>
