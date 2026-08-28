<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 border rounded-md font-semibold text-xs uppercase tracking-widest shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150', 'style' => 'background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.15); color: rgba(255,255,255,0.7);']) }}>
    {{ $slot }}
</button>
