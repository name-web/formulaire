<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight" style="color: white;">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);">
                <div class="p-6" style="color: rgba(255,255,255,0.85);">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
