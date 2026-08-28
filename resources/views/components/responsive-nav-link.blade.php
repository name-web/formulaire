@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 text-start text-base font-medium focus:outline-none transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium hover:text-white hover:bg-white/5 hover:border-white/30 focus:outline-none focus:text-white focus:bg-white/5 focus:border-white/30 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes, 'style' => $active ? 'border-color: #ffb703; color: white; background: rgba(255,183,3,0.05);' : 'color: rgba(255,255,255,0.65);']) }}>
    {{ $slot }}
</a>
