<x-layouts.admin :title="__('Devis')">

    <section class="intro">
        <div>
            <div class="intro-label">
                <span></span>
                Gestion
            </div>
            <h1>Devis</h1>
            <p>{{ $quotes->total() }} devis</p>
        </div>
        <a href="{{ route('admin.quotes.create') }}" class="top-btn primary"><i class="fa-solid fa-plus" style="margin-right:4px;"></i> Créer un devis</a>
    </section>

    <div class="admin-card">
        <div style="padding:18px 22px;border-bottom:1px solid var(--line);display:flex;gap:10px;flex-wrap:wrap;align-items:center;" class="search-form">
            <form method="GET" action="{{ route('admin.quotes.index') }}" style="display:flex;gap:10px;flex:1;flex-wrap:wrap;">
                <input type="text" name="search" class="admin-input" placeholder="Rechercher (numéro, objet, client)..." value="{{ request('search') }}" style="max-width:320px;">
                <select name="status" class="admin-input" style="max-width:180px;">
                    <option value="">Tous les statuts</option>
                    @php $selectedStatus = request('status'); @endphp
                    @foreach(\App\Models\Quote::getStatuses() as $key => $cfg)
                        <option value="{{ $key }}" @selected($selectedStatus == $key)>{{ $cfg['label'] }}</option>
                    @endforeach
                </select>
                <button type="submit" class="admin-btn btn-secondary"><i class="fa-solid fa-magnifying-glass"></i> Filtrer</button>
            </form>
        </div>

        @if($quotes->isEmpty())
            <div class="empty-state">
                <i class="fa-regular fa-file-lines"></i>
                <p>Aucun devis pour le moment.</p>
            </div>
        @else
            <div style="padding:14px 22px;display:flex;flex-direction:column;gap:10px;">
                @foreach($quotes as $quote)
                    <a href="{{ route('admin.quotes.show', $quote) }}" style="display:flex;align-items:center;gap:14px;padding:14px 18px;border-radius:6px;border:1px solid var(--line);text-decoration:none;transition:all .2s;min-width:0;">
                        <div style="width:40px;height:40px;border-radius:6px;background:var(--yellow);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="fa-solid fa-file-invoice" style="color:var(--black);font-size:14px;"></i>
                        </div>
                        <div style="flex:1;min-width:0;overflow:hidden;">
                            <div style="font-size:15px;font-weight:700;color:var(--black);margin-bottom:3px;">{{ $quote->number }}</div>
                            <div style="font-size:13px;color:var(--text-secondary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $quote->subject }} — {{ $quote->client->name }}</div>
                        </div>
                        <div style="font-size:14px;font-weight:700;color:var(--black);white-space:nowrap;">{{ number_format($quote->total, 0, ',', ' ') }} {{ \App\Models\CompanySetting::get()->currency }}</div>
                        <span class="admin-badge {{ $quote->status_class }}">{{ $quote->status_label }}</span>
                        <span class="date">{{ $quote->issued_at->format('d/m/Y') }}</span>
                    </a>
                @endforeach
            </div>

            <div style="padding:0 22px 18px;">
                {{ $quotes->links() }}
            </div>
        @endif
    </div>

</x-layouts.admin>
