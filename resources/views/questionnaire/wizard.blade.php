<x-layouts.app :title="'Questionnaire — DOMINIUM TECH'">

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
                    <div class="project-badge-label">Type de projet</div>
                    <div class="project-badge-value">{{ $typeLabels }}</div>
                </div>
            </div>
        @endif
    </div>

    <div class="sidebar-label">VOTRE PROGRESSION</div>

    <nav class="step-list" id="stepList">
        @php $globalIdx = 0; @endphp
        @foreach($steps->groupBy('group_label') as $groupLabel => $groupSteps)
            <div class="step-group" data-group="{{ $groupLabel }}">
                <div class="step-group-label">{{ $groupLabel }}</div>
                @foreach($groupSteps as $stepDef)
                    @php $globalIdx++; @endphp
                    <div class="step-item {{ $globalIdx === 1 ? 'active' : 'pending' }}"
                         data-step="{{ $globalIdx }}"
                         data-visible="true"
                         id="sidebar-step-{{ $globalIdx }}">
                        <div class="step-dot {{ $globalIdx === 1 ? 'active' : '' }}">
                            {{ str_pad($globalIdx, 2, '0', STR_PAD_LEFT) }}
                        </div>
                        <span class="step-label">{{ \Illuminate\Support\Str::limit($stepDef['question']->title, 35) }}</span>
                        @if($globalIdx === 1)
                            <i class="fa-solid fa-chevron-right step-arrow"></i>
                        @endif
                    </div>
                @endforeach
            </div>
        @endforeach
    </nav>

    <div class="progress-section">
        <div class="progress-header">
            <span class="progress-label">Progression</span>
            <span class="progress-value" id="progressPercent">0%</span>
        </div>
        <div class="progress-bar-track">
            <div class="progress-bar-fill" id="progressFill" style="width:0%"></div>
        </div>
        <div class="progress-step-text">Question <strong id="progressCurrent">1</strong> sur <strong id="progressTotal">{{ count($steps) }}</strong></div>
    </div>
</aside>

<main class="main-content">
    <div class="content-card anim-scale">

        @if($errors->any())
            <div class="error-box anim-up d1">
                <div class="error-header"><i class="fa-solid fa-circle-exclamation"></i><span>Erreur</span></div>
                @foreach($errors->all() as $error)
                    <div class="error-msg">{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('questionnaire.wizard.submit') }}" method="POST" enctype="multipart/form-data" id="wizardForm">
            @csrf

            @php $globalIdx = 0; @endphp
            @foreach($steps as $i => $stepDef)
                @php $globalIdx++; @endphp
                @php $question = $stepDef['question']; @endphp

                <div class="wizard-step {{ $globalIdx === 1 ? 'active' : '' }}" data-step="{{ $globalIdx }}" id="step-{{ $globalIdx }}">
                    <div class="card-progress-top">
                        <div class="card-progress-top-fill step-progress-fill" style="width:0%"></div>
                    </div>

                    <div class="top">
                        <div class="eyebrow">{{ strtoupper($stepDef['group_label']) }}</div>
                        <div class="counter">{{ str_pad($globalIdx, 2, '0', STR_PAD_LEFT) }} / {{ str_pad(count($steps), 2, '0', STR_PAD_LEFT) }}</div>
                    </div>

                    <h1 class="question-title anim-slide-in">{{ $question->title }}</h1>
                    @if(!empty($question->description))
                        <p class="question-desc anim-up d1">{{ $question->description }}</p>
                    @endif

                    @php
                        $existingAnswer = $answers[$question->id] ?? null;
                        $options = $question->options->where('is_active', true)->sortBy('sort_order');
                    @endphp

                    {{-- TEXT --}}
                    @if($question->type === 'text')
                        <div class="anim-up d2">
                            <div class="input-icon-wrap">
                                <i class="fa-solid fa-pen input-icon"></i>
                                <input type="text" name="answers[{{ $question->id }}][value]" class="glass-input"
                                       placeholder="{{ $question->placeholder ?? 'Votre réponse…' }}"
                                       value="{{ old("answers.{$question->id}.value", $existingAnswer?->text_value ?? '') }}"
                                       {{ $question->is_required ? 'required' : '' }}>
                            </div>
                            @if(!empty($question->help_text))
                                <div style="font-size:12px;color:var(--text-muted);margin-top:8px;display:flex;align-items:center;gap:6px;">
                                    <i class="fa-solid fa-circle-info" style="font-size:10px;"></i> {{ $question->help_text }}
                                </div>
                            @endif
                        </div>

                    {{-- TEXTAREA --}}
                    @elseif($question->type === 'textarea')
                        <div class="anim-up d2">
                            <div class="textarea-wrap">
                                <textarea name="answers[{{ $question->id }}][value]" class="glass-input" rows="4"
                                          placeholder="{{ $question->placeholder ?? 'Décrivez en détail…' }}"
                                          {{ $question->is_required ? 'required' : '' }}>{{ old("answers.{$question->id}.value", $existingAnswer?->text_value ?? '') }}</textarea>
                                <span class="char-counter"><span class="current">0</span>/500</span>
                            </div>
                            @if(!empty($question->help_text))
                                <div style="font-size:12px;color:var(--text-muted);margin-top:8px;display:flex;align-items:center;gap:6px;">
                                    <i class="fa-solid fa-circle-info" style="font-size:10px;"></i> {{ $question->help_text }}
                                </div>
                            @endif
                        </div>

                    {{-- NUMBER --}}
                    @elseif($question->type === 'number')
                        <div class="anim-up d2">
                            <div class="input-icon-wrap">
                                <i class="fa-solid fa-hashtag input-icon"></i>
                                <input type="number" name="answers[{{ $question->id }}][value]" class="glass-input"
                                       placeholder="{{ $question->placeholder ?? '0' }}"
                                       value="{{ old("answers.{$question->id}.value", $existingAnswer?->text_value ?? '') }}"
                                       {{ $question->is_required ? 'required' : '' }}>
                            </div>
                        </div>

                    {{-- DATE --}}
                    @elseif($question->type === 'date')
                        <div class="anim-up d2">
                            <div class="input-icon-wrap">
                                <i class="fa-solid fa-calendar input-icon"></i>
                                <input type="date" name="answers[{{ $question->id }}][value]" class="glass-input"
                                       value="{{ old("answers.{$question->id}.value", $existingAnswer?->text_value ?? '') }}"
                                       {{ $question->is_required ? 'required' : '' }}>
                            </div>
                        </div>

                    {{-- YES/NO --}}
                    @elseif($question->type === 'yes_no')
                        @php $currentVal = old("answers.{$question->id}.value", $existingAnswer?->text_value ?? ''); @endphp
                        <input type="hidden" name="answers[{{ $question->id }}][value]" value="{{ $currentVal }}" data-yesno="{{ $question->id }}">
                        <div class="option-grid anim-up d2" style="grid-template-columns:1fr 1fr;">
                            <label class="opt-card yesno-card {{ $currentVal === 'yes' ? 'selected' : '' }}"
                                   data-group="yesno-{{ $question->id }}" data-value="yes" data-question="{{ $question->id }}">
                                <div class="opt-check"><i class="fa-solid fa-check" style="font-size:10px;"></i></div>
                                <div class="yesno-icon" style="background:rgba(59,130,246,0.12);color:#3b82f6;">
                                    <i class="fa-solid fa-check"></i>
                                </div>
                                <div class="opt-label">Oui</div>
                            </label>
                            <label class="opt-card yesno-card {{ $currentVal === 'no' ? 'selected' : '' }}"
                                   data-group="yesno-{{ $question->id }}" data-value="no" data-question="{{ $question->id }}">
                                <div class="opt-check" style="background:#f87171;"><i class="fa-solid fa-xmark" style="font-size:10px;"></i></div>
                                <div class="yesno-icon" style="background:rgba(59,130,246,0.12);color:#3b82f6;">
                                    <i class="fa-solid fa-xmark"></i>
                                </div>
                                <div class="opt-label">Non</div>
                            </label>
                        </div>

                    {{-- SELECT --}}
                    @elseif($question->type === 'select')
                        @php
                            $selectedId = old("answers.{$question->id}.option_ids.0");
                            if($selectedId === null && $existingAnswer) {
                                $selectedId = $existingAnswer->answerOptions->first()?->question_option_id;
                            }
                            $otherOption = $options->firstWhere('value', 'autre');
                            $showOther = $selectedId && $otherOption && $selectedId == $otherOption->id;
                        @endphp
                        <div class="anim-up d2" style="display:flex;flex-direction:column;gap:12px;">
                            <div class="input-icon-wrap">
                                <i class="fa-solid fa-list input-icon"></i>
                                <select name="answers[{{ $question->id }}][option_ids][]" class="glass-input"
                                        {{ $question->is_required ? 'required' : '' }}
                                        data-qid="{{ $question->id }}" data-other-option="{{ $otherOption?->id ?? '' }}">
                                    <option value="">— Sélectionnez —</option>
                                    @foreach($options as $option)
                                        <option value="{{ $option->id }}" {{ $selectedId == $option->id ? 'selected' : '' }}>{{ $option->label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if($otherOption)
                                <div class="other-field" style="display:{{ $showOther ? 'block' : 'none' }};" data-other-for="{{ $question->id }}">
                                    <div class="input-icon-wrap">
                                        <i class="fa-solid fa-pen input-icon"></i>
                                        <input type="text" name="answers[{{ $question->id }}][other_value]" class="glass-input"
                                               placeholder="Précisez votre réponse…"
                                               value="{{ old("answers.{$question->id}.other_value", $existingAnswer?->other_value ?? '') }}">
                                    </div>
                                </div>
                            @endif
                        </div>

                    {{-- MULTISELECT --}}
                    @elseif($question->type === 'multiselect')
                        @php
                            $selectedValues = old("answers.{$question->id}.option_ids");
                            if(!is_array($selectedValues) && $existingAnswer) {
                                $selectedValues = $existingAnswer->answerOptions->pluck('question_option_id')->toArray();
                            }
                            $selectedValues = $selectedValues ?? [];
                            $otherOption = $options->firstWhere('value', 'autre');
                            $showOther = $otherOption && in_array($otherOption->id, $selectedValues);
                        @endphp
                        <div class="anim-up d2" style="display:flex;flex-direction:column;gap:12px;">
                            <div class="option-grid">
                                @foreach($options as $option)
                                    @php $sel = in_array($option->id, $selectedValues); @endphp
                                    <label class="opt-card {{ $sel ? 'selected' : '' }}"
                                           data-group="multi-{{ $question->id }}" data-toggle="true"
                                           data-value="{{ $option->id }}" data-question="{{ $question->id }}">
                                        <div class="opt-check"><i class="fa-solid fa-check" style="font-size:9px;"></i></div>
                                        <div class="opt-label">{{ $option->label }}</div>
                                        <input type="checkbox" name="answers[{{ $question->id }}][option_ids][]"
                                               value="{{ $option->id }}" {{ $sel ? 'checked' : '' }} class="hidden">
                                    </label>
                                @endforeach
                            </div>
                            @if($otherOption)
                                <div class="other-field" style="display:{{ $showOther ? 'block' : 'none' }};" data-other-for="{{ $question->id }}">
                                    <div class="input-icon-wrap">
                                        <i class="fa-solid fa-pen input-icon"></i>
                                        <input type="text" name="answers[{{ $question->id }}][other_value]" class="glass-input"
                                               placeholder="Précisez votre réponse…"
                                               value="{{ old("answers.{$question->id}.other_value", $existingAnswer?->other_value ?? '') }}">
                                    </div>
                                </div>
                            @endif
                        </div>

                    {{-- FILE --}}
                    @elseif($question->type === 'file')
                        <div class="anim-up d2">
                            <div class="dropzone" data-dropzone="{{ $question->id }}">
                                <input type="file" name="answers[{{ $question->id }}][file]"
                                       data-file-input="{{ $question->id }}" class="hidden"
                                       accept=".png,.jpg,.jpeg,.gif,.pdf,.svg,.doc,.docx,.zip">
                                <div class="dropzone-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                                <div class="dropzone-title">Glissez-déposez votre fichier ici</div>
                                <div class="dropzone-sub">ou cliquez pour parcourir</div>
                                <div class="dropzone-formats">
                                    <span class="format-tag">PNG</span>
                                    <span class="format-tag">JPG</span>
                                    <span class="format-tag">PDF</span>
                                    <span class="format-tag">DOC</span>
                                    <span class="format-tag">ZIP</span>
                                </div>
                            </div>
                            <div data-file-list="{{ $question->id }}" style="display:flex;flex-direction:column;gap:8px;margin-top:8px;"></div>
                            @if($existingAnswer && !empty($existingAnswer->file_path))
                                <div style="padding:12px 16px;display:flex;align-items:center;gap:10px;font-size:13px;margin-top:8px;border-radius:var(--radius-xs);background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.15);">
                                    <i class="fa-solid fa-file-check" style="color:#3b82f6;"></i>
                                    <span style="color:var(--text-secondary);">Fichier déjà envoyé</span>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- "Je ne sais pas encore" link --}}
                    @if(!$question->is_required && $question->type !== 'file')
                        <div class="anim-up d3" style="margin-top:16px;">
                            <a href="#" class="skip-link" data-qid="{{ $question->id }}"
                               style="font-size:13px;color:var(--text-muted);text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:color .2s;">
                                <i class="fa-solid fa-circle-question" style="font-size:11px;"></i> Je ne sais pas encore
                            </a>
                        </div>
                    @endif

                    <div class="card-bottom anim-up d5">
                        @if($globalIdx > 1)
                            <button type="button" class="btn-back" onclick="goToStep({{ $globalIdx - 1 }})">
                                <i class="fa-solid fa-arrow-left"></i> Retour
                            </button>
                        @else
                            <a href="{{ route('questionnaire.index') }}" class="btn-back" style="text-decoration:none;">
                                <i class="fa-solid fa-arrow-left"></i> Retour
                            </a>
                        @endif
                        @if($globalIdx < count($steps))
                            <button type="button" class="btn-continue" onclick="goToStep({{ $globalIdx + 1 }})">
                                Continuer <i class="fa-solid fa-arrow-right"></i>
                            </button>
                        @else
                            <button type="submit" class="btn-continue">
                                Envoyer ma demande <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </form>
    </div>
</main>

<style>
.step-group { margin-bottom: 4px; }
.step-group-label {
    font-size: 9px;
    font-weight: 700;
    color: rgba(255,255,255,0.3);
    text-transform: uppercase;
    letter-spacing: 1.2px;
    padding: 6px 8px 2px;
}
.step-group .step-item { padding-left: 12px; }
.skip-link:hover { color: var(--blue-light) !important; }
.skip-link.skipped { color: var(--blue-light); }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const totalSteps = {{ count($steps) }};
    let currentStep = 1;

    const projectTypes = @json(is_array($project->project_type) ? $project->project_type : [$project->project_type]);
    const stepTypes = @json($steps->pluck('types')->toArray());

    function updateSidebar() {
        document.querySelectorAll('.step-item').forEach(item => {
            const step = parseInt(item.dataset.step);
            const isVisible = item.dataset.visible === 'true';
            if (!isVisible) {
                item.style.display = 'none';
                return;
            }
            item.style.display = '';
            item.classList.remove('active', 'done');
            const dot = item.querySelector('.step-dot');
            dot.classList.remove('active');
            const arrow = item.querySelector('.step-arrow');
            if (arrow) arrow.remove();

            if (step === currentStep) {
                item.classList.add('active');
                dot.classList.add('active');
                const arrowEl = document.createElement('i');
                arrowEl.className = 'fa-solid fa-chevron-right step-arrow';
                item.appendChild(arrowEl);
            } else if (step < currentStep) {
                item.classList.add('done');
                dot.innerHTML = '<i class="fa-solid fa-check"></i>';
            } else {
                dot.textContent = String(step).padStart(2, '0');
            }
        });

        // Hide group labels that have no visible children
        document.querySelectorAll('.step-group').forEach(group => {
            const visibleItems = group.querySelectorAll('.step-item:not([style*="display: none"])');
            group.style.display = visibleItems.length > 0 ? '' : 'none';
        });

        // Scroll active item into view
        const activeItem = document.querySelector('.step-item.active');
        if (activeItem) activeItem.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }

    function updateProgress() {
        const visibleSteps = document.querySelectorAll('.step-item:not([style*="display: none"])');
        const total = visibleSteps.length;
        let currentIdx = 0;
        visibleSteps.forEach((item) => {
            if (parseInt(item.dataset.step) === currentStep) currentIdx++;
        });
        const pct = total > 0 ? Math.round((currentIdx / total) * 100) : 0;
        document.getElementById('progressPercent').textContent = pct + '%';
        document.getElementById('progressFill').style.width = pct + '%';
        document.getElementById('progressCurrent').textContent = currentIdx;
        document.getElementById('progressTotal').textContent = total;

        document.querySelectorAll('.step-progress-fill').forEach(fill => {
            fill.style.width = '0%';
        });
        const activeFill = document.querySelector('.wizard-step.active .step-progress-fill');
        if (activeFill) activeFill.style.width = pct + '%';
    }

    function showStep(step) {
        document.querySelectorAll('.wizard-step').forEach(s => s.classList.remove('active'));
        const target = document.getElementById('step-' + step);
        if (target) target.classList.add('active');
        currentStep = step;
        updateSidebar();
        updateProgress();
        document.querySelector('.main-content').scrollTo(0, 0);
    }

    window.goToStep = function(step, depth) {
        depth = depth || 0;
        if (depth > totalSteps) {
            for (let i = totalSteps; i >= 1; i--) {
                if (isStepVisible(i - 1)) { showStep(i); return; }
            }
            return;
        }
        if (step < 1 || step > totalSteps) return;
        const targetStepDef = stepTypes[step - 1];
        if (targetStepDef && !targetStepDef.includes('all')) {
            const matches = targetStepDef.some(t => projectTypes.includes(t));
            if (!matches) {
                goToStep(step + 1, depth + 1);
                return;
            }
        }
        showStep(step);
    };

    function isStepVisible(stepIndex) {
        const types = stepTypes[stepIndex];
        if (!types || types.includes('all')) return true;
        return types.some(t => projectTypes.includes(t));
    }

    // Set initial visibility from server data, then let JS override
    document.querySelectorAll('.step-item').forEach(item => {
        const step = parseInt(item.dataset.step);
        if (isStepVisible(step - 1)) {
            item.dataset.visible = 'true';
        } else {
            item.dataset.visible = 'false';
        }
    });

    // "Je ne sais pas encore" links
    document.querySelectorAll('.skip-link').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const qid = link.dataset.qid;

            // For yes_no: set value to 'no'
            const yesnoInput = document.querySelector(`input[data-yesno="${qid}"]`);
            if (yesnoInput) {
                yesnoInput.value = 'no';
                document.querySelectorAll(`.opt-card[data-group="yesno-${qid}"]`).forEach(c => {
                    c.classList.remove('selected');
                    const ch = c.querySelector('.opt-check');
                    if (ch) { ch.style.opacity = '0'; ch.style.transform = 'scale(0)'; }
                });
                const noCard = document.querySelector(`.opt-card[data-group="yesno-${qid}"][data-value="no"]`);
                if (noCard) {
                    noCard.classList.add('selected');
                    const ch = noCard.querySelector('.opt-check');
                    if (ch) { ch.style.opacity = '1'; ch.style.transform = 'scale(1)'; }
                }
            }

            // For select: set to empty (skip)
            const selectEl = document.querySelector(`select[data-qid="${qid}"]`);
            if (selectEl) {
                selectEl.value = '';
            }

            // For multiselect: uncheck all
            document.querySelectorAll(`input[name="answers[${qid}][option_ids][]"]`).forEach(cb => {
                cb.checked = false;
                const card = cb.closest('.opt-card');
                if (card) {
                    card.classList.remove('selected');
                    const ch = card.querySelector('.opt-check');
                    if (ch) { ch.style.opacity = '0'; ch.style.transform = 'scale(0)'; }
                }
            });

            // For text/textarea: clear
            const textInput = document.querySelector(`input[name="answers[${qid}][value]"], textarea[name="answers[${qid}][value]"]`);
            if (textInput) {
                textInput.value = '';
            }

            // Visual feedback
            link.classList.add('skipped');
            link.innerHTML = '<i class="fa-solid fa-check" style="font-size:11px;"></i> Passé';

            // Auto-advance after short delay
            setTimeout(() => {
                if (currentStep < totalSteps) {
                    goToStep(currentStep + 1);
                }
            }, 400);
        });
    });

    // Yes/No cards
    document.querySelectorAll('.opt-card[data-group^="yesno-"]').forEach(card => {
        card.addEventListener('click', () => {
            const group = card.dataset.group;
            document.querySelectorAll(`.opt-card[data-group="${group}"]`).forEach(c => {
                c.classList.remove('selected');
                const ch = c.querySelector('.opt-check');
                if(ch) { ch.style.opacity = '0'; ch.style.transform = 'scale(0)'; }
            });
            card.classList.add('selected');
            const ch = card.querySelector('.opt-check');
            if(ch) { ch.style.opacity = '1'; ch.style.transform = 'scale(1)'; }
            const qid = card.dataset.question;
            const inp = document.querySelector(`input[data-yesno="${qid}"]`);
            if(inp) inp.value = card.dataset.value;
        });
    });

    // Multiselect toggle cards
    document.querySelectorAll('.opt-card[data-toggle="true"]').forEach(card => {
        card.addEventListener('click', () => {
            card.classList.toggle('selected');
            const cb = card.querySelector('input[type=checkbox]');
            if(cb) cb.checked = card.classList.contains('selected');
            const check = card.querySelector('.opt-check');
            if(check) {
                check.style.opacity = card.classList.contains('selected') ? '1' : '0';
                check.style.transform = card.classList.contains('selected') ? 'scale(1)' : 'scale(0)';
            }
            const qid = card.dataset.question;
            const optId = parseInt(card.dataset.value);
            const otherField = document.querySelector(`[data-other-for="${qid}"]`);
            if (otherField) {
                const selectEl = document.querySelector(`select[data-qid="${qid}"]`);
                if (selectEl) {
                    const otherOptionId = selectEl.dataset.otherOption;
                    otherField.style.display = selectEl.value == otherOptionId ? 'block' : 'none';
                } else {
                    // Check if this is the "autre" option
                    const otherInput = otherField.querySelector('input[type="text"]');
                    if (otherInput) {
                        const parentSelect = document.querySelector(`select[data-qid="${qid}"]`);
                        if (parentSelect && parseInt(parentSelect.dataset.otherOption) === optId) {
                            otherField.style.display = card.classList.contains('selected') ? 'block' : 'none';
                        }
                    }
                }
            }
        });
    });

    // Select change for "other" field
    document.querySelectorAll('select[data-qid]').forEach(selectEl => {
        const qid = selectEl.dataset.qid;
        const otherOptionId = selectEl.dataset.otherOption;
        selectEl.addEventListener('change', () => {
            const field = document.querySelector(`[data-other-for="${qid}"]`);
            if(field) field.style.display = selectEl.value == otherOptionId ? 'block' : 'none';
        });
    });

    // Character counters
    document.querySelectorAll('.textarea-wrap textarea').forEach(ta => {
        const counter = ta.parentElement.querySelector('.char-counter .current');
        if(!counter) return;
        const update = () => { counter.textContent = ta.value.length; };
        ta.addEventListener('input', update);
        update();
    });

    // Dropzones
    document.querySelectorAll('[data-dropzone]').forEach(dz => {
        const qid = dz.dataset.dropzone;
        const fi = document.querySelector(`[data-file-input="${qid}"]`);
        const fl = document.querySelector(`[data-file-list="${qid}"]`);
        if(!fi) return;
        dz.addEventListener('click', () => fi.click());
        dz.addEventListener('dragover', (e) => { e.preventDefault(); dz.classList.add('drag-over'); });
        dz.addEventListener('dragleave', () => dz.classList.remove('drag-over'));
        dz.addEventListener('drop', (e) => {
            e.preventDefault(); dz.classList.remove('drag-over');
            fi.files = e.dataTransfer.files;
            showWizardFiles(fi.files, fl);
        });
        fi.addEventListener('change', () => showWizardFiles(fi.files, fl));
    });

    function showWizardFiles(files, container) {
        if(!container) return;
        container.innerHTML = '';
        Array.from(files).forEach(f => {
            const div = document.createElement('div');
            div.style.cssText = 'padding:10px 14px;display:flex;align-items:center;gap:10px;font-size:13px;border-radius:12px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);';
            div.innerHTML = '<i class="fa-solid fa-file" style="color:#60a5fa;"></i><span style="color:white;">' + f.name + '</span><span style="margin-left:auto;color:var(--text-muted);font-size:11px;">' + (f.size/1024).toFixed(0) + ' KB</span>';
            container.appendChild(div);
        });
    }

    showStep(1);
});
</script>
</x-layouts.app>
