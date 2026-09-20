<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Structure du formulaire de devis automatique
    |--------------------------------------------------------------------------
    |
    | Source unique des libellés, aides et choix du parcours client. Les vues
    | et la validation s'appuient sur ce fichier : le formulaire reste ainsi
    | cohérent avec le dossier fonctionnel sans dupliquer les textes.
    |
    | Chaque choix est un tableau [valeur, libellé, aide].
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Étape 3 — question de détail selon le type de projet
    |--------------------------------------------------------------------------
    |
    | Les types « site vitrine » et « refonte » partagent le même détail.
    |
    */
    'variantes' => [
        'site_vitrine' => 'site',
        'refonte' => 'site',
        'ecommerce' => 'ecommerce',
        'logiciel' => 'logiciel',
        'scolaire' => 'scolaire',
        'application_mobile' => 'mobile',
    ],

    /*
    |--------------------------------------------------------------------------
    | Identité visuelle (types site, ecommerce et mobile)
    |--------------------------------------------------------------------------
    */
    'identite' => [
        ['aucune', "J'ai déjà mon logo et mes couleurs", ''],
        ['logo', 'Créer mon logo', ''],
        ['logo_charte', 'Créer logo et charte graphique complète', ''],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fonctions optionnelles par variante
    |--------------------------------------------------------------------------
    */
    'options' => [

        'site' => [
            ['blog', 'Blog / actualités', 'Publier vos articles vous-même — très bon pour Google'],
            ['galerie', 'Galerie de réalisations', 'Photos de vos travaux, filtrables par catégorie'],
            ['form_avance', 'Formulaire de devis en ligne', 'Champs conditionnels, pièces jointes, enregistrement des demandes'],
            ['rdv', 'Prise de rendez-vous', 'Calendrier et confirmation automatique'],
            ['membre', 'Espace client sécurisé', "Connexion, mots de passe, droits d'accès"],
            ['multilingue', 'Site en français et en anglais', ''],
        ],

        'ecommerce' => [
            ['notifs', 'Notifications automatiques', 'Commande reçue, payée, expédiée, livrée'],
            ['dashboard', 'Tableau de bord vendeur', 'Ventes du jour, produits les plus vendus, état du stock'],
            ['promos', 'Codes promotionnels', ''],
            ['photos_produits', 'Photos de mes produits en studio', 'Réalisé par DOMINIUM COMM — par produit'],
        ],

        'logiciel' => [
            ['demarrage', 'Accompagnement sur site au démarrage', 'Un technicien présent les premiers jours'],
        ],

        'mobile' => [
            ['push', 'Notifications push', 'Envoyer des messages depuis votre back-office'],
            ['horsligne', 'Fonctionne sans connexion', 'Synchronisation automatique au retour du réseau'],
        ],

        'scolaire' => [],

    ],

    /*
    |--------------------------------------------------------------------------
    | Modules facturés séparément
    |--------------------------------------------------------------------------
    */
    'modules' => [

        'logiciel' => [
            ['stock', 'Gestion de stock', 'Entrées, sorties, seuils d\'alerte, inventaire, valorisation'],
            ['caisse', 'Caisse / point de vente', 'Ventes, tickets, clôture de caisse journalière'],
            ['facturation', 'Facturation et devis', 'Numérotation automatique, TVA, relances, export PDF'],
            ['crm', 'Clients (CRM)', 'Fiches, historique, relances, segmentation'],
            ['rh', 'Ressources humaines', 'Employés, présences, congés, éléments de paie'],
            ['compta', 'Comptabilité simplifiée', 'Recettes, dépenses, exports pour le comptable'],
        ],

        'scolaire' => [
            ['eleves', 'Élèves et inscriptions', 'Dossiers, documents, photos, historique de scolarité'],
            ['notes', 'Notes et moyennes', 'Saisie par matière, coefficients, rangs, statistiques'],
            ['bulletins', 'Bulletins', 'Modèle personnalisé, appréciations, édition en masse en PDF'],
            ['absences', 'Absences et discipline', 'Retards, justificatifs, alertes aux parents'],
            ['parents', 'Espace parents', 'Notes, absences, messages, situation de scolarité'],
            ['paiements', 'Scolarité et paiements', 'Échéanciers, encaissements, reste à payer, reçus'],
            ['edt', 'Emploi du temps', 'Salles, enseignants, publication aux élèves'],
            ['sms', 'SMS aux parents', 'Passerelle SMS et modèles de messages'],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Contenus, paiement, plateforme, reprise
    |--------------------------------------------------------------------------
    */
    'contenus' => [
        ['fournis', 'Je fournis tout', 'Textes et images prêts'],
        ['rediger', 'DOMINIUM rédige les textes', 'Contenu original optimisé pour Google'],
    ],

    'paiement' => [
        ['mobile', 'Paiement mobile', 'Wave, Orange Money, MTN MoMo'],
        ['carte', 'Carte bancaire', ''],
        ['les_deux', 'Les deux', ''],
    ],

    'plateforme' => [
        ['android', 'Android uniquement', "Le plus répandu en Côte d'Ivoire"],
        ['ios', 'iPhone uniquement', ''],
        ['les_deux', 'Android et iPhone', 'Double le coût de développement'],
    ],

    'reprise' => [
        ['oui', 'Reprendre mes données existantes', "Import depuis Excel ou l'ancien logiciel"],
        ['non', 'Partir de zéro', ''],
    ],

    /*
    |--------------------------------------------------------------------------
    | Étape 4 — hébergement et suivi
    |--------------------------------------------------------------------------
    */
    'domaine' => [
        ['com', '.com — le plus courant', ''],
        ['ci', '.ci — domaine ivoirien', ''],
        ['aucun', "J'en ai déjà un", ''],
    ],

    'hebergement' => [
        ['mutualise', 'Hébergement géré', 'Convient à un site vitrine ou professionnel'],
        ['vps', 'Serveur dédié', 'Recommandé pour une boutique ou un logiciel de gestion'],
        ['aucun', "J'en ai déjà un", ''],
    ],

    'maintenance' => [
        ['essentiel', 'Essentiel', 'Sauvegardes, mises à jour, 2 h de modifications par mois'],
        ['business', 'Business', '6 h de modifications, suivi du référencement, chef de projet'],
        ['partenaire', 'Partenaire', 'Chef de projet dédié, feuille de route annuelle, formation'],
        ['aucune', "Pas de maintenance pour l'instant", ''],
    ],

    /*
    |--------------------------------------------------------------------------
    | Étape 5 — niveau de finition et délai
    |--------------------------------------------------------------------------
    */
    'niveau' => [
        ['ESSENTIEL', 'Essentiel', 'Le strict nécessaire, bien fait. Modèle adapté, un tour de correction.'],
        ['STANDARD', 'Standard — recommandé', 'Création sur mesure, deux tours de correction, optimisation, formation.'],
        ['PREMIUM', 'Premium', 'Sur mesure complet, exigences élevées, accompagnement renforcé.'],
    ],

    'delai' => [
        ['normal', 'Délai normal', ''],
        ['accelere', 'Livraison prioritaire', 'Majoration de 30 % — mobilisation d\'équipe renforcée'],
    ],

];
