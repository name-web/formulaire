<x-layouts.app :title="'Questionnaire — DOMINIUM TECH'">

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
.q-block { padding:28px 0; border-bottom:1px solid rgba(255,255,255,.06); }
.q-block:last-child { border-bottom:none; }
.q-block-num { display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:50%; background:rgba(255,183,3,.1); color:var(--blue-light); font-size:11px; font-weight:800; margin-right:10px; flex-shrink:0; }
.q-block-title { display:inline; font-size:16px; font-weight:800; color:white; }
.q-block-desc { margin:6px 0 0 38px; font-size:13px; color:rgba(255,255,255,.4); }
.q-block-required { color:#f87171; margin-left:4px; }
.q-block-skip { margin-top:14px; margin-left:38px; }
.q-block-skip .skip-link { font-size:13px; color:var(--text-muted); text-decoration:none; display:inline-flex; align-items:center; gap:6px; transition:color .2s; }
.q-block-skip .skip-link.skipped { color:var(--blue-light); }
.q-block-skip .skip-link:hover { color:var(--blue-light); }
.q-block-answers { margin-top:14px; margin-left:38px; }
.section-divider { font-size:10px; font-weight:800; color:rgba(255,255,255,.25); letter-spacing:1.5px; text-transform:uppercase; padding:20px 0 8px; }
</style>

<aside class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <img src="{{ asset('IMAGES/Logo dominium.png') }}" alt="DOMINIUM">
            <span>DOMINIUM TECH</span>
        </div>
    </div>

    <div class="sidebar-label">{{ __('VOTRE PROGRESSION') }}</div>

    <nav class="step-list" id="stepList">
        <div class="step-item active">
            <div class="step-dot active">01</div>
            <span class="step-label">{{ __('Vos informations') }}</span>
            <i class="fa-solid fa-chevron-right step-arrow"></i>
        </div>
        <div class="step-item active" style="background:linear-gradient(90deg,rgba(255,183,3,.14),rgba(255,183,3,.04));color:white;border:1px solid rgba(255,183,3,.12);">
            <div class="step-dot" style="background:var(--blue);border-color:var(--blue);color:white;box-shadow:0 0 20px rgba(255,183,3,.25);">02</div>
            <span class="step-label">{{ __('Vos réponses') }}</span>
        </div>
        <div class="step-item">
            <div class="step-dot">03</div>
            <span class="step-label">{{ __('Récapitulatif') }}</span>
        </div>
    </nav>

    <div class="progress-section">
        <div class="progress-header">
            <span class="progress-label">{{ __('Progression') }}</span>
            <span class="progress-value" id="progressPercent">0%</span>
        </div>
        <div class="progress-bar-track">
            <div class="progress-bar-fill" id="progressFill" style="width:0%"></div>
        </div>
        <div class="progress-step-text">{{ __('Étape') }} <strong>2</strong> {{ __('sur') }} <strong>3</strong></div>
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

        <form action="{{ route('questionnaire.wizard.submit') }}" method="POST" enctype="multipart/form-data" id="wizardForm" novalidate>
            @csrf

            <div class="step-error" id="stepError" style="display:none;"></div>

            <div class="top">
                <div class="eyebrow">{{ __('PARLONS DE VOTRE PROJET') }}</div>
                <div class="counter" id="questionCounter">0 / {{ count($steps) }}</div>
            </div>

            <h1 class="question-title anim-slide-in">{{ __('Vos réponses') }}</h1>
            <p class="question-desc anim-up d1">{{ __('Répondez aux questions ci-dessous. Sélectionnez "Je ne sais pas encore" si vous n\'avez pas la réponse.') }}</p>

            @php $globalIdx = 0; @endphp
            @foreach($steps as $i => $stepDef)
                @php
                    $globalIdx++;
                    $question = $stepDef['question'];
                    $existingAnswer = $answers[$question->id] ?? null;
                    $options = $question->options->where('is_active', true)->sortBy('sort_order');
                    $hasNsa = $options->contains('value', 'nsa');
                @endphp

                <div class="q-block anim-up" style="animation-delay:{{ min($globalIdx * 40, 300) }}ms;opacity:0;animation-fill-mode:forwards;">
                    <div style="display:flex;align-items:flex-start;">
                        <span class="q-block-num">{{ $globalIdx }}</span>
                        <div style="flex:1;">
                            <span class="q-block-title">
                                {{ $question->title }}
                                @if($question->is_required)<span class="q-block-required">*</span>@endif
                            </span>
                            @if(!empty($question->description))
                                <div class="q-block-desc">{{ $question->description }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="q-block-answers">
                        {{-- TEXT --}}
                        @if($question->type === 'text')
                            <div class="input-icon-wrap" style="max-width:500px;">
                                <i class="fa-solid fa-pen input-icon"></i>
                                <input type="text" name="answers[{{ $question->id }}][value]" class="glass-input"
                                       placeholder="{{ $question->placeholder ?? __('Votre réponse…') }}"
                                       value="{{ old("answers.{$question->id}.value", $existingAnswer?->text_value ?? '') }}"
                                       {{ $question->is_required ? 'required' : '' }}>
                            </div>

                        {{-- TEXTAREA --}}
                        @elseif($question->type === 'textarea')
                            <div class="textarea-wrap" style="max-width:500px;">
                                <textarea name="answers[{{ $question->id }}][value]" class="glass-input" rows="3"
                                          placeholder="{{ $question->placeholder ?? __('Décrivez brièvement…') }}"
                                          {{ $question->is_required ? 'required' : '' }}>{{ old("answers.{$question->id}.value", $existingAnswer?->text_value ?? '') }}</textarea>
                                <span class="char-counter"><span class="current">0</span>/500</span>
                            </div>

                        {{-- NUMBER --}}
                        @elseif($question->type === 'number')
                            <div class="input-icon-wrap" style="max-width:500px;">
                                <i class="fa-solid fa-hashtag input-icon"></i>
                                <input type="number" name="answers[{{ $question->id }}][value]" class="glass-input"
                                       placeholder="{{ $question->placeholder ?? '0' }}"
                                       value="{{ old("answers.{$question->id}.value", $existingAnswer?->text_value ?? '') }}"
                                       {{ $question->is_required ? 'required' : '' }}>
                            </div>

                        {{-- DATE --}}
                        @elseif($question->type === 'date')
                            <div class="input-icon-wrap" style="max-width:500px;">
                                <i class="fa-solid fa-calendar input-icon"></i>
                                <input type="date" name="answers[{{ $question->id }}][value]" class="glass-input"
                                       value="{{ old("answers.{$question->id}.value", $existingAnswer?->text_value ?? '') }}"
                                       {{ $question->is_required ? 'required' : '' }}>
                            </div>

                        {{-- YES/NO --}}
                        @elseif($question->type === 'yes_no')
                            @php $currentVal = old("answers.{$question->id}.value", $existingAnswer?->text_value ?? ''); @endphp
                            <input type="hidden" name="answers[{{ $question->id }}][value]" value="{{ $currentVal }}" data-yesno="{{ $question->id }}">
                            <div class="option-grid" style="grid-template-columns:repeat(2,1fr);max-width:500px;">
                                <label class="opt-card yesno-card {{ $currentVal === 'yes' ? 'selected' : '' }}"
                                       data-group="yesno-{{ $question->id }}" data-value="yes" data-question="{{ $question->id }}">
                                    <div class="opt-check"><i class="fa-solid fa-check" style="font-size:10px;"></i></div>
                                    <div class="yesno-icon" style="background:rgba(255,183,3,0.12);color:#ffb703;">
                                        <i class="fa-solid fa-check"></i>
                                    </div>
                                    <div class="opt-label">{{ __('Oui') }}</div>
                                </label>
                                <label class="opt-card yesno-card {{ $currentVal === 'no' ? 'selected' : '' }}"
                                       data-group="yesno-{{ $question->id }}" data-value="no" data-question="{{ $question->id }}">
                                    <div class="opt-check" style="background:#f87171;"><i class="fa-solid fa-xmark" style="font-size:10px;"></i></div>
                                    <div class="yesno-icon" style="background:rgba(255,183,3,0.12);color:#ffb703;">
                                        <i class="fa-solid fa-xmark"></i>
                                    </div>
                                    <div class="opt-label">{{ __('Non') }}</div>
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
                                $isNsa = $selectedId && $existingAnswer && $existingAnswer->answerOptions->first()?->questionOption?->value === 'nsa';
                            @endphp
                            <div style="display:flex;flex-direction:column;gap:10px;max-width:500px;">
                                <div class="input-icon-wrap">
                                    <i class="fa-solid fa-list input-icon"></i>
                                    <select name="answers[{{ $question->id }}][option_ids][]" class="glass-input"
                                            {{ $question->is_required ? 'required' : '' }}
                                            data-qid="{{ $question->id }}" data-other-option="{{ $otherOption?->id ?? '' }}">
                                        <option value="">{{ __('— Sélectionnez —') }}</option>
                                        @foreach($options as $option)
                                            <option value="{{ $option->id }}" {{ $selectedId == $option->id ? 'selected' : '' }}>{{ $option->label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @if($otherOption)
                                    <div class="other-field" style="display:{{ $showOther ? 'block' : 'none' }};" data-other-for="{{ $question->id }}">
                                        <div class="textarea-wrap">
                                            <textarea name="answers[{{ $question->id }}][other_value]" class="glass-input" rows="2"
                                                      placeholder="{{ __('Précisez votre réponse…') }}">{{ old("answers.{$question->id}.other_value", $existingAnswer?->other_value ?? '') }}</textarea>
                                            <span class="char-counter"><span class="current">0</span>/500</span>
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
                                $isNsa = in_array('nsa', collect($options->where('value', 'nsa'))->pluck('id')->toArray()) && count(array_intersect($selectedValues, $options->where('value', 'nsa')->pluck('id')->toArray())) > 0;
                            @endphp
                            <div style="display:flex;flex-direction:column;gap:10px;">
                                <div class="option-grid" data-other-option="{{ $otherOption?->id }}">
                                    @foreach($options as $option)
                                        @php $sel = in_array($option->id, $selectedValues); @endphp
                                        <label class="opt-card {{ $sel ? 'selected' : '' }}"
                                               data-group="multi-{{ $question->id }}" data-toggle="true"
                                               data-value="{{ $option->id }}" data-question="{{ $question->id }}"
                                               data-option-value="{{ $option->value }}">
                                            <div class="opt-check"><i class="fa-solid fa-check" style="font-size:9px;"></i></div>
                                            <div class="opt-label">{{ $option->label }}</div>
                                            <input type="checkbox" name="answers[{{ $question->id }}][option_ids][]"
                                                   value="{{ $option->id }}" {{ $sel ? 'checked' : '' }} class="hidden">
                                        </label>
                                    @endforeach
                                </div>
                                @if($otherOption)
                                    <div class="other-field" style="display:{{ $showOther ? 'block' : 'none' }};" data-other-for="{{ $question->id }}">
                                        <div class="textarea-wrap">
                                            <textarea name="answers[{{ $question->id }}][other_value]" class="glass-input" rows="2"
                                                      placeholder="{{ __('Précisez votre réponse…') }}">{{ old("answers.{$question->id}.other_value", $existingAnswer?->other_value ?? '') }}</textarea>
                                            <span class="char-counter"><span class="current">0</span>/500</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                        {{-- FILE --}}
                        @elseif($question->type === 'file')
                            <div>
                                <div class="dropzone" data-dropzone="{{ $question->id }}">
                                    <input type="file" name="answers[{{ $question->id }}][file]"
                                           data-file-input="{{ $question->id }}" class="hidden"
                                           accept=".png,.jpg,.jpeg,.gif,.pdf,.svg,.doc,.docx,.zip">
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
                                <div data-file-list="{{ $question->id }}" style="display:flex;flex-direction:column;gap:8px;margin-top:8px;"></div>
                                @if($existingAnswer && !empty($existingAnswer->file_path))
                                    <div style="padding:12px 16px;display:flex;align-items:center;gap:10px;font-size:13px;margin-top:8px;border-radius:var(--radius-xs);background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.15);">
                                        <i class="fa-solid fa-file-check" style="color:#ffb703;"></i>
                                        <span style="color:var(--text-secondary);">{{ __('Fichier déjà envoyé') }}</span>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- "Je ne sais pas encore" --}}
                    @if($hasNsa && $question->type !== 'file')
                        <div class="q-block-skip">
                            <a href="#" class="skip-link" data-qid="{{ $question->id }}">
                                <i class="fa-solid fa-circle-question" style="font-size:11px;"></i> {{ __('Je ne sais pas encore') }}
                            </a>
                        </div>
                    @endif
                </div>
            @endforeach

            <div class="card-bottom anim-up d5">
                <a href="{{ route('questionnaire.index') }}" class="btn-back" style="text-decoration:none;">
                    <i class="fa-solid fa-arrow-left"></i> {{ __('Retour') }}
                </a>
                <button type="submit" class="btn-continue">
                    {{ __('Voir le récapitulatif') }} <i class="fa-solid fa-paper-plane"></i>
                </button>
            </div>
        </form>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const errorEl = document.getElementById('stepError');

    function clearError() {
        if (errorEl) { errorEl.style.display = 'none'; errorEl.textContent = ''; }
    }

    function showError(msg) {
        if (!errorEl) return;
        errorEl.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> ' + msg;
        errorEl.style.display = 'flex';
        errorEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function updateProgress() {
        const allInputs = document.querySelectorAll('#wizardForm input[name^="answers["], #wizardForm textarea[name^="answers["], #wizardForm select[name^="answers["]');
        const qids = new Set();
        allInputs.forEach(el => {
            const m = el.name.match(/answers\[(\d+)\]/);
            if (m) qids.add(m[1]);
        });

        let answered = 0;
        qids.forEach(qid => {
            const textEl = document.querySelector(`input[name="answers[${qid}][value]"]`);
            const areaEl = document.querySelector(`textarea[name="answers[${qid}][value]"]`);
            const selectEl = document.querySelector(`select[data-qid="${qid}"]`);
            const checkboxes = document.querySelectorAll(`input[name="answers[${qid}][option_ids][]"]:checked`);

            if (textEl && textEl.value.trim()) { answered++; }
            else if (areaEl && areaEl.value.trim()) { answered++; }
            else if (selectEl && selectEl.value) { answered++; }
            else if (checkboxes.length > 0) { answered++; }
        });

        const total = qids.size;
        const pct = total > 0 ? Math.round((answered / total) * 100) : 0;
        document.getElementById('progressPercent').textContent = pct + '%';
        document.getElementById('progressFill').style.width = pct + '%';
        document.getElementById('questionCounter').textContent = answered + ' / ' + total;
    }

    // "Je ne sais pas encore" links
    document.querySelectorAll('.skip-link').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const qid = link.dataset.qid;

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

            const selectEl = document.querySelector(`select[data-qid="${qid}"]`);
            if (selectEl) {
                const nsaOpt = Array.from(selectEl.options).find(o => o.text.includes('Je ne sais pas encore'));
                if (nsaOpt) selectEl.value = nsaOpt.value;
            }

            document.querySelectorAll(`input[name="answers[${qid}][option_ids][]"]`).forEach(cb => {
                const card = cb.closest('.opt-card');
                const label = card ? card.querySelector('.opt-label') : null;
                if (label && label.textContent.includes('Je ne sais pas encore')) {
                    cb.checked = true;
                    if (card) {
                        card.classList.add('selected');
                        const ch = card.querySelector('.opt-check');
                        if (ch) { ch.style.opacity = '1'; ch.style.transform = 'scale(1)'; }
                    }
                } else {
                    cb.checked = false;
                    if (card) {
                        card.classList.remove('selected');
                        const ch = card.querySelector('.opt-check');
                        if (ch) { ch.style.opacity = '0'; ch.style.transform = 'scale(0)'; }
                    }
                }
            });

            const textInput = document.querySelector(`input[name="answers[${qid}][value]"], textarea[name="answers[${qid}][value]"]`);
            if (textInput) textInput.value = '';

            link.classList.add('skipped');
            link.innerHTML = '<i class="fa-solid fa-check" style="font-size:11px;"></i> {{ __("Passé") }}';

            updateProgress();
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
            updateProgress();
        });
    });

    // Multiselect toggle cards
    document.querySelectorAll('.opt-card[data-toggle="true"]').forEach(card => {
        card.addEventListener('click', (e) => {
            e.preventDefault();
            const optionValue = card.dataset.optionValue;
            const isExclusive = optionValue === 'rien';
            const qid = card.dataset.question;
            const grid = card.closest('.option-grid');

            if (isExclusive && !card.classList.contains('selected')) {
                // Deselect all other cards in the same question
                grid.querySelectorAll('.opt-card').forEach(other => {
                    if (other !== card) {
                        other.classList.remove('selected');
                        const cb = other.querySelector('input[type=checkbox]');
                        if (cb) cb.checked = false;
                        const check = other.querySelector('.opt-check');
                        if (check) { check.style.opacity = '0'; check.style.transform = 'scale(0)'; }
                        // Hide other field if it was the "autre" card
                        const otherField = document.querySelector(`[data-other-for="${qid}"]`);
                        if (otherField && other.dataset.optionValue === 'autre') {
                            otherField.style.display = 'none';
                        }
                    }
                });
            } else if (!isExclusive) {
                // Deselect any exclusive card in the same question
                grid.querySelectorAll('.opt-card').forEach(excl => {
                    if (excl !== card && excl.dataset.optionValue === 'rien') {
                        excl.classList.remove('selected');
                        const cb = excl.querySelector('input[type=checkbox]');
                        if (cb) cb.checked = false;
                        const check = excl.querySelector('.opt-check');
                        if (check) { check.style.opacity = '0'; check.style.transform = 'scale(0)'; }
                    }
                });
            }

            card.classList.toggle('selected');
            const cb = card.querySelector('input[type=checkbox]');
            if(cb) cb.checked = card.classList.contains('selected');
            const check = card.querySelector('.opt-check');
            if(check) {
                check.style.opacity = card.classList.contains('selected') ? '1' : '0';
                check.style.transform = card.classList.contains('selected') ? 'scale(1)' : 'scale(0)';
            }
            const optId = parseInt(card.dataset.value);
            const otherField = document.querySelector(`[data-other-for="${qid}"]`);
            if (otherField) {
                const otherOptionId = grid ? parseInt(grid.dataset.otherOption) : 0;
                if (otherOptionId && optId === otherOptionId) {
                    otherField.style.display = card.classList.contains('selected') ? 'block' : 'none';
                }
            }
            updateProgress();
        });
    });

    // Select change for "other" field
    document.querySelectorAll('select[data-qid]').forEach(selectEl => {
        const qid = selectEl.dataset.qid;
        const otherOptionId = selectEl.dataset.otherOption;
        selectEl.addEventListener('change', () => {
            const field = document.querySelector(`[data-other-for="${qid}"]`);
            if(field) field.style.display = selectEl.value == otherOptionId ? 'block' : 'none';
            updateProgress();
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
        fi.addEventListener('change', () => { showWizardFiles(fi.files, fl); updateProgress(); });
    });

    function showWizardFiles(files, container) {
        if(!container) return;
        container.innerHTML = '';
        Array.from(files).forEach(f => {
            const div = document.createElement('div');
            div.style.cssText = 'padding:10px 14px;display:flex;align-items:center;gap:10px;font-size:13px;border-radius:12px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);min-width:0;';
            div.innerHTML = '<i class="fa-solid fa-file" style="color:#ffc72c;flex-shrink:0;"></i><span style="color:white;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0;">' + f.name + '</span><span style="margin-left:auto;color:var(--text-muted);font-size:11px;flex-shrink:0;">' + (f.size/1024).toFixed(0) + ' KB</span>';
            container.appendChild(div);
        });
    }

    // Form validation on submit
    document.getElementById('wizardForm').addEventListener('submit', (e) => {
        const requiredInputs = document.querySelectorAll('[required]');
        let hasError = false;
        requiredInputs.forEach(el => {
            if (!el.value || !el.value.trim()) {
                hasError = true;
            }
        });

        // Check required selects
        document.querySelectorAll('select[required]').forEach(sel => {
            if (!sel.value) hasError = true;
        });

        if (hasError) {
            e.preventDefault();
            showError('{{ __("Veuillez répondre à toutes les questions obligatoires.") }}');
            return;
        }
        clearError();
    });

    // Clear validation error on any user interaction
    document.getElementById('wizardForm').addEventListener('input', clearError);
    document.getElementById('wizardForm').addEventListener('change', clearError);

    updateProgress();
});
</script>
</x-layouts.app>
