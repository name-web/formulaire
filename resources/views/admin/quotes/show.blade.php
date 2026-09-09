<x-layouts.admin :title="'Devis '.$quote->number">

    <section class="intro">
        <div>
            <div class="intro-label">
                <span></span>
                Devis
            </div>
            <h1>{{ $quote->number }}</h1>
            <p>{{ $quote->subject }} — {{ $quote->client->name }}</p>
        </div>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <a href="{{ route('admin.quotes.preview', $quote) }}" class="top-btn primary" target="_blank"><i class="fa-solid fa-eye" style="margin-right:4px;"></i> Aperçu</a>
            <a href="{{ route('admin.quotes.pdf', $quote) }}" class="top-btn primary"><i class="fa-solid fa-file-pdf" style="margin-right:4px;"></i> PDF</a>
            <a href="{{ route('admin.quotes.edit', $quote) }}" class="top-btn"><i class="fa-solid fa-pen" style="margin-right:4px;"></i> Modifier</a>
            <form method="POST" action="{{ route('admin.quotes.destroy', $quote) }}" onsubmit="return confirm('Supprimer ce devis ?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="top-btn"><i class="fa-solid fa-trash-can" style="margin-right:4px;"></i> Supprimer</button>
            </form>
            <a href="{{ route('admin.quotes.index') }}" class="top-btn"><i class="fa-solid fa-arrow-left" style="margin-right:4px;"></i> Retour</a>
        </div>
    </section>

    <section class="content" style="grid-template-columns:minmax(0,1fr) 300px;align-items:start;">

        <div style="display:flex;flex-direction:column;gap:18px;">
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title"><i class="fa-solid fa-file-invoice" style="color:var(--yellow);margin-right:6px;"></i> Informations</div>
                    </div>
                </div>
                <div style="padding:22px;display:grid;grid-template-columns:1fr 1fr;gap:16px;" class="form-grid">
                    <div>
                        <div class="admin-label">Client</div>
                        <a href="{{ $quote->client->isClient() ? route('admin.client-clients.show', $quote->client) : route('admin.clients.show', $quote->client) }}" style="font-weight:700;color:var(--black);">{{ $quote->client->name }}</a>
                    </div>
                    <div>
                        <div class="admin-label">Statut</div>
                        <span class="admin-badge {{ $quote->status_class }}">{{ $quote->status_label }}</span>
                    </div>
                    <div>
                        <div class="admin-label">Émis le</div>
                        <div>{{ $quote->issued_at->format('d/m/Y') }}</div>
                    </div>
                    <div>
                        <div class="admin-label">Échéance</div>
                        <div>{{ $quote->due_at?->format('d/m/Y') ?? '—' }}</div>
                    </div>
                    <div style="grid-column:1/-1;">
                        <div class="admin-label">Objet</div>
                        <div>{{ $quote->subject }}</div>
                    </div>
                    @if($quote->description)
                        <div style="grid-column:1/-1;">
                            <div class="admin-label">Description</div>
                            <div style="white-space:pre-wrap;">{{ $quote->description }}</div>
                        </div>
                    @endif
                    @if($quote->payment_terms)
                        <div style="grid-column:1/-1;">
                            <div class="admin-label">Modalités de paiement</div>
                            <div style="white-space:pre-wrap;">{{ $quote->payment_terms }}</div>
                        </div>
                    @endif
                    @if($quote->notes)
                        <div style="grid-column:1/-1;">
                            <div class="admin-label">Notes</div>
                            <div style="white-space:pre-wrap;font-style:italic;color:var(--muted);">{{ $quote->notes }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title"><i class="fa-solid fa-list-check" style="color:var(--yellow);margin-right:6px;"></i> Lignes</div>
                    </div>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Désignation</th>
                            <th>Détail</th>
                            <th style="text-align:right;">Qté</th>
                            <th style="text-align:right;">Prix unit.</th>
                            <th style="text-align:right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($quote->items as $item)
                            <tr>
                                <td style="font-weight:700;">{{ $item->designation }}</td>
                                <td>{{ $item->description }}</td>
                                <td style="text-align:right;">{{ $item->quantity }}</td>
                                <td style="text-align:right;">{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                                <td style="text-align:right;">{{ number_format($item->total, 0, ',', ' ') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @foreach($quote->sections as $section)
                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title"><i class="fa-solid fa-layer-group" style="color:var(--yellow);margin-right:6px;"></i> {{ $section->title }}</div>
                            <div class="card-subtitle">{{ \App\Models\QuoteSection::getTypes()[$section->type] ?? $section->type }}</div>
                        </div>
                    </div>
                    <div style="padding:18px 22px;font-size:14px;">
                        @if($section->type === \App\Models\QuoteSection::TYPE_TEXT)
                            <div style="white-space:pre-wrap;">{{ $section->content['text'] ?? '' }}</div>
                        @elseif($section->type === \App\Models\QuoteSection::TYPE_LIST)
                            <ul style="padding-left:20px;display:flex;flex-direction:column;gap:6px;">
                                @foreach($section->content['items'] ?? [] as $listItem)
                                    <li>{{ $listItem }}</li>
                                @endforeach
                            </ul>
                        @elseif(in_array($section->type, [\App\Models\QuoteSection::TYPE_TABLE, \App\Models\QuoteSection::TYPE_PRODUCT_TABLE]))
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        @foreach($section->content['headers'] ?? [] as $header)
                                            <th>{{ $header }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($section->content['rows'] ?? [] as $row)
                                        <tr>
                                            @foreach($row as $cell)
                                                <td>{{ $cell }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @elseif($section->type === \App\Models\QuoteSection::TYPE_CUSTOM_FIELDS)
                            <div style="display:flex;flex-direction:column;gap:8px;">
                                @foreach($quote->customFields as $field)
                                    <div style="display:flex;gap:12px;">
                                        <div style="font-weight:700;min-width:200px;">{{ $field->name }}</div>
                                        <div>{{ $field->value }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div style="display:flex;flex-direction:column;gap:18px;">
            <div class="card">
                <div class="side-section">
                    <div class="side-heading">Totaux</div>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <div style="display:flex;justify-content:space-between;">
                            <span style="color:var(--muted);">Sous-total</span>
                            <strong>{{ number_format($quote->subtotal, 0, ',', ' ') }}</strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;">
                            <span style="color:var(--muted);">Remise</span>
                            <strong>-{{ number_format($quote->discount, 0, ',', ' ') }}</strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;border-top:2px solid var(--black);padding-top:10px;font-size:18px;">
                            <span style="font-weight:800;">TOTAL</span>
                            <strong>{{ number_format($quote->total, 0, ',', ' ') }} {{ \App\Models\CompanySetting::get()->currency }}</strong>
                        </div>
                    </div>
                </div>
                <div class="side-section">
                    <div class="side-heading">Changer le statut</div>
                    <form method="POST" action="{{ route('admin.quotes.update-status', $quote) }}">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="admin-input" onchange="this.form.submit()">
                            @php $currentStatus = $quote->status; @endphp
                            @foreach(\App\Models\Quote::getStatuses() as $key => $cfg)
                                <option value="{{ $key }}" @selected($currentStatus === $key)>{{ $cfg['label'] }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        </div>

    </section>

</x-layouts.admin>
