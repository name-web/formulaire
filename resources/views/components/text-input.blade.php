@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'focus:border-yellow-500 focus:ring-yellow-500 rounded-md shadow-sm', 'style' => 'background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.15); color: white;']) }}>
