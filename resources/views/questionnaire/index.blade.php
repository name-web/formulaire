<x-layouts.app :title="'Démarrer le questionnaire'">

<style>
#wizardForm { display:flex; flex-direction:column; flex:1; }
.content-card { overflow:visible !important; }
.searchable-dropdown { position:relative; width:100%; overflow:visible; }
.dropdown-list { display:none; position:relative; width:100%; max-height:220px; overflow-y:auto; margin-top:8px; background:#0b1b31; border:1px solid rgba(255,255,255,0.12); border-radius:12px; z-index:10; box-shadow:0 12px 35px rgba(0,0,0,0.4); box-sizing:border-box; }
.dropdown-list::-webkit-scrollbar { width:4px; }
.dropdown-list::-webkit-scrollbar-thumb { background:rgba(255,255,255,0.1); border-radius:10px; }
.dropdown-list .dropdown-item { padding:10px 16px; font-size:13px; color:rgba(255,255,255,0.75); cursor:pointer; transition:background 0.15s, color 0.15s; }
.dropdown-list .dropdown-item:hover, .dropdown-list .dropdown-item.active { background:rgba(255,183,3,0.15); color:white; }
.dropdown-list .dropdown-item.no-results { color:var(--text-muted); cursor:default; font-style:italic; }
.dropdown-list .dropdown-item.no-results:hover { background:transparent; }
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
.counter { width:58px; height:58px; display:grid; place-items:center; border-radius:50%; background:rgba(255,183,3,.1); border:1px solid rgba(255,183,3,.18); color:var(--blue-light); font-size:13px; font-weight:800; }
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
.other-field { width:100%; }
.form-label { display:block; margin-bottom:8px; color:rgba(255,255,255,.58); font-size:11px; font-weight:700; }
.input-icon-wrap { position:relative; width:100%; }
.input-icon { position:absolute; left:16px; top:50%; transform:translateY(-50%); color:rgba(255,255,255,.35); font-size:13px; pointer-events:none; z-index:2; }
.glass-input { width:100%; height:48px; padding:0 16px 0 45px; border:1px solid rgba(255,255,255,.09); border-radius:12px; outline:none; background:rgba(255,255,255,.035); color:white; font-size:13px; transition:border-color .2s ease, background .2s ease, box-shadow .2s ease; }
.glass-input::placeholder { color:rgba(255,255,255,.28); }
.glass-input:focus { border-color:rgba(255,183,3,.55); background:rgba(255,183,3,.05); box-shadow:0 0 0 3px rgba(255,183,3,.08); }
.glass-input:disabled { opacity:.55; cursor:not-allowed; }
.card-bottom { margin-top:auto; padding-top:35px; display:flex; align-items:center; gap:25px; }
.btn-continue { display:inline-flex; align-items:center; gap:10px; padding:16px 25px; border:none; border-radius:13px; background:#ffb703; color:white; font-size:14px; font-weight:800; cursor:pointer; transition:transform .25s ease, background .25s ease, box-shadow .25s ease; }
.btn-continue:hover { background:#ffc72c; transform:translateY(-2px); box-shadow:0 12px 30px rgba(255,183,3,.25); }
.btn-continue:disabled { opacity:.4; cursor:not-allowed; transform:none; box-shadow:none; }
.error-box { margin-bottom:20px; padding:15px; border-radius:12px; background:rgba(248,113,113,.08); border:1px solid rgba(248,113,113,.15); }
.error-header { display:flex; align-items:center; gap:8px; margin-bottom:8px; color:#f87171; font-size:13px; font-weight:700; }
.error-msg { color:rgba(255,255,255,.65); font-size:12px; margin-top:4px; }
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

/* ── Hamburger for questionnaire sidebar ── */
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
            <span class="step-label">{{ __('Vos informations') }}</span>
            <i class="fa-solid fa-chevron-right step-arrow"></i>
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
        <div class="progress-step-text">{{ __('Étape') }} <strong>1</strong></div>
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

        <form action="{{ route('questionnaire.start') }}" method="POST" id="wizardForm"
              data-project-types="{{ json_encode(collect($projectTypes)->mapWithKeys(fn($t, $k) => [$k => $t['label']])) }}">
            @csrf

            <div class="top">
                <div class="eyebrow">{{ __('PARLONS DE VOTRE PROJET') }}</div>
            </div>
            <h1 class="question-title anim-slide-in">{{ __('Vos informations') }}</h1>
            <p class="question-desc anim-up d1">{{ __('Commençons par vos coordonnées.') }}</p>

            <div style="display:flex;flex-direction:column;gap:20px;">
                <div>
                    <label class="form-label">{{ __('Nom complet') }} <span style="color:#f87171;">*</span></label>
                    <div class="input-icon-wrap">
                        <i class="fa-solid fa-user input-icon"></i>
                        <input type="text" name="name" value="{{ old('name') }}" class="glass-input" placeholder="Jean Dupont" required>
                    </div>
                </div>
                <div>
                    <label class="form-label">{{ __('Email') }} <span style="color:#f87171;">*</span></label>
                    <div class="input-icon-wrap">
                        <i class="fa-solid fa-envelope input-icon"></i>
                        <input type="email" name="email" value="{{ old('email') }}" class="glass-input" placeholder="jean@entreprise.com" required>
                    </div>
                </div>
                <div class="option-grid" style="grid-template-columns:1fr 1fr;">
                    <div>
                        <label class="form-label">{{ __('Téléphone') }} <span style="color:#f87171;">*</span></label>
                        <div class="input-icon-wrap">
                            <i class="fa-solid fa-phone input-icon"></i>
                            <input type="tel" name="phone" value="{{ old('phone') }}" class="glass-input" placeholder="+212 6..." required>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">{{ __('WhatsApp') }}</label>
                        <div class="input-icon-wrap">
                            <i class="fa-brands fa-whatsapp input-icon"></i>
                            <input type="tel" name="whatsapp" value="{{ old('whatsapp') }}" class="glass-input" placeholder="+212 6...">
                        </div>
                    </div>
                </div>
                <div>
                    <label class="form-label">{{ __('Société') }}</label>
                    <div class="input-icon-wrap">
                        <i class="fa-solid fa-building input-icon"></i>
                        <input type="text" name="company_name" value="{{ old('company_name') }}" class="glass-input" placeholder="Nom de votre société">
                    </div>
                </div>
                <div class="option-grid" style="grid-template-columns:1fr 1fr;">
                    <div>
                        <label class="form-label">{{ __('Pays') }} <span style="color:#f87171;">*</span></label>
                        <div class="searchable-dropdown" id="countryDropdown">
                            <div class="input-icon-wrap">
                                <i class="fa-solid fa-earth-africa input-icon"></i>
                                <input type="text" class="glass-input dropdown-search" placeholder="Chargement des pays…" autocomplete="off" value="{{ old('country') }}" disabled>
                                <input type="hidden" name="country" id="countryNameInput" value="{{ old('country') }}">
                            </div>
                            <div class="dropdown-list" id="countryDropdownList"></div>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">{{ __('Ville') }} <span style="color:#f87171;">*</span></label>
                        <div class="searchable-dropdown" id="cityDropdown">
                            <div class="input-icon-wrap">
                                <i class="fa-solid fa-location-dot input-icon"></i>
                                <input type="text" class="glass-input dropdown-search" placeholder="Sélectionnez d'abord un pays…" autocomplete="off" value="{{ old('city') }}" disabled>
                                <input type="hidden" name="city" id="cityNameInput" value="{{ old('city') }}">
                            </div>
                            <div class="dropdown-list" id="cityDropdownList"></div>
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" name="project_types" id="projectTypesInput" value="{{ json_encode($selectedProjectTypes) }}">

            <div class="card-bottom anim-up d3">
                <div></div>
                <button type="submit" class="btn-continue">{{ __('Commencer le questionnaire') }} <i class="fa-solid fa-arrow-right"></i></button>
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
    const otherField = document.getElementById('otherField');

    document.querySelectorAll('.opt-card[data-group="project_types"]').forEach(card => {
        card.addEventListener('click', () => {
            card.classList.toggle('selected');
            const ch = card.querySelector('.opt-check');
            if(ch) { ch.style.opacity = card.classList.contains('selected') ? '1' : '0'; ch.style.transform = card.classList.contains('selected') ? 'scale(1)' : 'scale(0)'; }
            card.style.transform = 'translateY(-6px) scale(0.97)';
            setTimeout(() => { card.style.transform = 'translateY(-4px) scale(1)'; }, 100);
            const selected = [];
            document.querySelectorAll('.opt-card[data-group="project_types"].selected').forEach(c => selected.push(c.dataset.value));
            typesInput.value = JSON.stringify(selected);
            typesInput.dispatchEvent(new Event('change'));
            if(otherField) {
                const showOther = selected.includes('autre');
                otherField.style.display = showOther ? 'block' : 'none';
                const otherInput = otherField.querySelector('input[name="other_description"]');
                if(otherInput) otherInput.required = showOther;
            }
        });
    });

    let countriesData = [];

    function initSearchableDropdown(dropdownId, items, onSelect, getLabel, hiddenInputId, listId) {
        const dropdown = document.getElementById(dropdownId);
        if(!dropdown) return;
        const searchInput = dropdown.querySelector('.dropdown-search');
        const hiddenInput = document.getElementById(hiddenInputId);
        const list = document.getElementById(listId) || dropdown.querySelector('.dropdown-list');
        if(!searchInput || !hiddenInput || !list) return;
        let currentItems = items;

        function renderList(query) {
            const q = (query || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            const filtered = currentItems.filter(item => {
                const label = String(getLabel(item));
                return label.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').includes(q);
            });
            list.innerHTML = '';
            if(filtered.length === 0) { const div = document.createElement('div'); div.className = 'dropdown-item no-results'; div.textContent = '{{ __("Aucun résultat") }}'; list.appendChild(div); return; }
            filtered.forEach(item => {
                const label = String(getLabel(item));
                const div = document.createElement('div');
                div.className = 'dropdown-item';
                if(label === searchInput.value) div.classList.add('active');
                div.textContent = label;
                div.addEventListener('mousedown', e => { e.preventDefault(); searchInput.value = label; hiddenInput.value = label; hideList(); if(onSelect) onSelect(item); });
                list.appendChild(div);
            });
        }

        function showList() { if(searchInput.disabled) return; renderList(searchInput.value); list.style.position = 'static'; list.style.width = '100%'; list.style.maxWidth = '100%'; list.style.marginTop = '6px'; list.style.display = 'block'; dropdown.classList.add('dropdown-open'); }
        function hideList() { list.style.display = 'none'; dropdown.classList.remove('dropdown-open'); }

        searchInput.addEventListener('focus', showList);
        searchInput.addEventListener('click', showList);
        searchInput.addEventListener('input', () => { if(searchInput.disabled) return; renderList(searchInput.value); list.style.position = 'static'; list.style.width = '100%'; list.style.maxWidth = '100%'; list.style.marginTop = '6px'; list.style.display = 'block'; dropdown.classList.add('dropdown-open'); });
        window.addEventListener('resize', () => { if(list.style.display === 'block') list.style.width = '100%'; });
        document.addEventListener('click', e => { if(!dropdown.contains(e.target)) hideList(); });
        searchInput.addEventListener('keydown', e => {
            if(e.key === 'Escape') { hideList(); searchInput.blur(); return; }
            if(e.key === 'Enter') { e.preventDefault(); const first = list.querySelector('.dropdown-item:not(.no-results)'); if(first) first.dispatchEvent(new MouseEvent('mousedown', { bubbles: true })); }
        });
        dropdown._updateItems = function(newItems, newGetLabel) { currentItems = Array.isArray(newItems) ? newItems : []; if(newGetLabel) getLabel = newGetLabel; if(list.style.display === 'block') renderList(searchInput.value); };
    }

    function resetCityDropdown() {
        const citySearch = document.querySelector('#cityDropdown .dropdown-search');
        const cityNameInput = document.getElementById('cityNameInput');
        const cityList = document.getElementById('cityDropdownList');
        if(!citySearch) return;
        citySearch.value = ''; citySearch.disabled = true; citySearch.placeholder = "{{ __("Sélectionnez d'abord un pays…") }}";
        if(cityNameInput) cityNameInput.value = '';
        if(cityList) { cityList.innerHTML = ''; cityList.style.display = 'none'; }
        const cd = document.getElementById('cityDropdown');
        if(cd) cd.classList.remove('dropdown-open');
    }

    function loadCitiesForCountry(country) {
        const citySearch = document.querySelector('#cityDropdown .dropdown-search');
        const cityList = document.getElementById('cityDropdownList');
        const cityNameInput = document.getElementById('cityNameInput');
        const cityDropdown = document.getElementById('cityDropdown');
        if(!citySearch || !cityList || !cityNameInput) return;
        citySearch.disabled = true; citySearch.placeholder = '{{ __("Chargement…") }}';
        cityNameInput.value = ''; citySearch.value = ''; cityList.innerHTML = ''; cityList.style.display = 'none';
        if(cityDropdown) cityDropdown.classList.remove('dropdown-open');
        fetch('/api/countries/' + encodeURIComponent(country.code) + '/cities')
            .then(r => { if(!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(data => {
                const cities = Array.isArray(data.cities) ? data.cities : [];
                citySearch.disabled = false;
                citySearch.placeholder = cities.length ? '{{ __("Rechercher une ville…") }}' : '{{ __("Aucune ville disponible") }}';
                if(cityDropdown && cityDropdown._updateItems) cityDropdown._updateItems(cities, item => item);
            })
            .catch(() => { citySearch.disabled = false; citySearch.placeholder = '{{ __("Erreur de chargement — réessayez") }}'; });
    }

    fetch('/api/countries').then(r => { if(!r.ok) throw new Error('HTTP ' + r.status); return r.json(); }).then(data => {
        countriesData = Array.isArray(data) ? data : [];
        const cs = document.querySelector('#countryDropdown .dropdown-search');
        if(cs) { cs.disabled = false; cs.placeholder = '{{ __("Rechercher un pays…") }}'; }
        initSearchableDropdown('countryDropdown', countriesData, country => {
            document.getElementById('countryNameInput').value = country.name;
            const cd = document.getElementById('countryDropdown'); const cl = document.getElementById('countryDropdownList');
            if(cl) cl.style.display = 'none'; if(cd) cd.classList.remove('dropdown-open');
            resetCityDropdown(); loadCitiesForCountry(country);
        }, item => item.name, 'countryNameInput', 'countryDropdownList');
        initSearchableDropdown('cityDropdown', [], city => { document.getElementById('cityNameInput').value = city; }, item => item, 'cityNameInput', 'cityDropdownList');
        const oldCountry = document.getElementById('countryNameInput').value;
        if(oldCountry) { const c = countriesData.find(c => c.name === oldCountry); if(c) loadCitiesForCountry(c); }
    }).catch(() => {
        const cs = document.querySelector('#countryDropdown .dropdown-search');
        if(cs) { cs.disabled = false; cs.placeholder = '{{ __("Erreur de chargement — réessayez") }}'; }
    });
});
</script>
</x-layouts.app>
