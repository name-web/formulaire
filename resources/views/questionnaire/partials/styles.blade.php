<style>
/* ═══════════════════════════════════════════════════════
   DOMINIUM — Chart Graphique Questionnaire v2
   Accent: #FFB900 | Dark: #071426 | White: #FFFFFF
   Fonts: Montserrat (titres), Inter (corps)
   ═══════════════════════════════════════════════════════ */

/* ── SIDEBAR ── */
.sidebar {
    position: fixed; top: 0; left: 0; bottom: 0; width: 270px;
    padding: 34px 24px;
    background: rgba(7,20,38,.96);
    border-right: 1px solid var(--line);
    backdrop-filter: blur(24px);
    display: flex; flex-direction: column;
    z-index: 100;
}
.sidebar-header { margin-bottom: 40px; }
.sidebar-logo { display: flex; align-items: center; gap: 12px; }
.sidebar-logo img { height: 42px; width: auto; object-fit: contain; }
.sidebar-logo span {
    font-family: 'Montserrat', sans-serif;
    font-size: 16px; font-weight: 800; letter-spacing: -.3px; color: white;
}
.sidebar-label {
    margin-bottom: 18px;
    color: rgba(255,255,255,.30);
    font-size: 9px; font-weight: 800; letter-spacing: 2px;
    text-transform: uppercase;
}
.step-list { display: flex; flex-direction: column; gap: 2px; overflow-y: auto; flex: 1; }
.step-item {
    position: relative; display: flex; align-items: center; gap: 10px;
    min-height: 40px; padding: 6px 10px; border-radius: 10px;
    color: rgba(255,255,255,.30);
    transition: background .25s ease, color .25s ease;
}
.step-dot {
    width: 28px; height: 28px; flex-shrink: 0;
    display: grid; place-items: center; border-radius: 50%;
    background: rgba(255,255,255,.05);
    border: 1.5px solid rgba(255,255,255,.08);
    color: rgba(255,255,255,.30);
    font-size: 9px; font-weight: 800;
    transition: all .25s ease;
}
.step-label {
    font-size: 12px; font-weight: 600;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.step-item.active {
    background: rgba(255,185,0,.08);
    color: white;
    border: 1px solid rgba(255,185,0,.12);
}
.step-dot.active {
    background: var(--yellow);
    border-color: var(--yellow);
    color: var(--navy);
    box-shadow: 0 0 20px rgba(255,185,0,.30);
}
.step-arrow { margin-left: auto; color: var(--blue-light); font-size: 11px; }

.progress-section { margin-top: auto; padding-top: 12px; }
.progress-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
.progress-label { font-size: 11px; color: rgba(255,255,255,.30); }
.progress-value { font-size: 11px; color: var(--yellow); font-weight: 700; }
.progress-bar-track {
    height: 4px; width: 100%; overflow: hidden; border-radius: 20px;
    background: rgba(255,255,255,.06);
}
.progress-bar-fill {
    display: block; height: 100%; border-radius: 20px;
    background: linear-gradient(90deg, #E6A400, #FFB900, #FFC72C);
    box-shadow: 0 0 12px rgba(255,185,0,.35);
    transition: width .6s cubic-bezier(0.16,1,0.3,1);
}
.progress-step-text { margin-top: 8px; font-size: 10px; color: rgba(255,255,255,.22); }

/* ── MAIN CONTENT ── */
.main-content {
    position: relative; z-index: 1;
    min-height: 100vh; margin-left: 270px;
    padding: 40px 55px;
    display: flex; align-items: center; justify-content: center;
}
.content-card {
    width: 100%; max-width: 950px; min-height: 650px;
    padding: 55px 60px; border-radius: 28px;
    background: rgba(11,27,49,.85);
    border: 1px solid rgba(255,255,255,.06);
    box-shadow: 0 40px 100px rgba(0,0,0,.40);
    backdrop-filter: blur(24px);
    display: flex; flex-direction: column;
}

/* ── HEADER ── */
.top { display: flex; align-items: center; justify-content: space-between; }
.eyebrow {
    font-family: 'Montserrat', sans-serif;
    color: var(--yellow);
    font-size: 10px; font-weight: 800; letter-spacing: 2.5px;
    text-transform: uppercase;
}
.eyebrow.muted { color: rgba(255,255,255,.30); }

/* ── TITLES ── */
.question-title {
    max-width: 750px; margin: 48px 0 16px;
    font-family: 'Montserrat', sans-serif;
    font-size: clamp(36px, 4.5vw, 56px);
    line-height: 1.02; letter-spacing: -2px;
    font-weight: 900; color: white;
    text-transform: uppercase;
}
.question-title.compact {
    font-size: clamp(28px, 3.5vw, 42px);
    letter-spacing: -1.2px;
    margin: 32px 0 12px;
}
.question-desc {
    max-width: 680px; margin: 0 0 36px;
    color: rgba(255,255,255,.42);
    font-size: 14px; line-height: 1.75;
}

/* ── OPTION CARDS ── */
.option-grid { display: grid; grid-template-columns: repeat(2,1fr); gap: 14px; }
.opt-card {
    position: relative; display: block;
    padding: 22px; border: 1.5px solid rgba(255,255,255,.07);
    border-radius: 16px;
    background: rgba(255,255,255,.025);
    color: rgba(255,255,255,.8);
    cursor: pointer;
    transition: transform .25s ease, border-color .25s ease, background .25s ease, box-shadow .25s ease;
}
.opt-card:hover {
    transform: translateY(-3px);
    background: rgba(255,185,0,.04);
    border-color: rgba(255,185,0,.25);
}
.opt-card.selected {
    transform: translateY(-3px);
    background: rgba(255,185,0,.08);
    border-color: rgba(255,185,0,.60);
    color: white;
    box-shadow: 0 12px 30px rgba(0,0,0,.20), 0 0 20px rgba(255,185,0,.06);
}
.opt-card input {
    position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
    overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; border: 0;
}
.opt-card:focus-within { outline: 2px solid rgba(255,185,0,.50); outline-offset: 2px; }
.opt-check {
    position: absolute; top: 14px; right: 14px;
    width: 22px; height: 22px;
    display: grid; place-items: center; border-radius: 50%;
    background: var(--yellow); color: var(--navy);
    opacity: 0; transform: scale(0);
    transition: opacity .2s ease, transform .2s ease;
}
.opt-card.selected .opt-check { opacity: 1; transform: scale(1); }
.opt-icon-wrap {
    width: 40px; height: 40px;
    display: grid; place-items: center; border-radius: 12px;
    font-size: 15px; margin-bottom: 14px;
}
.opt-label {
    display: block; font-size: 14px; font-weight: 700;
}
.opt-desc {
    display: block; margin-top: 8px;
    font-size: 12px; line-height: 1.6;
    color: rgba(255,255,255,.35);
}
.opt-card.selected .opt-desc { color: rgba(255,255,255,.55); }

/* ── FORM FIELDS ── */
.form-label {
    display: block; margin-bottom: 8px;
    color: rgba(255,255,255,.50);
    font-size: 11px; font-weight: 700; letter-spacing: .8px;
    text-transform: uppercase;
}
.form-hint {
    display: block; margin: -4px 0 10px;
    color: rgba(255,255,255,.28);
    font-size: 12px; line-height: 1.5;
}
.input-icon-wrap { position: relative; width: 100%; }
.input-icon {
    position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
    color: rgba(255,255,255,.25); font-size: 13px;
    pointer-events: none; z-index: 2;
}
.glass-input {
    width: 100%; height: 48px; padding: 0 16px 0 45px;
    border: 1.5px solid rgba(255,255,255,.07);
    border-radius: 12px; outline: none;
    background: rgba(255,255,255,.025);
    color: white; font-size: 13px;
    transition: border-color .2s ease, background .2s ease, box-shadow .2s ease;
}
.glass-input.no-icon { padding-left: 16px; }
.glass-input::placeholder { color: rgba(255,255,255,.22); }
.glass-input:focus {
    border-color: rgba(255,185,0,.50);
    background: rgba(255,185,0,.03);
    box-shadow: 0 0 0 3px rgba(255,185,0,.06);
}
select.glass-input { appearance: none; cursor: pointer; }
select.glass-input option { background: #0b1b31; color: white; }
textarea.glass-input {
    height: auto; min-height: 110px;
    padding: 14px 16px; line-height: 1.6; resize: vertical;
}
.field-block { margin-bottom: 24px; }
.field-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 20px; }

/* ── SECTION LABELS ── */
.section-label {
    font-family: 'Montserrat', sans-serif;
    font-size: 11px; font-weight: 800;
    color: rgba(255,255,255,.30);
    letter-spacing: 1.8px; text-transform: uppercase;
    margin: 28px 0 14px;
}

/* ── BUTTONS ── */
.card-bottom {
    margin-top: auto; padding-top: 35px;
    display: flex; align-items: center; gap: 25px;
}
.btn-continue {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 16px 28px; border: none; border-radius: 14px;
    background: var(--yellow); color: var(--navy);
    font-family: 'Montserrat', sans-serif;
    font-size: 14px; font-weight: 800;
    cursor: pointer;
    transition: transform .25s ease, background .25s ease, box-shadow .25s ease;
}
.btn-continue:hover {
    background: var(--yellow-light);
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(255,185,0,.25);
}
.btn-continue:disabled { opacity: .35; cursor: not-allowed; transform: none; box-shadow: none; }
.btn-back {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 16px 22px; border-radius: 14px;
    border: 1.5px solid rgba(255,255,255,.10);
    background: transparent; color: rgba(255,255,255,.55);
    font-size: 14px; font-weight: 700; cursor: pointer;
    transition: background .25s ease, color .25s ease, border-color .25s ease;
}
.btn-back:hover {
    background: rgba(255,255,255,.04);
    color: white;
    border-color: rgba(255,255,255,.20);
}

/* ── ERRORS ── */
.error-box {
    margin-bottom: 20px; padding: 16px;
    border-radius: 14px;
    background: rgba(248,113,113,.06);
    border: 1px solid rgba(248,113,113,.12);
}
.error-header {
    display: flex; align-items: center; gap: 8px;
    margin-bottom: 8px; color: #f87171;
    font-size: 13px; font-weight: 700;
}
.error-msg { color: rgba(255,255,255,.55); font-size: 12px; margin-top: 4px; }

/* ── HAMBURGER ── */
.qm-hamburger {
    display: none; align-items: center; justify-content: center;
    width: 44px; height: 44px; border: none;
    background: rgba(255,255,255,.05);
    color: white; font-size: 18px; cursor: pointer;
    border-radius: 12px; transition: background .2s;
    position: relative; z-index: 210;
}
.qm-hamburger:hover { background: rgba(255,255,255,.10); }
.qm-hamburger .bars { width: 20px; height: 14px; display: flex; flex-direction: column; justify-content: space-between; position: relative; }
.qm-hamburger .bars span { display: block; width: 100%; height: 2px; background: white; border-radius: 2px; transition: all .3s cubic-bezier(.4,0,.2,1); transform-origin: center; }
.qm-hamburger.active .bars span:nth-child(1) { transform: translateY(6px) rotate(45deg); }
.qm-hamburger.active .bars span:nth-child(2) { opacity: 0; transform: scaleX(0); }
.qm-hamburger.active .bars span:nth-child(3) { transform: translateY(-6px) rotate(-45deg); }
.qm-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.55); z-index: 199; opacity: 0; transition: opacity .3s ease; pointer-events: none; }
.qm-overlay.visible { opacity: 1; pointer-events: auto; }

/* ── RESPONSIVE ── */
@media (max-width: 1024px) {
    .sidebar { width: 240px; padding: 24px 20px; }
    .sidebar-logo img { height: 36px; }
    .step-item { min-height: 36px; padding: 5px 6px; }
    .step-dot { width: 24px; height: 24px; font-size: 8px; }
    .step-label { font-size: 11px; }
    .main-content { margin-left: 240px; padding: 30px 35px; }
    .content-card { padding: 40px 45px; }
}
@media (max-width: 750px) {
    .sidebar {
        position: fixed; top: 0; left: 0; bottom: 0; width: 270px;
        transform: translateX(-100%);
        transition: transform .35s cubic-bezier(.4,0,.2,1);
        z-index: 200;
    }
    .sidebar.open { transform: translateX(0); }
    .sidebar-label { display: none; }
    .step-list { flex-direction: column; gap: 4px; }
    .step-item { padding: 6px 8px; min-height: 40px; background: transparent !important; border: none !important; }
    .step-dot { width: 28px; height: 28px; }
    .progress-section { margin-top: 12px; }
    .main-content { margin-left: 0; min-height: auto; padding: 16px; align-items: flex-start; }
    .content-card { min-height: auto; padding: 24px 18px; border-radius: 20px; }
    .question-title { margin: 24px 0 12px; font-size: 26px; letter-spacing: -1px; line-height: 1.1; }
    .option-grid { grid-template-columns: 1fr !important; gap: 10px; }
    .field-grid { grid-template-columns: 1fr; gap: 0; }
    .opt-card { padding: 18px; }
    .card-bottom { flex-direction: column-reverse; align-items: stretch; gap: 10px; }
    .btn-continue, .btn-back { width: 100%; justify-content: center; min-height: 48px; }
    .qm-hamburger { display: flex; }
    .qm-overlay { display: block; }
}
@media (pointer: coarse) {
    .opt-card { min-height: 48px; }
    .btn-continue, .btn-back { min-height: 48px; }
    .glass-input { height: 48px; }
}
</style>
