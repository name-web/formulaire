<x-layouts.app :title="'Devis provisoire '.$quote->number">

@php
    $money = fn ($amount) => number_format((int) round($amount), 0, ',', ' ').' '.$company['devise'];
    $niveauLabel = config('devis.niveaux.'.($calculation['niveau'] ?? ''), $calculation['niveau'] ?? '');
    $creation = $calculation['creation'] ?? [];
    $recurrentes = $calculation['recurrentes'] ?? [];
    $echeancier = $company['echeancier'];
@endphp

<style>
/* ═══════════════════════════════════════════════════════
   DEVIS RÉCAPITULATIF — UX Pricing
   Un seul prix par ligne, hiérarchie visuelle forte
   ═══════════════════════════════════════════════════════ */

.devis-card {
    width: 100%; max-width: 980px; margin: 0 auto;
    padding: 48px 52px; border-radius: 28px;
    background: rgba(11,27,49,.88);
    border: 1px solid rgba(255,255,255,.06);
    box-shadow: 0 40px 100px rgba(0,0,0,.40);
    backdrop-filter: blur(24px);
}

/* ── HEADER ── */
.devis-head {
    border-bottom: 2px solid rgba(255,185,0,.30);
    padding-bottom: 22px; margin-bottom: 28px;
}
.devis-no {
    font-family: 'Montserrat', sans-serif;
    font-size: 10px; font-weight: 800; letter-spacing: 2.5px;
    color: var(--yellow); text-transform: uppercase;
}
.devis-head h1 {
    margin: 10px 0 8px;
    font-family: 'Montserrat', sans-serif;
    font-size: clamp(24px, 3vw, 34px);
    letter-spacing: -.8px; font-weight: 900;
    color: white; text-transform: uppercase;
}
.devis-meta {
    font-size: 12.5px; color: rgba(255,255,255,.35);
}

/* ── PARTIES ── */
.parties {
    display: grid; grid-template-columns: 1fr 1fr; gap: 24px;
    margin-bottom: 28px;
}
.parties h3 {
    font-family: 'Montserrat', sans-serif;
    font-size: 9px; letter-spacing: 2px; font-weight: 800;
    color: rgba(255,255,255,.30); margin: 0 0 10px;
    text-transform: uppercase;
}
.parties p { margin: 0; font-size: 13px; line-height: 1.7; color: rgba(255,255,255,.60); }

/* ── NOTE ── */
.devis-note {
    background: rgba(255,185,0,.06);
    border-left: 3px solid var(--yellow);
    padding: 16px 18px; border-radius: 8px;
    font-size: 13px; line-height: 1.7;
    color: rgba(255,255,255,.60);
    margin-bottom: 28px;
}
.devis-note strong { color: white; }

/* ── TABLEAU DES PRESTATIONS ── */
.prestations-section { margin-bottom: 32px; }
.prestations-title {
    font-family: 'Montserrat', sans-serif;
    font-size: 11px; font-weight: 800; letter-spacing: 2px;
    color: rgba(255,255,255,.30); text-transform: uppercase;
    margin-bottom: 16px;
}

.prestation-item {
    display: flex; align-items: flex-start; justify-content: space-between;
    padding: 16px 0;
    border-bottom: 1px solid rgba(255,255,255,.05);
}
.prestation-item:last-child { border-bottom: none; }
.prestation-info { flex: 1; min-width: 0; }
.prestation-ref {
    font-family: 'Montserrat', sans-serif;
    font-size: 10px; font-weight: 800; letter-spacing: .8px;
    color: var(--yellow);
}
.prestation-name {
    font-size: 14px; font-weight: 700; color: white;
    margin-top: 4px;
}
.prestation-detail {
    font-size: 12px; color: rgba(255,255,255,.35);
    margin-top: 4px; line-height: 1.5;
}
.prestation-qty {
    font-size: 12px; color: rgba(255,255,255,.35);
    margin-top: 4px;
}
.prestation-price {
    text-align: right; flex-shrink: 0; margin-left: 20px;
}
.prestation-unit {
    font-size: 12px; color: rgba(255,255,255,.35);
}
.prestation-total {
    font-family: 'Montserrat', sans-serif;
    font-size: 16px; font-weight: 800; color: white;
    margin-top: 2px;
}

/* ── CATÉGORIE ── */
.categorie-label {
    font-family: 'Montserrat', sans-serif;
    font-size: 10px; font-weight: 800; letter-spacing: 1.6px;
    color: var(--yellow);
    background: rgba(255,185,0,.06);
    padding: 8px 12px; margin-top: 20px; margin-bottom: 4px;
    border-radius: 6px; text-transform: uppercase;
}
.categorie-label:first-child { margin-top: 0; }

/* ── TOTAUX ── */
.totaux-section {
    margin-top: 28px;
    border-top: 2px solid rgba(255,255,255,.08);
    padding-top: 20px;
}
.totaux-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 8px 0; font-size: 14px;
    color: rgba(255,255,255,.50);
}
.totaux-row .label { font-weight: 500; }
.totaux-row .value { font-weight: 700; color: rgba(255,255,255,.80); }
.totaux-row.remise .value { color: #4ade80; }
.totaux-row.majoration .value { color: var(--yellow); }

.totaux-total {
    display: flex; justify-content: space-between; align-items: center;
    padding: 18px 0 8px; margin-top: 8px;
    border-top: 2px solid rgba(255,185,0,.25);
}
.totaux-total .label {
    font-family: 'Montserrat', sans-serif;
    font-size: 14px; font-weight: 800; color: white;
    text-transform: uppercase; letter-spacing: .5px;
}
.totaux-total .value {
    font-family: 'Montserrat', sans-serif;
    font-size: 24px; font-weight: 900; color: var(--yellow);
}

/* ── ÉCHÉANCIER ── */
.echeance-section {
    margin-top: 28px;
    border: 1.5px solid rgba(255,255,255,.06);
    border-radius: 16px; padding: 22px;
    background: rgba(255,255,255,.015);
}
.echeance-title {
    font-family: 'Montserrat', sans-serif;
    font-size: 10px; font-weight: 800; letter-spacing: 2px;
    color: rgba(255,255,255,.35); text-transform: uppercase;
    margin: 0 0 16px;
}
.echeance-item {
    display: flex; justify-content: space-between; align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid rgba(255,255,255,.04);
}
.echeance-item:last-child { border-bottom: none; }
.echeance-label { font-size: 13px; color: rgba(255,255,255,.50); }
.echeance-value { font-size: 14px; font-weight: 700; color: white; }
.echeance-delay {
    display: flex; justify-content: space-between; align-items: center;
    padding: 14px 0 4px; margin-top: 8px;
    border-top: 1.5px solid rgba(255,255,255,.06);
}
.echeance-delay .label { font-size: 13px; color: rgba(255,255,255,.50); }
.echeance-delay .value {
    font-family: 'Montserrat', sans-serif;
    font-size: 14px; font-weight: 800; color: var(--yellow);
}

/* ── RÉCURRENT ── */
.recurrent-section {
    margin-top: 28px;
    border-left: 3px solid var(--yellow);
    padding: 4px 0 4px 20px;
}
.recurrent-header {
    font-family: 'Montserrat', sans-serif;
    font-size: 14px; font-weight: 800; color: white;
    margin: 0 0 6px;
}
.recurrent-desc {
    font-size: 12px; color: rgba(255,255,255,.35);
    margin: 0 0 16px; line-height: 1.6;
}
.recurrent-total {
    display: flex; justify-content: space-between; align-items: center;
    padding: 12px 0; margin-bottom: 12px;
    border-bottom: 1.5px solid rgba(255,185,0,.15);
}
.recurrent-total .label {
    font-size: 13px; color: rgba(255,255,255,.50);
}
.recurrent-total .value {
    font-family: 'Montserrat', sans-serif;
    font-size: 16px; font-weight: 800; color: var(--yellow);
}

/* ── CONDITIONS ── */
.conds-section {
    margin-top: 28px; font-size: 12px;
    color: rgba(255,255,255,.30); line-height: 1.7;
}
.conds-section ul { padding-left: 18px; margin: 0; }

/* ── STAMP ── */
.stamp-section {
    margin-top: 28px;
    border: 1.5px dashed rgba(255,255,255,.12);
    border-radius: 12px; padding: 22px;
    text-align: center; font-size: 12px;
    color: rgba(255,255,255,.30);
}

/* ── ACTIONS ── */
.devis-actions {
    display: flex; gap: 12px; flex-wrap: wrap;
    margin-top: 28px;
}

/* ── RESPONSIVE ── */
@media (max-width: 750px) {
    .devis-card { padding: 26px 20px; }
    .parties { grid-template-columns: 1fr; }
    .prestation-item { flex-direction: column; gap: 8px; }
    .prestation-price { text-align: left; margin-left: 0; }
    .totaux-total .value { font-size: 20px; }
}
@media print {
    .sidebar, .qm-hamburger, .qm-overlay, .devis-actions, .no-print { display: none !important; }
    .main-content { margin-left: 0; padding: 0; }
    .devis-card { border: 0; box-shadow: none; background: #fff; color: #14202e; }
    .devis-head h1, .prestation-name, .recurrent-header { color: #14202e; }
    .prestation-detail, .prestation-qty, .prestation-unit,
    .totaux-row, .echeance-label, .conds-section { color: #333; }
    .totaux-total .label { color: #14202e; }
    .totaux-total .value, .echeance-delay .value, .recurrent-total .value { color: #14202e; }
    .categorie-label { background: #f5f5f5; color: #14202e; }
}
</style>

<main class="main-content" style="display:block; padding-top:36px;">
    <div class="devis-card">

        {{-- ── HEADER ── --}}
        <div class="devis-head">
            <div class="devis-no">{{ __('DEVIS PROVISOIRE') }} · {{ $quote->number }}</div>
            <h1>{{ $calculation['objet'] ?? $quote->subject }}</h1>
            <div class="devis-meta">
                {{ __('Émis le :date', ['date' => $quote->issued_at->format('d/m/Y')]) }} ·
                {{ __('Valable jusqu\'au :date', ['date' => $quote->due_at?->format('d/m/Y') ?? '—']) }} ·
                {{ __('Niveau :niveau', ['niveau' => mb_strtolower($niveauLabel)]) }}
            </div>
        </div>

        {{-- ── PARTIES ── --}}
        <div class="parties">
            <div>
                <h3>{{ __('PRESTATAIRE') }}</h3>
                <p>
                    <strong>{{ $company['societe'] }}</strong><br>
                    {{ $company['branche'] }}<br>
                    {{ $company['adresse'] }}<br>
                    {{ $company['telephone'] }}<br>
                    {{ $company['email'] }}
                </p>
            </div>
            <div>
                <h3>{{ __('CLIENT') }}</h3>
                <p>
                    <strong>{{ $client?->company_name ?: $client?->name }}</strong><br>
                    {{ $client?->name }}<br>
                    {{ $client?->city }}<br>
                    {{ $client?->phone }}<br>
                    {{ $client?->email }}
                </p>
            </div>
        </div>

        {{-- ── NOTE ── --}}
        <div class="devis-note">
            <strong>{{ __('Ce devis est provisoire.') }}</strong>
            {{ __('Il a été établi automatiquement à partir de vos réponses, sur la base de notre grille tarifaire officielle. Un chargé de clientèle vous appelle sous 24 h ouvrées pour valider le périmètre et vous remettre le devis définitif signé.') }}
        </div>

        {{-- ── PRESTATIONS ── --}}
        <div class="prestations-section">
            <div class="prestations-title">{{ __('PRESTATIONS INCLUSES') }}</div>

            @php $currentCategory = null; @endphp
            @foreach($creation as $ligne)
                @if($ligne['categorie'] !== $currentCategory)
                    @php $currentCategory = $ligne['categorie']; @endphp
                    <div class="categorie-label">{{ $currentCategory }}</div>
                @endif

                <div class="prestation-item">
                    <div class="prestation-info">
                        <div class="prestation-ref">{{ $ligne['ref'] }}</div>
                        <div class="prestation-name">{{ $ligne['nom'] }}</div>
                        <div class="prestation-detail">{{ $ligne['inclus'] }}</div>
                        @if($ligne['quantite'] > 1)
                            <div class="prestation-qty">{{ __('Quantité : :qty', ['qty' => $ligne['quantite']]) }}</div>
                        @endif
                    </div>
                    <div class="prestation-price">
                        @if($ligne['quantite'] > 1)
                            <div class="prestation-unit">{{ $money($ligne['prix_unitaire']) }} × {{ $ligne['quantite'] }}</div>
                        @endif
                        <div class="prestation-total">{{ $money($ligne['quantite'] * $ligne['prix_unitaire']) }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ── TOTAUX ── --}}
        <div class="totaux-section">
            <div class="totaux-row">
                <span class="label">{{ __('Sous-total hors taxes') }}</span>
                <span class="value">{{ $money($calculation['sous_total_ht'] ?? 0) }}</span>
            </div>

            @if(!empty($calculation['majoration']))
                <div class="totaux-row majoration">
                    <span class="label">{{ __('Majoration livraison prioritaire (30 %)') }}</span>
                    <span class="value">+ {{ $money($calculation['majoration']) }}</span>
                </div>
            @endif

            @if(!empty($calculation['remise']))
                <div class="totaux-row remise">
                    <span class="label">{{ __('Remise commerciale (:rate %)', ['rate' => round(($calculation['remise_taux'] ?? 0) * 100)]) }}</span>
                    <span class="value">− {{ $money($calculation['remise']) }}</span>
                </div>
            @endif

            <div class="totaux-row">
                <span class="label">{{ __('Net commercial HT') }}</span>
                <span class="value">{{ $money($calculation['net_ht'] ?? 0) }}</span>
            </div>
            <div class="totaux-row">
                <span class="label">{{ __('TVA 18 %') }}</span>
                <span class="value">{{ $money($calculation['tva'] ?? 0) }}</span>
            </div>

            <div class="totaux-total">
                <span class="label">{{ __('TOTAL À PAYER TTC') }}</span>
                <span class="value">{{ $money($calculation['ttc'] ?? 0) }}</span>
            </div>
        </div>

        {{-- ── ÉCHÉANCIER ── --}}
        <div class="echeance-section">
            <div class="echeance-title">{{ __('ÉCHÉANCIER DE PAIEMENT') }}</div>

            <div class="echeance-item">
                <span class="echeance-label">{{ __('À la signature — :rate %', ['rate' => round($echeancier['acompte'] * 100)]) }}</span>
                <span class="echeance-value">{{ $money($calculation['acompte'] ?? 0) }}</span>
            </div>
            <div class="echeance-item">
                <span class="echeance-label">{{ __('À la validation de la maquette — :rate %', ['rate' => round($echeancier['deuxieme'] * 100)]) }}</span>
                <span class="echeance-value">{{ $money($calculation['deuxieme'] ?? 0) }}</span>
            </div>
            <div class="echeance-item">
                <span class="echeance-label">{{ __('À la livraison — :rate %', ['rate' => round($echeancier['solde'] * 100)]) }}</span>
                <span class="echeance-value">{{ $money($calculation['solde'] ?? 0) }}</span>
            </div>

            <div class="echeance-delay">
                <span class="label">{{ __('Délai de réalisation estimé') }}</span>
                <span class="value">{{ __(':jours JOURS OUVRÉS', ['jours' => $calculation['jours'] ?? 0]) }}</span>
            </div>
        </div>

        {{-- ── FRAIS RÉCURRENTS ── --}}
        @if(!empty($recurrentes))
            <div class="recurrent-section">
                <div class="recurrent-header">
                    {{ __('FRAIS ANNUELS RÉCURRENTS') }}
                </div>
                <div class="recurrent-desc">
                    {{ __('Ces lignes ne font pas partie du prix de création et se renouvellent chaque année :') }}
                </div>

                <div class="recurrent-total">
                    <span class="label">{{ __('Total récurrent TTC par an') }}</span>
                    <span class="value">{{ $money($calculation['recurrent_ttc'] ?? 0) }}</span>
                </div>

                @foreach($recurrentes as $ligne)
                    <div class="prestation-item">
                        <div class="prestation-info">
                            <div class="prestation-ref">{{ $ligne['ref'] }}</div>
                            <div class="prestation-name">
                                {{ $ligne['nom'] }}@if($ligne['quantite'] > 1) × {{ $ligne['quantite'] }}@endif
                            </div>
                        </div>
                        <div class="prestation-price">
                            <div class="prestation-total">{{ $money($ligne['quantite'] * $ligne['prix_unitaire']) }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- ── CONDITIONS ── --}}
        <div class="conds-section">
            <ul>
                <li>{{ __('Devis valable :jours jours à compter de la date d\'émission.', ['jours' => $company['validite_jours']]) }}</li>
                <li>{{ __('Aucun travail ne démarre avant réception de l\'acompte et retour du devis signé avec la mention « bon pour accord ».') }}</li>
                <li>{{ __('Garantie de correction des anomalies : :jours jours après la livraison.', ['jours' => $company['garantie_jours']]) }}</li>
                <li>{{ __('Tout retard de paiement entraîne une pénalité de :rate % par mois.', ['rate' => $company['penalite_mensuelle'] * 100]) }}</li>
                <li>{{ __('Le nom de domaine et tous les accès sont enregistrés au nom du client et lui sont remis à la livraison.') }}</li>
                <li>{{ __('Toute demande hors du périmètre décrit fera l\'objet d\'un avenant chiffré et validé par écrit avant exécution.') }}</li>
            </ul>
        </div>

        {{-- ── STAMP ── --}}
        <div class="stamp-section">
            {{ __('Pour :societe — signature et cachet · Pour le client — « bon pour accord », date, signature et cachet', ['societe' => $company['societe']]) }}
        </div>

        {{-- ── ACTIONS ── --}}
        <div class="devis-actions">
            <button type="button" class="btn-continue" onclick="window.print()">
                <i class="fa-solid fa-file-pdf"></i> {{ __('Enregistrer en PDF ou imprimer') }}
            </button>
            <a href="{{ route('questionnaire.restart') }}" class="btn-back">
                <i class="fa-solid fa-rotate-left"></i> {{ __('Nouveau devis') }}
            </a>
        </div>
    </div>
</main>

</x-layouts.app>
