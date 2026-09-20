{{-- Template du document devis — fidèle au PDF DOMINIUM TECH --}}
@php
    $currency = $company->currency ?? 'FCFA';
    $primaryColor = $company->primary_color ?? '#F5A623';
    $secondaryColor = $company->secondary_color ?? '#1a1a1a';
    $fmt = fn ($amount) => number_format((float) $amount, 0, ',', ' ');
    $logoPath = filled($company->logo_path ?? null) ? public_path('storage/' . $company->logo_path) : null;
    if (!$logoPath || !is_file($logoPath)) {
        $logoPath = filled($company->logo_path ?? null) ? public_path($company->logo_path) : null;
    }
    $hasLogo = $logoPath !== null && is_file($logoPath);
    $initial = mb_strtoupper(mb_substr($company->name ?? 'D', 0, 1));
@endphp

<div class="page">
    <div class="bar"></div>
    <div class="content">

        {{-- ── En-tête ── --}}
        <div class="header">
            <div class="brand">
                @if($hasLogo)
                    <img class="logo-img" src="{{ $logoPath }}" alt="Logo {{ $company->name }}">
                @else
                    <div class="logo" style="display:inline-block;vertical-align:middle;margin-right:12px;">
                        <div class="logo-circle" style="border-color:{{ $primaryColor }};color:{{ $primaryColor }};">{{ $initial }}</div>
                    </div>
                @endif
                <div class="brand-text" style="display:inline-block;vertical-align:middle;">
                    <div class="brand-sub">{{ $company->name }}<span class="accent" style="color:{{ $primaryColor }};"></span></div>
                    <div class="brand-meta">
                        {{ $company->description }}<br>
                        @if($company->city || $company->country)
                            {{ collect([$company->city, $company->country])->filter()->implode(', ') }}<br>
                        @endif
                        @if($company->email){{ $company->email }}@endif
                        @if($company->email && $company->phone) &bull; @endif
                        @if($company->phone){{ $company->phone }}@endif
                    </div>
                </div>
            </div>
            <div class="doc-info">
                <div class="doc-title">DEVIS</div>
                <div class="doc-sub">(valant facture après règlement)</div>
                <div class="doc-meta"><strong>N° {{ $quote->number }}</strong></div>
                <div class="doc-meta">Émis le {{ $quote->issued_at->format('d/m/Y') }}</div>
            </div>
        </div>
        <hr class="rule" style="border-top-color:{{ $primaryColor }};">

        {{-- ── Client / Prestataire ── --}}
        <div class="parties">
            <div class="col">
                <div class="label-sm">CLIENT</div>
                <div class="party-name">{{ $quote->client->name }}</div>
                @if($quote->client->phone)
                    <div class="party-line">Tél. : {{ $quote->client->phone }}</div>
                @endif
                @if($quote->client->company_name)
                    <div class="party-line">Activité : {{ $quote->client->company_name }}</div>
                @endif
            </div>
            <div class="col">
                <div class="label-sm">PRESTATAIRE</div>
                <div class="party-name">{{ $company->name }}</div>
                @if($company->description)
                    <div class="party-line">{{ $company->description }}</div>
                @endif
            </div>
        </div>

        {{-- ── Objet ── --}}
        <h2 class="section" style="color:{{ $secondaryColor }};">Objet de la prestation</h2>
        <p class="body-text">{{ $quote->description ?: $quote->subject }}</p>

        {{-- ── Blocs dynamiques ── --}}
        @foreach($quote->sections as $section)
            @if($section->type === \App\Models\QuoteSection::TYPE_BUDGET)
                <h2 class="section" style="color:{{ $secondaryColor }};">{{ $section->title ?: 'Répartition du budget' }}</h2>
                @include('admin.quotes.partials.budget-table')

            @elseif($section->type === \App\Models\QuoteSection::TYPE_TEXT)
                <h2 class="section" style="color:{{ $secondaryColor }};">{{ $section->title ?: 'Texte' }}</h2>
                <p class="body-text">{{ $section->content['text'] ?? '' }}</p>

            @elseif($section->type === \App\Models\QuoteSection::TYPE_LIST)
                <h2 class="section" style="color:{{ $secondaryColor }};">{{ $section->title ?: 'Liste' }}</h2>
                <ul class="prestations">
                    @foreach($section->content['items'] ?? [] as $listItem)
                        <li>{{ $listItem }}</li>
                    @endforeach
                </ul>

            @elseif($section->type === \App\Models\QuoteSection::TYPE_TABLE || $section->type === \App\Models\QuoteSection::TYPE_PRODUCT_TABLE)
                @php
                    $isProductTable = $section->type === \App\Models\QuoteSection::TYPE_PRODUCT_TABLE;
                    $headers = $section->content['headers'] ?? [];
                    $rows = $section->content['rows'] ?? [];
                @endphp
                <h2 class="section" style="color:{{ $secondaryColor }};">{{ $section->title ?: ($isProductTable ? 'Analyse des produits & cible recommandée' : 'Tableau') }}</h2>
                <table class="{{ $isProductTable ? 'products' : '' }}" @if(!$isProductTable) style="border-collapse:collapse;width:100%;font-size:12px;margin-top:6px;" @endif>
                    <thead>
                        <tr>
                            @foreach($headers as $header)
                                <th @if(!$isProductTable) style="background:{{ $secondaryColor }};color:#fff;text-align:left;padding:10px 12px;font-weight:700;" @endif>{{ $header }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            <tr>
                                @foreach($row as $cellIndex => $cell)
                                    <td @if(!$isProductTable) style="padding:10px 12px;border-bottom:1px solid #DDD;vertical-align:top;" @endif
                                        @if($isProductTable && $cellIndex === 0) class="product-name" @endif
                                    >{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            @elseif($section->type === \App\Models\QuoteSection::TYPE_CUSTOM_FIELDS)
                <h2 class="section" style="color:{{ $secondaryColor }};">{{ $section->title ?: 'Informations personnalisées' }}</h2>
                <table class="table-fields" style="border-collapse:collapse;width:100%;font-size:12px;margin-top:6px;">
                    <tbody>
                        @foreach($quote->customFields as $field)
                            <tr>
                                <td style="width:40%;padding:8px 12px;border:1px solid #DDD;"><strong>{{ $field->name }}</strong></td>
                                <td style="padding:8px 12px;border:1px solid #DDD;">{{ $field->value }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @endforeach

        {{-- Compatibilité : devis sans bloc budgétaire --}}
        @if(! $quote->sections->contains(fn ($s) => $s->type === \App\Models\QuoteSection::TYPE_BUDGET) && $quote->items->isNotEmpty())
            <h2 class="section" style="color:{{ $secondaryColor }};">Répartition du budget</h2>
            @include('admin.quotes.partials.budget-table')
        @endif

        {{-- ── Devis provisoire issu du nouveau formulaire ── --}}
        @if($quote->isFromForm())
            @php $calc = $quote->calculation; @endphp
            <h2 class="section" style="color:{{ $secondaryColor }};">Détail du devis provisoire</h2>
            <table style="border-collapse:collapse;width:100%;font-size:12px;margin-top:6px;">
                <tbody>
                    <tr>
                        <td style="width:40%;padding:8px 12px;border:1px solid #DDD;"><strong>Niveau de finition</strong></td>
                        <td style="padding:8px 12px;border:1px solid #DDD;">{{ config('devis.niveaux.'.($calc['niveau'] ?? ''), $calc['niveau'] ?? '—') }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 12px;border:1px solid #DDD;"><strong>Délai de réalisation estimé</strong></td>
                        <td style="padding:8px 12px;border:1px solid #DDD;">{{ $calc['jours'] ?? 0 }} jours ouvrés@if(! empty($calc['accelere'])) — livraison prioritaire@endif</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 12px;border:1px solid #DDD;"><strong>Net commercial HT</strong></td>
                        <td style="padding:8px 12px;border:1px solid #DDD;">{{ $fmt($calc['net_ht'] ?? 0) }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 12px;border:1px solid #DDD;"><strong>TVA 18 %</strong></td>
                        <td style="padding:8px 12px;border:1px solid #DDD;">{{ $fmt($calc['tva'] ?? 0) }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 12px;border:1px solid #DDD;"><strong>Total TTC</strong></td>
                        <td style="padding:8px 12px;border:1px solid #DDD;"><strong>{{ $fmt($calc['ttc'] ?? 0) }}</strong></td>
                    </tr>
                    <tr>
                        <td style="padding:8px 12px;border:1px solid #DDD;"><strong>Échéancier de paiement</strong></td>
                        <td style="padding:8px 12px;border:1px solid #DDD;">
                            Signature 40 % : {{ $fmt($calc['acompte'] ?? 0) }} • Maquette 30 % : {{ $fmt($calc['deuxieme'] ?? 0) }} • Livraison 30 % : {{ $fmt($calc['solde'] ?? 0) }}
                        </td>
                    </tr>
                    @if(! empty($calc['recurrentes']))
                        <tr>
                            <td style="padding:8px 12px;border:1px solid #DDD;"><strong>Frais annuels récurrents</strong></td>
                            <td style="padding:8px 12px;border:1px solid #DDD;">{{ $fmt($calc['recurrent_ttc'] ?? 0) }} TTC par an</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            @if(! empty($calc['recurrentes']))
                <ul class="prestations">
                    @foreach($calc['recurrentes'] as $recurrentLine)
                        <li>
                            {{ $recurrentLine['ref'] }} — {{ $recurrentLine['nom'] }}@if($recurrentLine['quantite'] > 1) × {{ $recurrentLine['quantite'] }}@endif :
                            {{ $fmt($recurrentLine['quantite'] * $recurrentLine['prix_unitaire']) }}
                        </li>
                    @endforeach
                </ul>
            @endif

            @if(! empty($calc['message']))
                <h2 class="section" style="color:{{ $secondaryColor }};">Message du client</h2>
                <p class="body-text">{{ $calc['message'] }}</p>
            @endif
        @endif

        {{-- ── Modalités de paiement ── --}}
        @if($quote->payment_terms)
            <h2 class="section" style="color:{{ $secondaryColor }};">Modalités de paiement</h2>
            <p class="body-text">{{ $quote->payment_terms }}</p>
        @endif

        {{-- ── Badge statut ── --}}
        @if($quote->isPaid())
            <div class="stamp">✔ DEVIS RÉGLÉ — VALANT FACTURE</div>
        @endif

        {{-- ── Signatures ── --}}
        <div class="signatures">
            <div class="sig">
                <div class="sig-line">Le Client<br><strong>{{ $quote->client->name }}</strong></div>
            </div>
            <div class="sig">
                <div class="sig-line">Pour {{ $company->name }}<br><strong>{{ $company->name }}</strong></div>
            </div>
        </div>

    </div>

    {{-- Footer (aperçu navigateur uniquement) --}}
    @if(empty($inPdf))
        <div class="footer-content">
            {{ $company->name }} — {{ $company->footer ?? $company->description }}@if($company->city || $company->country)
                — {{ collect([$company->city, $company->country])->filter()->implode(', ') }}
            @endif
            <br>
            Devis N° {{ $quote->number }} • Page 1
        </div>
    @endif
</div>
