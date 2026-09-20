<x-layouts.app :title="'Recevez votre devis'">

@include('questionnaire.partials.styles')
@include('questionnaire.partials.sidebar', ['step' => $step, 'steps' => $steps, 'totalSteps' => $totalSteps])

<main class="main-content">
    <div class="content-card anim-scale">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
            <button class="qm-hamburger" id="qmToggle" aria-label="Menu">
                <div class="bars"><span></span><span></span><span></span></div>
            </button>
            <span class="eyebrow">DOMINIUM TECH</span>
        </div>

        @include('questionnaire.partials.errors')

        <div class="top">
            <div class="eyebrow">{{ __('PARLONS DE VOTRE PROJET') }}</div>
            <div class="eyebrow muted">{{ __('Étape 1 sur 5 — Vos coordonnées') }}</div>
        </div>

        <h1 class="question-title">{{ __('Recevez votre devis en 2 minutes') }}</h1>
        <p class="question-desc">
            {{ __('Répondez à quelques questions sur votre projet. Vous recevrez immédiatement un devis détaillé, ligne par ligne, avec le prix de chaque élément. Aucun engagement.') }}
        </p>

        <form action="{{ route('questionnaire.start') }}" method="POST" id="startForm">
            @csrf

            @include('questionnaire.partials.field', [
                'name' => 'nom',
                'label' => __('Vos nom et prénoms'),
                'type' => 'text',
                'value' => $answers['nom'],
                'icon' => 'fa-solid fa-user',
                'placeholder' => 'Jean Dupont',
                'required' => true,
            ])

            <div class="field-grid">
                @include('questionnaire.partials.field', [
                    'name' => 'entreprise',
                    'label' => __('Votre entreprise'),
                    'type' => 'text',
                    'value' => $answers['entreprise'],
                    'icon' => 'fa-solid fa-building',
                ])
                @include('questionnaire.partials.field', [
                    'name' => 'ville',
                    'label' => __('Ville'),
                    'type' => 'text',
                    'value' => $answers['ville'],
                    'icon' => 'fa-solid fa-location-dot',
                    'placeholder' => 'Abidjan',
                ])
            </div>

            <div class="field-grid">
                @include('questionnaire.partials.field', [
                    'name' => 'tel',
                    'label' => __('Téléphone (WhatsApp)'),
                    'type' => 'tel',
                    'value' => $answers['tel'],
                    'icon' => 'fa-solid fa-phone',
                    'placeholder' => '07 00 00 00 00',
                    'required' => true,
                ])
                @include('questionnaire.partials.field', [
                    'name' => 'email',
                    'label' => __('Adresse e-mail'),
                    'type' => 'email',
                    'value' => $answers['email'],
                    'icon' => 'fa-solid fa-envelope',
                    'placeholder' => 'vous@exemple.com',
                    'required' => true,
                ])
            </div>

            <div class="card-bottom">
                <div></div>
                <button type="submit" class="btn-continue">
                    {{ __('Commencer') }} <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>
</main>

@include('questionnaire.partials.scripts')

</x-layouts.app>
