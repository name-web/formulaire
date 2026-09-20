@php
    /* Les cinq étapes du parcours ; le devis provisoire complète la progression. */
    $progressPercent = (int) round((($step - 1) / $totalSteps) * 100);
@endphp

<aside class="sidebar" id="qmSidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <img src="{{ asset('IMAGES/Logo dominium.png') }}" alt="DOMINIUM">
            <span>DOMINIUM</span>
        </div>
    </div>

    <div class="sidebar-label">{{ __('VOTRE PROGRESSION') }}</div>

    <nav class="step-list">
        @foreach($steps as $number => $label)
            @php
                $isActive = $number === $step;
                $isDone = $number < $step;
            @endphp
            <div class="step-item {{ $isActive ? 'active' : '' }}" data-step="{{ $number }}">
                <div class="step-dot {{ $isActive ? 'active' : '' }}">
                    @if($isDone)
                        <i class="fa-solid fa-check"></i>
                    @else
                        {{ str_pad((string) $number, 2, '0', STR_PAD_LEFT) }}
                    @endif
                </div>
                <span class="step-label">{{ $label }}</span>
                @if($isActive)
                    <i class="fa-solid fa-chevron-right step-arrow"></i>
                @endif
            </div>
        @endforeach
    </nav>

    <div class="progress-section">
        <div class="progress-header">
            <span class="progress-label">{{ __('Progression') }}</span>
            <span class="progress-value">{{ $progressPercent }}%</span>
        </div>
        <div class="progress-bar-track">
            <div class="progress-bar-fill" style="width:{{ $progressPercent }}%"></div>
        </div>
        <div class="progress-step-text">
            {{ __('Étape') }} <strong>{{ $step }}</strong> {{ __('sur') }} <strong>{{ $totalSteps }}</strong>
        </div>
    </div>
</aside>

<div class="qm-overlay" id="qmOverlay"></div>
