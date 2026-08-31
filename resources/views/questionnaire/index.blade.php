<x-layouts.app :title="'Démarrer le questionnaire'">

<style>
#wizardForm { display:flex; flex-direction:column; flex:1; }
.content-card { overflow:visible !important; }
.sidebar { position:fixed; top:0; left:0; bottom:0; width:270px; padding:34px 24px; background:rgba(7,20,38,.92); border-right:1px solid rgba(255,255,255,.08); backdrop-filter:blur(20px); display:flex; flex-direction:column; z-index:100; }
.sidebar-header { margin-bottom:40px; }
.sidebar-logo { display:flex; align-items:center; gap:12px; }
.sidebar-logo img { height:45px; width:auto; object-fit:contain; }
.sidebar-logo span { font-size:17px; font-weight:800; letter-spacing:-.4px; color:white; }
.sidebar-label { margin-bottom:18px; color:rgba(255,255,255,.32); font-size:10px; font-weight:700; letter-spacing:1.8px; }
.step-list { display:flex; flex-direction:column; gap:2px; overflow-y:auto; flex:1; }
.step-item { position:relative; display:flex; align-items:center; gap:10px; min-height:40px; padding:6px 8px; border-radius:10px; color:rgba(255,255,255,.35); transition:background .25s ease, color .25s ease; }
.step-dot { width:28px; height:28px; flex-shrink:0; display:grid; place-items:center; border-radius:50%; background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.06); color:rgba(255,255,255,.35); font-size:9px; font-weight:800; }
.step-label { font-size:12px; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.step-item.active { background:linear-gradient(90deg,rgba(255,183,3,.14),rgba(255,183,3,.04)); color:white; border:1px solid rgba(255,183,3,.12); }
.step-dot.active { background:var(--blue); border-color:var(--blue); color:white; box-shadow:0 0 20px rgba(255,183,3,.25); }
.step-arrow { margin-left:auto; color:var(--blue-light); font-size:11px; }
.progress-section { margin-top:auto; padding-top:12px; }
.progress-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; }
.progress-label { font-size:11px; color:rgba(255,255,255,.35); }
.progress-value { font-size:11px; color:var(--blue-light); font-weight:700; }
.progress-bar-track { height:5px; width:100%; overflow:hidden; border-radius:20px; background:rgba(255,255,255,.08); }
.progress-bar-fill { display:block; height:100%; border-radius:20px; background:linear-gradient(90deg,#e6a400,#ffb703,#ffc72c); box-shadow:0 0 15px rgba(255,183,3,.4); transition:width .6s cubic-bezier(0.16,1,0.3,1); }
.progress-step-text { margin-top:8px; font-size:10px; color:rgba(255,255,255,.28); }
.main-content { position:relative; z-index:1; min-height:100vh; margin-left:270px; padding:40px 55px; display:flex; align-items:center; justify-content:center; }
.content-card { width:100%; max-width:950px; min-height:650px; padding:55px 60px; border-radius:30px; background:linear-gradient(145deg,rgba(18,44,77,.88),rgba(7,20,38,.94)); border:1px solid rgba(255,255,255,.08); box-shadow:0 35px 100px rgba(0,0,0,.35); backdrop-filter:blur(20px); display:flex; flex-direction:column; transform-style:preserve-3d; }
.top { display:flex; align-items:center; justify-content:space-between; }
.eyebrow { color:var(--blue-light); font-size:11px; font-weight:800; letter-spacing:2px; }
.question-title { max-width:750px; margin:50px 0 15px; font-size:clamp(40px,5vw,64px); line-height:1; letter-spacing:-2.5px; color:white; }
.question-desc { max-width:700px; margin:0 0 35px; color:rgba(255,255,255,.48); font-size:14px; line-height:1.7; }
.option-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:15px; }
.opt-card { position:relative; padding:22px; border:1px solid rgba(255,255,255,.09); border-radius:18px; background:rgba(255,255,255,.035); color:rgba(255,255,255,.8); cursor:pointer; transition:transform .25s ease, border .25s ease, background .25s ease, box-shadow .25s ease; }
.opt-card:hover { transform:translateY(-4px); background:rgba(255,183,3,.06); border-color:rgba(255,183,3,.35); }
.opt-card.selected { transform:translateY(-4px); background:linear-gradient(135deg,rgba(255,183,3,.14),rgba(255,183,3,.04)); border-color:rgba(255,183,3,.7); color:white; box-shadow:0 15px 35px rgba(0,0,0,.18),0 0 25px rgba(255,183,3,.08); }
.opt-check { position:absolute; top:14px; right:14px; width:22px; height:22px; display:grid; place-items:center; border-radius:50%; background:var(--blue); color:white; opacity:0; transform:scale(0); transition:opacity .2s ease, transform .2s ease; }
.opt-card.selected .opt-check { opacity:1; transform:scale(1); }
.opt-icon-wrap { width:40px; height:40px; display:grid; place-items:center; border-radius:12px; font-size:15px; }
.opt-label { font-size:14px; font-weight:800; }
.opt-desc { margin-top:12px; font-size:12px; line-height:1.6; color:rgba(255,255,255,.42); }
.form-label { display:block; margin-bottom:8px; color:rgba(255,255,255,.58); font-size:11px; font-weight:700; }
.input-icon-wrap { position:relative; width:100%; }
.input-icon { position:absolute; left:16px; top:50%; transform:translateY(-50%); color:rgba(255,255,255,.35); font-size:13px; pointer-events:none; z-index:2; }
.glass-input { width:100%; height:48px; padding:0 16px 0 45px; border:1px solid rgba(255,255,255,.09); border-radius:12px; outline:none; background:rgba(255,255,255,.035); color:white; font-size:13px; transition:border-color .2s ease, background .2s ease, box-shadow .2s ease; }
.glass-input::placeholder { color:rgba(255,255,255,.28); }
.glass-input:focus { border-color:rgba(255,183,3,.55); background:rgba(255,183,3,.05); box-shadow:0 0 0 3px rgba(255,183,3,.08); }
.card-bottom { margin-top:auto; padding-top:35px; display:flex; align-items:center; gap:25px; }
.btn-continue { display:inline-flex; align-items:center; gap:10px; padding:16px 25px; border:none; border-radius:13px; background:#ffb703; color:white; font-size:14px; font-weight:800; cursor:pointer; transition:transform .25s ease, background .25s ease, box-shadow .25s ease; }
.btn-continue:hover { background:#ffc72c; transform:translateY(-2px); box-shadow:0 12px 30px rgba(255,183,3,.25); }
.btn-continue:disabled { opacity:.4; cursor:not-allowed; transform:none; box-shadow:none; }
.error-box { margin-bottom:20px; padding:15px; border-radius:12px; background:rgba(248,113,113,.08); border:1px solid rgba(248,113,113,.15); }
.error-header { display:flex; align-items:center; gap:8px; margin-bottom:8px; color:#f87171; font-size:13px; font-weight:700; }
.error-msg { color:rgba(255,255,255,.65); font-size:12px; margin-top:4px; }
.section-label { font-size:11px; font-weight:800; color:rgba(255,255,255,.35); letter-spacing:1.5px; text-transform:uppercase; margin-bottom:14px; margin-top:24px; }
@media (max-width:1024px) {
    .sidebar { width:240px; padding:24px 20px; }
    .sidebar-logo img { height:36px; }
    .step-item { min-height:36px; padding:5px 6px; }
    .step-dot { width:24px; height:24px; font-size:8px; }
    .step-label { font-size:11px; }
    .main-content { margin-left:240px; padding:30px 35px; }
    .content-card { padding:40px 45px; }
    .question-title { font-size:clamp(32px,4.5vw,52px); }
}
@media (max-width:750px) {
    .sidebar { position:fixed; top:0; left:0; bottom:0; width:270px; transform:translateX(-100%); transition:transform .35s cubic-bezier(.4,0,.2,1); z-index:200; }
    .sidebar.open { transform:translateX(0); }
    .sidebar-label { display:none; }
    .step-list { flex-direction:column; gap:4px; }
    .step-item { padding:6px 8px; min-height:40px; background:transparent !important; border:none !important; }
    .step-dot { width:28px; height:28px; }
    .progress-section { margin-top:12px; }
    .main-content { margin-left:0; min-height:auto; padding:16px; align-items:flex-start; }
    .content-card { min-height:auto; padding:24px 18px; border-radius:20px; }
    .question-title { margin:24px 0 12px; font-size:28px; letter-spacing:-1px; line-height:1.1; }
    .option-grid { grid-template-columns:1fr !important; gap:10px; }
    .opt-card { padding:18px; }
    .card-bottom { flex-direction:column; align-items:stretch; gap:10px; }
    .btn-continue { width:100%; justify-content:center; min-height:48px; }
    .qm-hamburger { display:flex; }
    .qm-overlay { display:block; }
}
@media (pointer: coarse) {
    .opt-card { min-height:48px; }
    .btn-continue { min-height:48px; }
    .glass-input { height:48px; }
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

<aside class="sidebar" id="qmSidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <img src="{{ asset('IMAGES/Logo dominium.png') }}" alt="DOMINIUM">
            <span>DOMINIUM TECH</span>
        </div>
    </div>

    <div class="sidebar-label">{{ __('VOTRE PROGRESSION') }}</div>

    <nav class="step-list">
        <div class="step-item active" data-step="1">
            <div class="step-dot active">01</div>
            <span class="step-label">{{ __('Type de projet') }}</span>
            <i class="fa-solid fa-chevron-right step-arrow"></i>
        </div>
        <div class="step-item" data-step="2">
            <div class="step-dot">02</div>
            <span class="step-label">{{ __('Vos informations') }}</span>
        </div>
    </nav>

    <div class="progress-section">
        <div class="progress-header">
            <span class="progress-label">{{ __('Progression') }}</span>
            <span class="progress-value">0%</span>
        </div>
        <div class="progress-bar-track">
            <div class="progress-bar-fill" style="width:0%"></div>
        </div>
        <div class="progress-step-text">{{ __('Étape') }} <strong>1</strong> {{ __('sur') }} <strong>2</strong></div>
    </div>
</aside>

<div class="qm-overlay" id="qmOverlay"></div>

<main class="main-content">
    <div class="content-card anim-scale">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
            <button class="qm-hamburger" id="qmToggle" aria-label="Menu">
                <div class="bars"><span></span><span></span><span></span></div>
            </button>
            <span style="font-size:12px;font-weight:700;color:var(--blue-light);letter-spacing:1.5px;">DOMINIUM TECH</span>
        </div>

        @if($errors->any())
            <div class="error-box anim-up d1">
                <div class="error-header"><i class="fa-solid fa-circle-exclamation"></i><span>{{ __('Erreur') }}</span></div>
                @foreach($errors->all() as $error)
                    <div class="error-msg">{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('questionnaire.start') }}" method="POST" id="wizardForm">
            @csrf

            {{-- STEP 1: Project Type --}}
            <div class="wizard-step active" id="step-1">
                <div class="top">
                    <div class="eyebrow">{{ __('PARLONS DE VOTRE PROJET') }}</div>
                </div>
                <h1 class="question-title anim-slide-in">{{ __('Type de projet') }}</h1>
                <p class="question-desc anim-up d1">{{ __('Quel type de projet souhaitez-vous réaliser ?') }}</p>

                <div class="option-grid anim-up d2" style="grid-template-columns:repeat(2,1fr);">
                    @foreach($projectTypes as $key => $type)
                        <label class="opt-card" data-group="project_types" data-value="{{ $key }}">
                            <div class="opt-check"><i class="fa-solid fa-check" style="font-size:10px;"></i></div>
                            <div class="opt-icon-wrap" style="background:{{ $type['color'] }}20;color:{{ $type['color'] }};">
                                <i class="{{ $type['icon'] }}"></i>
                            </div>
                            <div class="opt-label">{{ $type['label'] }}</div>
                            <div class="opt-desc">{{ $type['description'] }}</div>
                        </label>
                    @endforeach
                </div>

                <input type="hidden" name="project_types" id="projectTypesInput" value="{{ json_encode($selectedProjectTypes) }}">

                <div class="card-bottom anim-up d3">
                    <div></div>
                    <button type="button" class="btn-continue" id="btnStep1">
                        {{ __('Continuer') }} <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            {{-- STEP 2: Client Info --}}
            <div class="wizard-step" id="step-2" style="display:none;">
                <div class="top">
                    <div class="eyebrow">{{ __('VOS INFORMATIONS') }}</div>
                </div>
                <h1 class="question-title anim-slide-in">{{ __('Qui êtes-vous ?') }}</h1>
                <p class="question-desc anim-up d1">{{ __('On a juste besoin de votre nom et numéro pour vous recontacter.') }}</p>

                <div style="display:flex;flex-direction:column;gap:20px;" class="anim-up d2">
                    <div>
                        <label class="form-label">{{ __('Nom complet') }} <span style="color:#f87171;">*</span></label>
                        <div class="input-icon-wrap">
                            <i class="fa-solid fa-user input-icon"></i>
                            <input type="text" name="name" value="{{ old('name') }}" class="glass-input" placeholder="Jean Dupont" required>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">{{ __('Numéro joignable') }} <span style="color:#f87171;">*</span></label>
                        <div class="input-icon-wrap">
                            <i class="fa-solid fa-phone input-icon"></i>
                            <input type="tel" name="phone" value="{{ old('phone') }}" class="glass-input" placeholder="+225 07 07 07 07" required>
                        </div>
                    </div>
                </div>

                <div class="card-bottom anim-up d3">
                    <button type="button" class="btn-back" onclick="showStep(1)">
                        <i class="fa-solid fa-arrow-left"></i> {{ __('Retour') }}
                    </button>
                    <button type="submit" class="btn-continue">
                        {{ __('Commencer le questionnaire') }} <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</main>

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

    const typesInput = document.getElementById('projectTypesInput');

    document.querySelectorAll('.opt-card[data-group="project_types"]').forEach(card => {
        card.addEventListener('click', () => {
            document.querySelectorAll('.opt-card[data-group="project_types"]').forEach(c => {
                c.classList.remove('selected');
                const ch = c.querySelector('.opt-check');
                if(ch) { ch.style.opacity = '0'; ch.style.transform = 'scale(0)'; }
            });
            card.classList.add('selected');
            const ch = card.querySelector('.opt-check');
            if(ch) { ch.style.opacity = '1'; ch.style.transform = 'scale(1)'; }
            card.style.transform = 'translateY(-6px) scale(0.97)';
            setTimeout(() => { card.style.transform = 'translateY(-4px) scale(1)'; }, 100);
            typesInput.value = JSON.stringify([card.dataset.value]);
            typesInput.dispatchEvent(new Event('change'));
        });
    });

    window.showStep = function(step) {
        document.querySelectorAll('.wizard-step').forEach(s => {
            s.style.display = 'none';
            s.classList.remove('active');
        });
        const target = document.getElementById('step-' + step);
        if (target) {
            target.style.display = '';
            target.classList.add('active');
            target.querySelectorAll('.anim-up, .anim-slide-in, .anim-scale').forEach(el => {
                el.style.animation = 'none';
                el.offsetHeight;
                el.style.animation = '';
            });
        }
        document.querySelectorAll('.step-item').forEach(item => {
            const s = parseInt(item.dataset.step);
            item.classList.remove('active');
            const dot = item.querySelector('.step-dot');
            dot.classList.remove('active');
            if (s === step) {
                item.classList.add('active');
                dot.classList.add('active');
            } else if (s < step) {
                dot.innerHTML = '<i class="fa-solid fa-check"></i>';
            }
        });
    };

    document.getElementById('btnStep1').addEventListener('click', () => {
        const val = typesInput.value;
        if (!val || val === '[]' || val === 'null') return;
        showStep(2);
    });
});
</script>
</x-layouts.app>
