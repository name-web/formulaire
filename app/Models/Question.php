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
     * These cover the wizard's inline options (steps 3-12).
     */
    private static array $wizardLabels = [
        // Step 8: Features (wizard adaptive values)
        'compte' => 'Créer un compte / Se connecter',
        'catalogue' => 'Consulter le catalogue de produits',
        'panier' => 'Ajouter au panier et commander',
        'paiement_en_ligne' => 'Payer en ligne',
        'suivi_cmd' => 'Suivre mes commandes',
        'avis' => 'Laisser des avis',
        'recherche' => 'Rechercher un produit',
        'promos' => 'Appliquer des codes promo',
        'compte_client' => 'Gérer mon espace client',
        'inscription' => "S'inscrire / Se connecter",
        'notifications' => 'Recevoir des notifications',
        'profil' => 'Gérer mon profil',
        'reservation' => 'Réserver / Commander',
        'paiement' => "Payer depuis l'application",
        'localisation' => 'Utiliser la géolocalisation',
        'chat' => 'Envoyer / Recevoir des messages',
        'partage' => "Partager avec d'autres utilisateurs",
        'dashboard' => 'Voir un tableau de bord',
        'clients_gestion' => 'Gérer mes clients',
        'ventes_gestion' => 'Gérer mes ventes',
        'facturation' => 'Créer et envoyer des factures',
        'stocks' => 'Gérer les stocks',
        'rapports' => 'Générer des rapports',
        'multi_user' => 'Plusieurs utilisateurs avec des droits différents',
        'import_export' => 'Importer / Exporter des données',
        'presentation' => 'Présenter mes services / activité',
        'contact_form' => 'Formulaire de contact',
        'rdv' => 'Prise de rendez-vous en ligne',
        'blog' => 'Publier des articles / actualités',
        'galerie' => 'Galerie photos / réalisations',
        'temoignages' => 'Afficher des témoignages',
        'faq' => 'FAQ (questions fréquentes)',
        'devis' => 'Demander un devis en ligne',
        // Step 3: Pourquoi
        'developper' => 'Développer mon activité',
        'clients' => 'Trouver davantage de clients',
        'vendre' => 'Vendre davantage',
        'temps' => 'Gagner du temps',
        'automatiser' => 'Automatiser certaines tâches',
        'organiser' => 'Mieux organiser mon activité',
        'ameliorer' => 'Améliorer mon service',
        'nouveau_service' => 'Créer un nouveau service',
        'remplacer' => 'Remplacer une méthode actuelle',
        'ameliorer_existant' => 'Améliorer une solution existante',
        'nouvelle_idee' => 'Réaliser une nouvelle idée',
        // Step 4: Problème
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
        'lg_processus_manuels' => 'Mes processus sont trop manuels',
        'lg_outils_dispersion' => "J'ai trop d'outils dispersés",
        'lg_pas_de_suivi' => "Je n'ai pas de suivi de mon activité",
        'lg_temps_perdu' => 'Mes employés perdent du temps',
        'lg_automatiser' => 'Je veux automatiser certaines tâches',
        'lg_desorganisation' => 'La gestion des informations est désorganisée',
        // Step 5: Actuel
        'whatsapp' => 'WhatsApp',
        'appels' => 'Appels téléphoniques',
        'reseaux' => 'Réseaux sociaux',
        'cahier' => 'Cahier / Papier',
        'excel' => 'Excel ou tableur',
        'site_exist' => 'Site internet existant',
        'app_exist' => 'Application existante',
        'plusieurs_outils' => 'Plusieurs outils différents',
        'rien' => 'Rien pour le moment',
        // Step 6: Résultat
        'plus_clients' => 'Obtenir plus de clients',
        'ventes' => 'Augmenter les ventes',
        'reduire_manuel' => 'Réduire les tâches manuelles',
        'experience_client' => "Améliorer l'expérience client",
        'centraliser' => 'Centraliser les informations',
        'faciliter_employes' => 'Faciliter le travail des employés',
        'visibilite' => 'Avoir une meilleure visibilité',
        // Step 7: Utilisateurs
        'employes' => 'Mes employés',
        'admin' => 'Administrateur(s)',
        'partenaires' => 'Partenaires',
        'fournisseurs' => 'Fournisseurs',
        'grand_public' => 'Grand public',
        'interne' => 'Usage interne uniquement',
        // Step 9: Workflow
        'navigation' => 'Il navigue / recherche',
        'action' => 'Il effectue une action (commande, réservation, etc.)',
        'confirmation' => 'Il reçoit une confirmation',
        'suivi' => "Il suit l'avancement (commande, livraison, etc.)",
        'notification' => 'Il reçoit des notifications',
        'support' => 'Il peut contacter le support',
        // Step 10: Style
        'moderne' => 'Moderne et épuré',
        'luxe' => 'Luxe et élégant',
        'coloré' => 'Coloré et dynamique',
        'sobre' => 'Sobre et professionnel',
        'jeune' => 'Jeune et tendance',
        'nature' => 'Naturel et organique',
        'tech' => 'Technique et futuriste',
        // Step 11: Budget
        'moins_500k' => 'Moins de 500 000 FCFA',
        '500k_1m' => '500 000 à 1 000 000 FCFA',
        '1m_3m' => '1 000 000 à 3 000 000 FCFA',
        'plus_3m' => 'Plus de 3 000 000 FCFA',
        'discuter' => 'Je préfère en discuter',
        // Step 11.1: Delay
        'moins_1_mois' => "Moins d'1 mois",
        '1_3_mois' => '1 à 3 mois',
        '3_6_mois' => '3 à 6 mois',
        'plus_6_mois' => 'Plus de 6 mois',
        'aucun' => 'Aucune date précise',
        // Step 9: Fonctionnalités (DB Q9)
        'relation_client' => 'Améliorer la relation client',
        'gestion' => 'Gérer mieux mon entreprise',
        // Step 13: Problème universal
        'autre' => 'Autre',
        'nsa' => 'Je ne sais pas encore',
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
