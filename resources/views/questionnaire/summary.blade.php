<x-layouts.app :title="'Récapitulatif'">

<style>
@media (max-width:750px) {
    .sidebar { position:fixed; top:0; left:0; bottom:0; width:270px; transform:translateX(-100%); transition:transform .35s cubic-bezier(.4,0,.2,1); z-index:200; }
    .sidebar.open { transform:translateX(0); }
    .sidebar-label { display:none; }
    .step-list { flex-direction:column; gap:4px; }
    .step-item { padding:6px 8px; min-height:40px; }
    .step-dot { width:28px; height:28px; }
    .progress-section { margin-top:12px; }
    .main-content { margin-left:0; min-height:auto; padding:16px; align-items:flex-start; }
    .content-card { min-height:auto; padding:24px 18px; border-radius:20px; }
    .question-title { margin:24px 0 12px; font-size:28px; letter-spacing:-1px; line-height:1.1; }
    .card-bottom { flex-direction:column; align-items:stretch; gap:10px; }
    .btn-continue { width:100%; justify-content:center; min-height:48px; }
    .qm-hamburger { display:flex; }
    .qm-overlay { display:block; }
}
.qm-hamburger {
    display:none; align-items:center; justify-content:center;
    width:44px; height:44px; border:none; background:rgba(255,255,255,.06);
    color:white; font-size:18px; cursor:pointer; border-radius:12px;
    transition:background .2s; position:relative; z-index:210;
}
.qm-hamburger:hover { background:rgba(255,255,255,.12); }
.qm-hamburger .bars {
    width:20px; height:14px; display:flex; flex-direction:column;
    justify-content:space-between; position:relative;
}
.qm-hamburger .bars span {
    display:block; width:100%; height:2px; background:white; border-radius:2px;
    transition:all .3s cubic-bezier(.4,0,.2,1); transform-origin:center;
}
.qm-hamburger.active .bars span:nth-child(1) { transform:translateY(6px) rotate(45deg); }
.qm-hamburger.active .bars span:nth-child(2) { opacity:0; transform:scaleX(0); }
.qm-hamburger.active .bars span:nth-child(3) { transform:translateY(-6px) rotate(-45deg); }
.qm-overlay {
    display:none; position:fixed; inset:0; background:rgba(0,0,0,.5);
    z-index:199; opacity:0; transition:opacity .3s ease; pointer-events:none;
}
.qm-overlay.visible { opacity:1; pointer-events:auto; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const qmToggle = document.getElementById('qmToggle');
    const qmSidebar = document.getElementById('qmSidebar');
    const qmOverlay = document.getElementById('qmOverlay');
    if (qmToggle && qmSidebar) {
        qmToggle.addEventListener('click', () => {
            qmSidebar.classList.toggle('open');
            qmToggle.classList.toggle('active');
            qmOverlay.classList.toggle('visible');
        });
    }
    if (qmOverlay && qmSidebar) {
        qmOverlay.addEventListener('click', () => {
            qmSidebar.classList.remove('open');
            qmToggle.classList.remove('active');
            qmOverlay.classList.remove('visible');
        });
    }
});
</script>

    <aside class="sidebar" id="qmSidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <img src="{{ asset('IMAGES/Logo dominium.png') }}" alt="DOMINIUM">
                <span>DOMINIUM TECH</span>
            </div>
        </div>

        <div class="sidebar-label">{{ __('VOTRE PROGRESSION') }}</div>

        <nav class="step-list">
            <div class="step-item done">
                <div class="step-dot done"><i class="fa-solid fa-check" style="font-size:11px;"></i></div>
                <span class="step-label">{{ __('Type de projet') }}</span>
            </div>
            <div class="step-item done">
                <div class="step-dot done"><i class="fa-solid fa-check" style="font-size:11px;"></i></div>
                <span class="step-label">{{ __('Vos informations') }}</span>
            </div>
            <div class="step-item done">
                <div class="step-dot done"><i class="fa-solid fa-check" style="font-size:11px;"></i></div>
                <span class="step-label">{{ __('Vos réponses') }}</span>
            </div>
            <div class="step-item active">
                <div class="step-dot active"><i class="fa-solid fa-clipboard-check" style="font-size:10px;"></i></div>
                <span class="step-label">{{ __('Récapitulatif') }}</span>
                <i class="fa-solid fa-chevron-right step-arrow"></i>
            </div>
        </nav>

        <div class="progress-section">
            <div class="progress-header">
                <span class="progress-label">{{ __('Progression') }}</span>
                <span class="progress-value">{{ $progress['percentage'] }}%</span>
            </div>
            <div class="progress-bar-track">
                <div class="progress-bar-fill" style="width: {{ $progress['percentage'] }}%"></div>
            </div>
            <div class="progress-step-text">{{ __('Récapitulatif') }}</div>
        </div>
    </aside>


    <div class="qm-overlay" id="qmOverlay"></div>

    <main class="main-content">
        <div class="content-card anim-scale">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                <button class="qm-hamburger" id="qmToggle" aria-label="Menu">
                    <div class="bars"><span></span><span></span><span></span></div>
                </button>
                <span style="font-size:12px;font-weight:700;color:var(--yellow);letter-spacing:1.5px;">DOMINIUM TECH</span>
            </div>

            <div class="card-progress-top">
                <div class="card-progress-top-fill" style="width: {{ $progress['percentage'] }}%"></div>
            </div>

            <div class="top">
                <div class="eyebrow">{{ __('PARLONS DE VOTRE PROJET') }}</div>
                <div class="counter">{{ str_pad(count($summary['questions']), 2, '0', STR_PAD_LEFT) }} / {{ str_pad(count($summary['questions']), 2, '0', STR_PAD_LEFT) }}</div>
            </div>

            <h1 class="question-title anim-up d1">
                {{ __('Récapitulatif') }}
            </h1>
            <p class="question-desc anim-up d2">{{ __('Vérifiez vos réponses avant de soumettre votre demande.') }}</p>

            @if(!empty($summary['project']))
                <div style="padding:24px;border-radius:var(--radius);background:rgba(255,255,255,0.035);border:1px solid rgba(255,255,255,0.08);margin-top:12px;" class="anim-up d3">
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                        <div style="width:36px;height:36px;border-radius:10px;background:rgba(255,183,3,0.12);display:flex;align-items:center;justify-content:center;font-size:14px;color:#ffb703;">
                            <i class="fa-solid fa-building"></i>
                        </div>
                        <span style="font-size:11px;font-weight:700;color:rgba(255,255,255,0.65);text-transform:uppercase;letter-spacing:0.06em;">{{ __('Type de projet') }}</span>
                    </div>
                    @php
                        $projType = $summary['project']->project_type ?? null;
                        $projTypes = is_array($projType) ? $projType : (isset($projType) ? [$projType] : []);
                        $projTypeLabels = collect($projTypes)->map(fn($t) => config("project_types.types.{$t}.label", $t))->implode(', ');
                    @endphp
                    <div style="font-size:15px;color:rgba(255,255,255,0.85);">{{ $projTypeLabels ?: '—' }}</div>
                </div>
            @endif

            @if(!empty($summary['client']))
                <div style="padding:24px;border-radius:var(--radius);background:rgba(255,255,255,0.035);border:1px solid rgba(255,255,255,0.08);margin-top:12px;" class="anim-up">
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                        <div style="width:36px;height:36px;border-radius:10px;background:var(--accent-surface);display:flex;align-items:center;justify-content:center;font-size:14px;color:var(--accent-light);">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <span style="font-size:11px;font-weight:700;color:rgba(255,255,255,0.65);text-transform:uppercase;letter-spacing:0.06em;">{{ __('Vos informations') }}</span>
                    </div>
                    <div style="font-size:14px;color:var(--text-secondary);line-height:1.9;">
                        <p><strong style="color:rgba(255,255,255,0.85);">{{ __('Nom :') }}</strong> {{ $summary['client']->name ?? '—' }}</p>
                        @if(!empty($summary['client']->phone))
                            <p><strong style="color:rgba(255,255,255,0.85);">{{ __('Téléphone :') }}</strong> {{ $summary['client']->phone }}</p>
                        @endif
                    </div>
                </div>
            @endif

            @if(!empty($summary['questions']) && count($summary['questions']) > 0)
                @foreach($summary['questions'] as $item)
                    @php
                        $question = $item['question'];
                        $answer = $item['answer'];
                        $display = $item['display_value'];
                    @endphp
                    <div style="padding:18px;border-radius:var(--radius-sm);background:rgba(255,255,255,0.035);border:1px solid rgba(255,255,255,0.08);margin-top:12px;{{ $loop->iteration <= 5 ? 'animation-delay:' . min($loop->iteration * 60, 300) . 'ms;opacity:0;animation-fill-mode:forwards;' : '' }}" class="anim-up">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;gap:8px;min-width:0;">
                            <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.06em;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $question->title }}</div>
                            <a href="{{ route('questionnaire.wizard') }}?focus={{ $question->id }}" style="font-size:11px;color:var(--accent-light);text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                                 <i class="fa-solid fa-pen" style="font-size:9px;"></i> {{ __('Modifier') }}
                            </a>
                        </div>
                        <div style="font-size:15px;color:rgba(255,255,255,0.85);font-weight:500;line-height:1.6;word-break:break-word;">
                            @if(!empty($display))
                                {{ $display }}
                            @elseif(!empty($answer) && $question->type === 'file' && !empty($answer->file_path))
                                <span style="display:flex;align-items:center;gap:6px;"><i class="fa-solid fa-paperclip" style="color:var(--accent-light);"></i> {{ __('Fichier joint') }}</span>
                            @else
                                <span style="color:var(--text-muted);font-style:italic;">{{ __('Non répondu') }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif

            @if(!empty($files) && count($files) > 0)
                <div style="padding:24px;border-radius:var(--radius);background:rgba(255,255,255,0.035);border:1px solid rgba(255,255,255,0.08);margin-top:12px;" class="anim-up">
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                        <div style="width:36px;height:36px;border-radius:10px;background:rgba(255,183,3,0.12);display:flex;align-items:center;justify-content:center;font-size:14px;color:#ffb703;">
                            <i class="fa-solid fa-paperclip"></i>
                        </div>
                        <span style="font-size:11px;font-weight:700;color:rgba(255,255,255,0.65);text-transform:uppercase;letter-spacing:0.06em;">{{ __('Fichiers joints') }}</span>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        @foreach($files as $index => $file)
                            @php
                                $fileName = is_array($file) ? ($file['original_name'] ?? '') : ($file->original_name ?? '');
                                $fileSize = is_array($file) ? ($file['size'] ?? 0) : ($file->size ?? 0);
                            @endphp
                            <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:12px;background:rgba(255,255,255,0.05);font-size:13px;min-width:0;">
                                <i class="fa-solid fa-file" style="color:var(--accent-light);flex-shrink:0;"></i>
                                <span style="color:white;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0;">{{ $fileName }}</span>
                                <span style="margin-left:auto;color:var(--text-muted);font-size:11px;flex-shrink:0;">{{ round($fileSize / 1024) }} KB</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div style="display:flex;align-items:center;gap:14px;padding:18px;border-radius:var(--radius);margin-top:24px;background:rgba(16,185,129,0.06);border:1px solid rgba(16,185,129,0.12);" class="anim-up">
                <i class="fa-solid fa-shield-halved" style="color:#ffb703;font-size:18px;"></i>
                <div style="font-size:12px;color:var(--text-secondary);line-height:1.6;">{{ __('Vos données sont transmises de manière sécurisée et ne sont jamais partagées avec des tiers.') }}</div>
            </div>

            <div class="card-bottom anim-up d5">
                <a href="{{ route('questionnaire.wizard') }}" class="btn-back" style="text-decoration:none;">
                    <i class="fa-solid fa-arrow-left"></i> {{ __('Modifier') }}
                </a>
                <div style="display:inline-flex;">
                    <form action="{{ route('questionnaire.submit') }}" method="POST" id="submitForm">
                        @csrf
                        <button type="submit" class="btn-continue" id="submitBtn">
                            {{ __('Envoyer ma demande') }} <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    {{-- Success modal --}}
    <div id="successOverlay" style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:300;display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:opacity .3s ease;">
        <div id="successBox" style="background:#fff;border-radius:12px;padding:48px 52px;max-width:560px;width:92%;box-shadow:0 20px 50px rgba(0,0,0,.25);transform:scale(.85);transition:transform .4s cubic-bezier(.175,.885,.32,1.275),opacity .3s ease;opacity:0;">
            <div style="display:flex;flex-direction:column;align-items:center;padding:40px 0 24px;">
                <svg id="checkSvg" width="120" height="120" viewBox="0 0 120 120" fill="none">
                    <path id="checkPath" d="M24 62L48 86L96 30" stroke="#ffb703" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <div id="successLabel" style="margin-top:24px;font-size:18px;font-weight:700;color:#071426;opacity:0;transform:translateY(6px);">Demande envoyée</div>
                <div id="successSubLabel" style="margin-top:8px;font-size:14px;color:rgba(7,20,38,0.5);opacity:0;transform:translateY(6px);">Vous recevrez une réponse sous 24 à 48 heures</div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var submitBtn = document.getElementById('submitBtn');
        var submitForm = document.getElementById('submitForm');
        var overlay = document.getElementById('successOverlay');
        var box = document.getElementById('successBox');
        var checkSvg = document.getElementById('checkSvg');
        var checkPath = document.getElementById('checkPath');
        var successLabel = document.getElementById('successLabel');
        var successSubLabel = document.getElementById('successSubLabel');

        if (!submitBtn || !submitForm) return;

        submitForm.addEventListener('submit', function(e) {
            e.preventDefault();

            submitBtn.style.opacity = '.5';
            submitBtn.style.pointerEvents = 'none';

            overlay.style.opacity = '1';
            overlay.style.pointerEvents = 'auto';
            requestAnimationFrame(function() {
                box.style.opacity = '1';
                box.style.transform = 'scale(1)';
            });

            checkSvg.style.transform = 'scale(0)';
            checkSvg.style.transition = 'none';

            var pathLen = checkPath.getTotalLength();
            checkPath.style.strokeDasharray = pathLen;
            checkPath.style.strokeDashoffset = pathLen;
            checkPath.style.transition = 'none';

            successLabel.style.opacity = '0';
            successLabel.style.transition = 'none';
            successSubLabel.style.opacity = '0';
            successSubLabel.style.transition = 'none';

            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    checkSvg.style.transition = 'transform .4s cubic-bezier(.175,.885,.32,1.275)';
                    checkSvg.style.transform = 'scale(1)';

                    setTimeout(function() {
                        checkPath.style.transition = 'stroke-dashoffset .55s cubic-bezier(.4,0,.2,1)';
                        checkPath.style.strokeDashoffset = '0';
                    }, 120);

                    setTimeout(function() {
                        successLabel.style.transition = 'all .3s ease';
                        successLabel.style.opacity = '1';
                        successLabel.style.transform = 'translateY(0)';
                    }, 480);

                    setTimeout(function() {
                        successSubLabel.style.transition = 'all .3s ease';
                        successSubLabel.style.opacity = '1';
                        successSubLabel.style.transform = 'translateY(0)';
                    }, 600);
                });
            });

            setTimeout(function() {
                submitForm.submit();
            }, 1500);
        });
    });
    </script>

</x-layouts.app>
