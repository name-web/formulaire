@php
    /*
     * Champ de saisie réutilisable.
     *
     * $name        : nom du champ
     * $label       : libellé affiché
     * $type        : type HTML (text, tel, email, number)
     * $value       : valeur par défaut, remplacée par old() après erreur
     * $hint        : aide sous le libellé
     * $icon        : classe Font Awesome de l'icône
     * $placeholder : placeholder
     * $required    : champ obligatoire
     * $min / $max  : bornes pour les champs number
     * $inputStep   : pas du champ number (nommé ainsi pour ne pas entrer en
     *                collision avec l'étape courante du parcours, $step)
     */
    $value = old($name, $value ?? '');
    $type = $type ?? 'text';
    $inputStep = $inputStep ?? null;
@endphp

<div class="field-block">
    <label class="form-label" for="{{ $name }}">
        {{ $label }}@if($required ?? false) <span style="color:#f87171;">*</span>@endif
    </label>
    @if(!empty($hint))
        <span class="form-hint">{{ $hint }}</span>
    @endif
    <div class="input-icon-wrap">
        @if(!empty($icon))
            <i class="{{ $icon }} input-icon"></i>
        @endif
        <input type="{{ $type }}"
               id="{{ $name }}"
               name="{{ $name }}"
               value="{{ $value }}"
               class="glass-input {{ empty($icon) ? 'no-icon' : '' }}"
               placeholder="{{ $placeholder ?? '' }}"
               @if(isset($min)) min="{{ $min }}" @endif
               @if(isset($max)) max="{{ $max }}" @endif
               @if($inputStep !== null) step="{{ $inputStep }}" @endif
               @if($required ?? false) required @endif>
    </div>
</div>
