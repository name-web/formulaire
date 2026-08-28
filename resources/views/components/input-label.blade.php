@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm', 'style' => 'color: rgba(255,255,255,0.7);']) }}>
    {{ $value ?? $slot }}
</label>
