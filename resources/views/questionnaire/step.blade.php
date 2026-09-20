<x-layouts.app :title="$stepTitle">

@include('questionnaire.partials.styles')
@include('questionnaire.partials.sidebar', ['step' => $step, 'steps' => $steps, 'totalSteps' => $totalSteps])

@php
    /* Choix du type de projet, enrichis des icônes et couleurs DOMINIUM. */
    $typeOptions = collect($formTypes)->map(fn ($key) => [
        $key,
        config("project_types.types.{$key}.label", $key),
        config("project_types.types.{$key}.description"),
        config("project_types.types.{$key}.icon"),
        config("project_types.types.{$key}.color"),
    ])->all();

    $backRoute = $step === \App\Services\Devis\DevisFormService::STEP_PROJET
        ? route('questionnaire.index')
        : route('questionnaire.step', $step - 1);
@endphp

<main class="main-content">
    <div class="content-card">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
            <button class="qm-hamburger" id="qmToggle" aria-label="Menu">
                <div class="bars"><span></span><span></span><span></span></div>
            </button>
            <span class="eyebrow">DOMINIUM TECH</span>
        </div>

        @include('questionnaire.partials.errors')

        <div class="top">
            <div class="eyebrow">{{ __('PARLONS DE VOTRE PROJET') }}</div>
            <div class="eyebrow muted">
                {{ __('Étape :step sur :total', ['step' => $step, 'total' => $totalSteps]) }} — {{ $stepTitle }}
            </div>
        </div>

        <form action="{{ route('questionnaire.step.store', $step) }}" method="POST" id="stepForm">
            @csrf

            @if($step === \App\Services\Devis\DevisFormService::STEP_PROJET)

                <h1 class="question-title compact">{{ __('De quoi avez-vous besoin ?') }}</h1>
                <p class="question-desc">{{ __('Choisissez ce qui ressemble le plus à votre projet.') }}</p>

                @include('questionnaire.partials.choices', [
                    'name' => 'type',
                    'options' => $typeOptions,
                    'selected' => old('type', $answers['type']),
                    'columns' => 2,
                ])

            @elseif($step === \App\Services\Devis\DevisFormService::STEP_DETAIL)

                <h1 class="question-title compact">{{ __('Détail du besoin') }}</h1>
                <p class="question-desc">{{ __('Précisez votre besoin : ces réponses déterminent les prestations de votre devis.') }}</p>

                @if($variant === 'site')

                    @include('questionnaire.partials.field', [
                        'name' => 'pages',
                        'label' => __('Combien de pages ?'),
                        'type' => 'number',
                        'value' => old('pages', $answers['pages']),
                        'hint' => __('Accueil, À propos, Services, Réalisations, Contact = 5 pages'),
                        'icon' => 'fa-solid fa-file-lines',
                        'min' => 1,
                        'max' => 100,
                    ])

                    <div class="section-label">{{ __('Fonctions souhaitées') }}</div>
                    @include('questionnaire.partials.choices', [
                        'name' => 'opt',
                        'options' => $choices['options']['site'],
                        'selected' => old('opt', $answers['opt']),
                        'multiple' => true,
                    ])

                    <div class="section-label">{{ __('Les textes et photos') }}</div>
                    @include('questionnaire.partials.choices', [
                        'name' => 'contenus',
                        'options' => $choices['contenus'],
                        'selected' => old('contenus', $answers['contenus']),
                    ])

                @elseif($variant === 'ecommerce')

                    @include('questionnaire.partials.field', [
                        'name' => 'produits',
                        'label' => __('Combien de produits à mettre en ligne ?'),
                        'type' => 'number',
                        'value' => old('produits', $answers['produits']),
                        'icon' => 'fa-solid fa-box',
                        'min' => 1,
                        'max' => 100000,
                    ])

                    @include('questionnaire.partials.field', [
                        'name' => 'pages',
                        'label' => __('Pages de présentation en plus de la boutique'),
                        'type' => 'number',
                        'value' => old('pages', $answers['pages']),
                        'icon' => 'fa-solid fa-file-lines',
                        'min' => 1,
                        'max' => 100,
                    ])

                    <div class="section-label">{{ __('Moyens de paiement') }}</div>
                    @include('questionnaire.partials.choices', [
                        'name' => 'paiement',
                        'options' => $choices['paiement'],
                        'selected' => old('paiement', $answers['paiement']),
                    ])

                    <div class="section-label">{{ __('Fonctions souhaitées') }}</div>
                    @include('questionnaire.partials.choices', [
                        'name' => 'opt',
                        'options' => $choices['options']['ecommerce'],
                        'selected' => old('opt', $answers['opt']),
                        'multiple' => true,
                    ])

                @elseif($variant === 'logiciel')

                    <div class="section-label">{{ __('Quels modules ?') }}</div>
                    <span class="form-hint">{{ __('Chaque module est facturé séparément.') }}</span>
                    @include('questionnaire.partials.choices', [
                        'name' => 'modules',
                        'options' => $choices['modules']['logiciel'],
                        'selected' => old('modules', $answers['modules']),
                        'multiple' => true,
                    ])

                    <div class="field-grid">
                        @include('questionnaire.partials.field', [
                            'name' => 'postes',
                            'label' => __('Sur combien de postes ?'),
                            'type' => 'number',
                            'value' => old('postes', $answers['postes']),
                            'hint' => __('Nombre d’ordinateurs où le logiciel sera installé'),
                            'icon' => 'fa-solid fa-desktop',
                            'min' => 1,
                            'max' => 1000,
                        ])
                        @include('questionnaire.partials.field', [
                            'name' => 'formation',
                            'label' => __('Jours de formation'),
                            'type' => 'number',
                            'value' => old('formation', $answers['formation']),
                            'hint' => __('Groupes de 5 personnes maximum'),
                            'icon' => 'fa-solid fa-chalkboard-user',
                            'min' => 0,
                            'max' => 365,
                        ])
                    </div>

                    <div class="section-label">{{ __('Vos données actuelles') }}</div>
                    @include('questionnaire.partials.choices', [
                        'name' => 'reprise',
                        'options' => $choices['reprise'],
                        'selected' => old('reprise', $answers['reprise']),
                    ])

                    <div class="section-label">{{ __('Accompagnement') }}</div>
                    @include('questionnaire.partials.choices', [
                        'name' => 'opt',
                        'options' => $choices['options']['logiciel'],
                        'selected' => old('opt', $answers['opt']),
                        'multiple' => true,
                    ])

                @elseif($variant === 'scolaire')

                    @include('questionnaire.partials.field', [
                        'name' => 'eleves',
                        'label' => __('Nombre d’élèves environ'),
                        'type' => 'number',
                        'value' => old('eleves', $answers['eleves']),
                        'icon' => 'fa-solid fa-users',
                        'min' => 0,
                        'max' => 200000,
                    ])

                    <div class="section-label">{{ __('Quels modules ?') }}</div>
                    @include('questionnaire.partials.choices', [
                        'name' => 'modules',
                        'options' => $choices['modules']['scolaire'],
                        'selected' => old('modules', $answers['modules']),
                        'multiple' => true,
                    ])

                    <div class="section-label">{{ __('Vos données actuelles') }}</div>
                    @include('questionnaire.partials.choices', [
                        'name' => 'reprise',
                        'options' => $choices['reprise'],
                        'selected' => old('reprise', $answers['reprise']),
                    ])

                @elseif($variant === 'mobile')

                    <div class="section-label">{{ __('Sur quels téléphones ?') }}</div>
                    @include('questionnaire.partials.choices', [
                        'name' => 'plateforme',
                        'options' => $choices['plateforme'],
                        'selected' => old('plateforme', $answers['plateforme']),
                    ])

                    @include('questionnaire.partials.field', [
                        'name' => 'ecrans',
                        'label' => __('Combien d’écrans ?'),
                        'type' => 'number',
                        'value' => old('ecrans', $answers['ecrans']),
                        'hint' => __('Accueil, connexion, liste, détail, profil… comptez large'),
                        'icon' => 'fa-solid fa-mobile-screen',
                        'min' => 1,
                        'max' => 500,
                    ])

                    <div class="section-label">{{ __('Fonctions souhaitées') }}</div>
                    @include('questionnaire.partials.choices', [
                        'name' => 'opt',
                        'options' => $choices['options']['mobile'],
                        'selected' => old('opt', $answers['opt']),
                        'multiple' => true,
                    ])

                @endif

                @if(in_array($variant, ['site', 'ecommerce', 'mobile'], true))
                    <div class="section-label">{{ __('Identité visuelle') }}</div>
                    <span class="form-hint">{{ __('Logo et couleurs de votre marque.') }}</span>
                    @include('questionnaire.partials.choices', [
                        'name' => 'identite',
                        'options' => $choices['identite'],
                        'selected' => old('identite', $answers['identite']),
                    ])
                @endif

            @elseif($step === \App\Services\Devis\DevisFormService::STEP_HERBERGEMENT)

                <h1 class="question-title compact">{{ __('Ce qui fait vivre votre outil') }}</h1>
                <p class="question-desc">
                    {{ __('Ces frais sont annuels et se renouvellent. Ils sont toujours présentés séparément du coût de création.') }}
                </p>

                @if($answers['type'] !== 'application_mobile')
                    <div class="section-label">{{ __('Nom de domaine') }}</div>
                    <span class="form-hint">{{ __('L’adresse de votre site sur internet.') }}</span>
                    @include('questionnaire.partials.choices', [
                        'name' => 'domaine',
                        'options' => $choices['domaine'],
                        'selected' => old('domaine', $answers['domaine']),
                    ])

                    <div class="section-label">{{ __('Hébergement') }}</div>
                    <span class="form-hint">{{ __('L’espace où votre site est installé.') }}</span>
                    @include('questionnaire.partials.choices', [
                        'name' => 'hebergement',
                        'options' => $choices['hebergement'],
                        'selected' => old('hebergement', $answers['hebergement']),
                    ])

                    @include('questionnaire.partials.field', [
                        'name' => 'emails',
                        'label' => __('Adresses e-mail professionnelles'),
                        'type' => 'number',
                        'value' => old('emails', $answers['emails']),
                        'hint' => __('Exemple : contact@votreentreprise.ci'),
                        'icon' => 'fa-solid fa-at',
                        'min' => 0,
                        'max' => 10000,
                    ])
                @endif

                <div class="section-label">{{ __('Suivi et maintenance') }}</div>
                <span class="form-hint">{{ __('Mises à jour, sauvegardes, assistance.') }}</span>
                @include('questionnaire.partials.choices', [
                    'name' => 'maintenance',
                    'options' => $choices['maintenance'],
                    'selected' => old('maintenance', $answers['maintenance']),
                ])

                <div class="section-label">{{ __('Sécurité des données') }}</div>
                @include('questionnaire.partials.choices', [
                    'name' => 'opt',
                    'options' => [['sauvegarde', 'Ajouter la sauvegarde externalisée quotidienne', 'Copie complète chaque jour, restauration testée']],
                    'selected' => old('opt', $answers['opt']),
                    'multiple' => true,
                ])

            @elseif($step === \App\Services\Devis\DevisFormService::STEP_NIVEAU)

                <h1 class="question-title compact">{{ __('Quel niveau de finition ?') }}</h1>
                <p class="question-desc">
                    {{ __('Le même projet peut être réalisé à trois niveaux d’exigence. Le niveau Standard est celui que nous recommandons.') }}
                </p>

                @include('questionnaire.partials.choices', [
                    'name' => 'niveau',
                    'options' => $choices['niveau'],
                    'selected' => old('niveau', $answers['niveau']),
                ])

                <div class="section-label">{{ __('Délai souhaité') }}</div>
                @include('questionnaire.partials.choices', [
                    'name' => 'delai',
                    'options' => $choices['delai'],
                    'selected' => old('delai', $answers['delai']),
                ])

                <div class="field-block">
                    <label class="form-label" for="message">{{ __('Précisions sur votre projet') }}</label>
                    <span class="form-hint">{{ __('Facultatif — tout ce qui nous aidera à affiner.') }}</span>
                    <textarea id="message"
                              name="message"
                              class="glass-input no-icon"
                              rows="3"
                              placeholder="Exemple : je gère une quincaillerie à Yopougon, 4 vendeurs, je perds du stock.">{{ old('message', $answers['message']) }}</textarea>
                </div>

            @endif

            <div class="card-bottom">
                <a href="{{ $backRoute }}" class="btn-back">
                    <i class="fa-solid fa-arrow-left"></i> {{ __('Retour') }}
                </a>
                <div></div>
                <button type="submit" class="btn-continue" style="margin-left:auto;">
                    @if($step === \App\Services\Devis\DevisFormService::STEP_NIVEAU)
                        {{ __('Voir mon devis') }} <i class="fa-solid fa-file-invoice"></i>
                    @else
                        {{ __('Continuer') }} <i class="fa-solid fa-arrow-right"></i>
                    @endif
                </button>
            </div>
        </form>
    </div>
</main>

@include('questionnaire.partials.scripts')

</x-layouts.app>
