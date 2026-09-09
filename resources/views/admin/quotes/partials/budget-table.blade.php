{{-- Tableau budgétaire (lignes financières + sous-total, remise, TOTAL) --}}
<table>
    <thead>
        <tr>
            <th style="width: 30%;">Désignation</th>
            <th style="width: 48%;">Détail</th>
            <th style="width: 22%; text-align: right;">Montant ({{ $currency }})</th>
        </tr>
    </thead>
    <tbody>
        @forelse($quote->items as $item)
            <tr>
                <td><strong>{{ $item->designation }}</strong></td>
                <td>{{ $item->description }}</td>
                <td style="text-align: right;">{{ $fmt($item->total) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3">Aucune prestation.</td>
            </tr>
        @endforelse
        @if($quote->items->isNotEmpty())
            @if((int) $quote->discount > 0)
                <tr>
                    <td>Sous-total</td>
                    <td></td>
                    <td style="text-align: right;">{{ $fmt($quote->subtotal) }}</td>
                </tr>
                <tr>
                    <td>Remise</td>
                    <td></td>
                    <td style="text-align: right;">-{{ $fmt($quote->discount) }}</td>
                </tr>
            @endif
            <tr class="bg-total">
                <td>TOTAL</td>
                <td></td>
                <td style="text-align: right;">{{ $fmt($quote->total) }}</td>
            </tr>
        @endif
    </tbody>
</table>
@if($quote->notes)
    <p class="note-text">{{ $quote->notes }}</p>
@endif
