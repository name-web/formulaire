---
paths:
  - 'resources/views/questionnaire/**'
  - 'app/Services/Devis/**'
  - 'resources/views/admin/projects/**'
---

# Formulaire de devis

## Le parcours public est le formulaire de devis, pas l'ancien questionnaire
`/questionnaire` est un parcours fixe de 5 étapes (coordonnées, type, détail, hébergement, niveau) défini par `App\Services\Devis\DevisFormService` + `config/devis_form.php`. Le type de détail de l'étape 3 vient de `config/devis_form.php` (`variantes`), jamais d'une condition stockée en base. L'ancien système `Question` / `QuestionOption` / `QuestionCondition` / `Answer` n'alimente plus aucune nouvelle soumission : il ne sert qu'à relire les prospects historiques (`projects.form_version = 'legacy'`). Ne pas rajouter de champ au nouveau parcours sans l'ajouter à `config/devis_form.php` **et** à `rulesForStep()`.

## Deux générations de projets cohabitent
`Project::form_version` vaut `legacy` (réponses dans `answers`, aucune donnée du nouveau formulaire) ou `Project::FORM_VERSION_CURRENT` (réponses dans `form_data` + `Quote` brouillon issue de `DevisBuilder`). L'affichage admin choisit la génération via `Project::isLegacyForm()` et `Quote::hasStoredCalculation()` ; ne jamais backfiller `form_data` pour un projet historique, ni recalculer ses devis.

## Piège Blade : le paramètre `$step` de `partials/field`
Les `@include` partagent la portée du parent, où `$step` est déjà le numéro d'étape courant. Un `isset($step)` dans le partial rendait `step="3"` sur les champs `number` de l'étape 3 (valeurs valides 1, 4, 7…), ce qui bloquait silencieusement l'envoi du formulaire. Le pas HTML s'appelle donc `$inputStep` et doit rester explicite.

## Ne plus re-encoder old() JSON dans les champs cachés
`old('project_types')` renvoie la string JSON brute du dernier POST ; la passer à `json_encode()` double-encode la valeur et casse la validation (`project_types.*.in`). Normaliser côté contrôleur (`json_decode` si string) et passer un tableau à la vue.
