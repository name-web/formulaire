<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'type',
        'project_types',
        'is_required',
        'sort_order',
        'group_label',
        'placeholder',
        'help_text',
        'is_active',
    ];

    protected $casts = [
        'project_types' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Question $question): void {
            $question->options()->delete();
            $question->conditions()->delete();
        });
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class);
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(QuestionCondition::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * Known wizard-to-label mappings for values that don't exist in DB options.
     * These cover legacy or fallback values stored in answers.
     */
    private static array $wizardLabels = [
        // Legacy step labels (backward compatibility with existing answers)
        'nouvelle_idee' => "J'ai une idée et je veux la concrétiser",
        'ameliorer' => "J'ai un projet existant à améliorer",
        'remplacer' => 'Je veux remplacer un processus actuel',
        'maquette' => "J'ai déjà une maquette ou un prototype",
        'nouveau' => "Je pars de zéro, c'est une nouvelle activité",
        'clients' => 'Trouver davantage de clients',
        'vendre' => 'Vendre davantage',
        'organiser' => 'Mieux organiser mon activité',
        'nouveau_service' => 'Créer un nouveau service',
        'ameliorer_existant' => 'Améliorer une solution existante',
        // Legacy problem labels
        'ec_manuelle' => 'Les commandes sont gérées manuellement',
        'ec_suivi' => 'Il est difficile de suivre les commandes',
        'ec_vente_en_ligne' => "L'entreprise veut vendre sur Internet",
        'ec_commandes_manuelles' => 'Les commandes sont gérées manuellement',
        'ec_suivi_difficile' => 'Il est difficile de suivre les commandes',
        'ec_clients_difficulte' => 'Les clients ont du mal à commander',
        'ec_paiements' => 'Les paiements sont compliqués',
        'ec_gestion_produits' => 'Les produits sont difficiles à gérer',
        'ec_pas_de_boutique' => "Je n'ai pas encore de boutique en ligne",
        'sv_invisible' => "Mon activité n'est pas visible sur Internet",
        'sv_site_insatisfaisant' => 'Mon site actuel ne me satisfait pas',
        'sv_attirer_clients' => "J'ai du mal à attirer de nouveaux clients",
        'sv_concurrence' => 'Mes concurrents ont un meilleur site',
        'sv_pas_de_site' => "Je n'ai pas de site professionnel",
        'sv_pas_visible' => 'Les visiteurs ne trouvent pas mon activité en ligne',
        'am_acces_difficile' => "Mes clients n'ont pas accès facilement à mon service",
        'am_experience_mobile' => 'Je veux offrir une expérience mobile à mes clients',
        'am_notifications' => 'Je veux envoyer des notifications à mes clients',
        'am_pas_sur_mobile' => "Mon service n'est pas encore sur mobile",
        'am_interactions' => 'Les interactions avec mes clients sont limitées',
        'lg_outils_dispersion' => "J'ai trop d'outils dispersés",
        'lg_pas_de_suivi' => "Je n'ai pas de suivi de mon activité",
        'lg_temps_perdu' => 'Mes employés perdent du temps',
        'lg_desorganisation' => 'La gestion des informations est désorganisée',
        // Legacy current tools labels
        'excel' => 'Excel ou tableur',
        'site_exist' => 'Site internet existant',
        'app_exist' => 'Application existante',
        'plusieurs_outils' => 'Plusieurs outils différents',
        'rien' => 'Rien pour le moment',
        // Legacy result labels
        'plus_clients' => 'Obtenir plus de clients',
        'reduire_manuel' => 'Réduire les tâches manuelles',
        'experience_client' => "Améliorer l'expérience client",
        'centraliser' => 'Centraliser les informations',
        'faciliter_employes' => 'Faciliter le travail des employés',
        // Legacy user labels
        'admin' => 'Administrateur(s)',
        'interne' => 'Usage interne uniquement',
        // Legacy workflow labels
        'navigation' => 'Il navigue / recherche',
        'action' => 'Il effectue une action (commande, réservation, etc.)',
        'confirmation' => 'Il reçoit une confirmation',
        'suivi' => "Il suit l'avancement (commande, livraison, etc.)",
        'notification' => 'Il reçoit des notifications',
        'support' => 'Il peut contacter le support',
        // Legacy style labels
        'luxe' => 'Luxe et élégant',
        'coloré' => 'Coloré et dynamique',
        'nature' => 'Naturel et organique',
        'tech' => 'Technique et futuriste',
        // Legacy budget labels
        'discuter' => 'Je préfère en discuter',
    ];

    /**
     * Look up the label for a given option value.
     * Checks DB options first, then known wizard labels.
     */
    public function getOptionLabel(string $value): ?string
    {
        $label = $this->options()
            ->where('value', $value)
            ->value('label');

        if ($label) {
            return $label;
        }

        return self::$wizardLabels[$value] ?? null;
    }

    /**
     * Convert an array of option values to their labels.
     * Values not found anywhere are returned as-is.
     */
    public function valuesToLabels(array $values): array
    {
        return array_map(function ($value) {
            return $this->getOptionLabel($value) ?? $value;
        }, $values);
    }
}
