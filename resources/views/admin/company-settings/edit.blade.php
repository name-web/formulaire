<x-layouts.admin :title="__('Paramètres entreprise')">

    <section class="intro">
        <div>
            <div class="intro-label">
                <span></span>
                Configuration
            </div>
            <h1>Paramètres entreprise</h1>
            <p>Identité affichée sur les devis (nom, logo, contacts, footer, couleurs).</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="top-btn"><i class="fa-solid fa-arrow-left" style="margin-right:4px;"></i> Retour</a>
    </section>

    <div class="admin-card">
        <form method="POST" action="{{ route('admin.company-settings.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <div style="padding:22px;display:grid;grid-template-columns:1fr 1fr;gap:16px;" class="form-grid">
                <div style="grid-column:1/-1;">
                    <label class="admin-label">Nom de l'entreprise *</label>
                    <input type="text" name="name" class="admin-input" value="{{ old('name', $company->name) }}" required>
                    @error('name')<div style="color:#dc2626;font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <div style="grid-column:1/-1;">
                    <label class="admin-label">Description (sous le nom)</label>
                    <input type="text" name="description" class="admin-input" value="{{ old('description', $company->description) }}" placeholder="Ex. : Services digitaux & développement d'applications">
                </div>

                {{-- Logo --}}
                <div style="grid-column:1/-1;">
                    <label class="admin-label">Logo de l'entreprise</label>
                    @if($company->logo_path)
                        <div style="margin-bottom:8px;">
                            <img src="{{ asset('storage/' . $company->logo_path) }}" alt="Logo actuel" style="max-height:60px;border-radius:8px;border:1px solid var(--line);padding:4px;">
                            <span style="font-size:11px;color:var(--text-muted);margin-left:8px;">Logo actuel</span>
                        </div>
                    @endif
                    <input type="file" name="logo" accept="image/png,image/jpeg,image/svg+xml" class="admin-input" style="padding:8px;">
                    <span style="font-size:11px;color:var(--text-muted);">PNG, JPG ou SVG — max 2 Mo</span>
                    @error('logo')<div style="color:#dc2626;font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="admin-label">Téléphone</label>
                    <input type="text" name="phone" class="admin-input" value="{{ old('phone', $company->phone) }}">
                </div>
                <div>
                    <label class="admin-label">Email</label>
                    <input type="email" name="email" class="admin-input" value="{{ old('email', $company->email) }}">
                    @error('email')<div style="color:#dc2626;font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="admin-label">Adresse</label>
                    <input type="text" name="address" class="admin-input" value="{{ old('address', $company->address) }}">
                </div>
                <div>
                    <label class="admin-label">Ville</label>
                    <input type="text" name="city" class="admin-input" value="{{ old('city', $company->city) }}">
                </div>
                <div>
                    <label class="admin-label">Pays</label>
                    <input type="text" name="country" class="admin-input" value="{{ old('country', $company->country) }}">
                </div>
                <div>
                    <label class="admin-label">Footer (activité)</label>
                    <input type="text" name="footer" class="admin-input" value="{{ old('footer', $company->footer) }}" placeholder="Ex. : Services digitaux & développement">
                </div>
                <div>
                    <label class="admin-label">Informations légales</label>
                    <input type="text" name="legal_info" class="admin-input" value="{{ old('legal_info', $company->legal_info) }}">
                </div>

                {{-- Couleurs --}}
                <div style="grid-column:1/-1;border-top:1px solid var(--line);padding-top:16px;margin-top:8px;">
                    <h3 style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:12px;">Couleurs du devis</h3>
                </div>
                <div>
                    <label class="admin-label">Couleur principale (bandeau, accents)</label>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <input type="color" name="primary_color_picker" value="{{ old('primary_color', $company->primary_color ?? '#F5A623') }}" style="width:48px;height:36px;border:1px solid #d1d5db;border-radius:6px;cursor:pointer;" oninput="document.getElementsByName('primary_color')[0].value = this.value;">
                        <input type="text" name="primary_color" class="admin-input" value="{{ old('primary_color', $company->primary_color ?? '#F5A623') }}" style="flex:1;" placeholder="#F5A623">
                    </div>
                </div>
                <div>
                    <label class="admin-label">Couleur secondaire (titres, texte)</label>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <input type="color" name="secondary_color_picker" value="{{ old('secondary_color', $company->secondary_color ?? '#1a1a1a') }}" style="width:48px;height:36px;border:1px solid #d1d5db;border-radius:6px;cursor:pointer;" oninput="document.getElementsByName('secondary_color')[0].value = this.value;">
                        <input type="text" name="secondary_color" class="admin-input" value="{{ old('secondary_color', $company->secondary_color ?? '#1a1a1a') }}" style="flex:1;" placeholder="#1a1a1a">
                    </div>
                </div>

                {{-- Règle de commission --}}
                <div style="grid-column:1/-1;border-top:1px solid var(--line);padding-top:16px;margin-top:8px;">
                    <h3 style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:12px;">Règle de commission par défaut</h3>
                </div>
                <div>
                    <label class="admin-label">Seuil minimum (FCFA)</label>
                    <input type="number" name="commission_threshold" class="admin-input" value="{{ old('commission_threshold', $company->commission_threshold ?? 100000) }}" min="0">
                    <span style="font-size:11px;color:var(--text-muted);">Au-dessus de ce montant, le % s'applique</span>
                </div>
                <div>
                    <label class="admin-label">Pourcentage (%)</label>
                    <input type="number" name="commission_percent" class="admin-input" value="{{ old('commission_percent', $company->commission_percent ?? 20) }}" min="0" max="100">
                </div>
                <div>
                    <label class="admin-label">Montant forfaitaire en dessous du seuil (FCFA)</label>
                    <input type="number" name="commission_flat" class="admin-input" value="{{ old('commission_flat', $company->commission_flat ?? 20000) }}" min="0">
                </div>
            </div>
            <div style="padding:0 22px 22px;display:flex;justify-content:flex-end;">
                <button type="submit" class="admin-btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Enregistrer</button>
            </div>
        </form>
    </div>

</x-layouts.admin>
