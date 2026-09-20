@php
    /*
     * Groupe de choix réutilisable.
     *
     * $name     : nom du champ
     * $options  : tableau de [valeur, libellé, aide?, icône?, couleur?]
     * $selected : valeur ou tableau de valeurs cochées
     * $multiple : cases à cocher si vrai, radios sinon
     * $columns  : nombre de colonnes (1 ou 2)
     */
    $selected = is_array($selected ?? null) ? $selected : ($selected ?? null !== null ? [$selected] : []);
    $selected = array_values(array_filter(array_map('strval', $selected), fn ($value) => $value !== ''));
    $multiple = $multiple ?? false;
    $columns = $columns ?? 1;
@endphp

<div class="option-grid" @if($columns === 1) style="grid-template-columns:1fr;" @endif>
    @foreach($options as $option)
        @php
            $value = (string) $option[0];
            $checked = in_array($value, $selected, true);
        @endphp
        <label class="opt-card {{ $checked ? 'selected' : '' }}">
            <input type="{{ $multiple ? 'checkbox' : 'radio' }}"
                   name="{{ $multiple ? $name.'[]' : $name }}"
                   value="{{ $value }}"
                   @checked($checked)>
            <span class="opt-check"><i class="fa-solid fa-check" style="font-size:10px;"></i></span>
            @if(!empty($option[3]))
                <span class="opt-icon-wrap" style="background:{{ $option[4] ?? '#FFB900' }}20;color:{{ $option[4] ?? '#FFB900' }};">
                    <i class="{{ $option[3] }}"></i>
                </span>
            @endif
            <span class="opt-label">{{ $option[1] }}</span>
            @if(!empty($option[2]))
                <span class="opt-desc">{{ $option[2] }}</span>
            @endif
        </label>
    @endforeach
</div>
