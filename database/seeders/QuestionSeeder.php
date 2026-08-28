<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionCondition;
use App\Models\QuestionOption;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================================
        // ÉTAPE 03 — VOTRE PROJET (questions discovery/motivation)
        // ============================================================

        // --- Pourquoi ---
        $qWhy = Question::create([
            'title' => 'Pourquoi souhaitez-vous créer cette solution ?',
            'description' => 'Sélectionnez la ou les raisons principales.',
            'type' => 'multiselect',
            'project_types' => ['site_vitrine', 'ecommerce', 'application_mobile', 'logiciel', 'autre'],
            'is_required' => false,
            'sort_order' => 5,
            'group_label' => 'Votre projet',
            'placeholder' => null,
            'help_text' => 'Vous pouvez sélectionner plusieurs réponses.',
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qWhy->id, 'label' => 'Développer mon activité', 'value' => 'developper', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qWhy->id, 'label' => 'Trouver davantage de clients', 'value' => 'clients', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qWhy->id, 'label' => 'Vendre davantage', 'value' => 'vendre', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qWhy->id, 'label' => 'Gagner du temps', 'value' => 'temps', 'sort_order' => 4, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qWhy->id, 'label' => 'Automatiser certaines tâches', 'value' => 'automatiser', 'sort_order' => 5, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qWhy->id, 'label' => 'Mieux organiser mon activité', 'value' => 'organiser', 'sort_order' => 6, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qWhy->id, 'label' => 'Améliorer mon service', 'value' => 'ameliorer', 'sort_order' => 7, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qWhy->id, 'label' => 'Créer un nouveau service', 'value' => 'nouveau_service', 'sort_order' => 8, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qWhy->id, 'label' => 'Remplacer une méthode actuelle', 'value' => 'remplacer', 'sort_order' => 9, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qWhy->id, 'label' => 'Améliorer une solution existante', 'value' => 'ameliorer_existant', 'sort_order' => 10, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qWhy->id, 'label' => 'Réaliser une nouvelle idée', 'value' => 'nouvelle_idee', 'sort_order' => 11, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qWhy->id, 'label' => 'Autre', 'value' => 'autre', 'sort_order' => 12, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qWhy->id, 'label' => 'Je ne sais pas encore', 'value' => 'nsa', 'sort_order' => 13, 'is_active' => true]);

        // --- Quel problème (adaptatif par type de projet) ---
        // Options universelles + options par type de projet
        $qProblem = Question::create([
            'title' => 'Quel problème cherchez-vous à résoudre ?',
            'description' => 'Sélectionnez le ou les problèmes qui correspondent le mieux à votre situation.',
            'type' => 'multiselect',
            'project_types' => ['site_vitrine', 'ecommerce', 'application_mobile', 'logiciel', 'autre'],
            'is_required' => false,
            'sort_order' => 6,
            'group_label' => 'Votre projet',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        // Site vitrine
        QuestionOption::create(['question_id' => $qProblem->id, 'label' => 'Les visiteurs ne trouvent pas mon activité en ligne', 'value' => 'sv_pas_visible', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qProblem->id, 'label' => 'Mon site actuel ne me satisfait pas', 'value' => 'sv_site_insatisfaisant', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qProblem->id, 'label' => "J'ai du mal à attirer de nouveaux clients", 'value' => 'sv_attirer_clients', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qProblem->id, 'label' => 'Mes concurrents ont un meilleur site', 'value' => 'sv_concurrence', 'sort_order' => 4, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qProblem->id, 'label' => "Je n'ai pas de site professionnel", 'value' => 'sv_pas_de_site', 'sort_order' => 5, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qProblem->id, 'label' => "Mon activité n'est pas visible sur Internet", 'value' => 'sv_invisible', 'sort_order' => 6, 'is_active' => true]);

        // E-commerce
        QuestionOption::create(['question_id' => $qProblem->id, 'label' => 'Les commandes sont gérées manuellement', 'value' => 'ec_commandes_manuelles', 'sort_order' => 10, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qProblem->id, 'label' => 'Les clients ont du mal à commander', 'value' => 'ec_clients_difficulte', 'sort_order' => 11, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qProblem->id, 'label' => 'Il est difficile de suivre les commandes', 'value' => 'ec_suivi_difficile', 'sort_order' => 12, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qProblem->id, 'label' => 'Les paiements sont compliqués', 'value' => 'ec_paiements', 'sort_order' => 13, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qProblem->id, 'label' => 'Les produits sont difficiles à gérer', 'value' => 'ec_gestion_produits', 'sort_order' => 14, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qProblem->id, 'label' => "L'entreprise veut vendre sur Internet", 'value' => 'ec_vente_en_ligne', 'sort_order' => 15, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qProblem->id, 'label' => "Je n'ai pas encore de boutique en ligne", 'value' => 'ec_pas_de_boutique', 'sort_order' => 16, 'is_active' => true]);

        // Application mobile
        QuestionOption::create(['question_id' => $qProblem->id, 'label' => "Mes clients n'ont pas accès facilement à mon service", 'value' => 'am_acces_difficile', 'sort_order' => 20, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qProblem->id, 'label' => 'Je veux offrir une expérience mobile à mes clients', 'value' => 'am_experience_mobile', 'sort_order' => 21, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qProblem->id, 'label' => 'Je veux envoyer des notifications à mes clients', 'value' => 'am_notifications', 'sort_order' => 22, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qProblem->id, 'label' => "Mon service n'est pas encore sur mobile", 'value' => 'am_pas_sur_mobile', 'sort_order' => 23, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qProblem->id, 'label' => 'Les interactions avec mes clients sont limitées', 'value' => 'am_interactions', 'sort_order' => 24, 'is_active' => true]);

        // Logiciel
        QuestionOption::create(['question_id' => $qProblem->id, 'label' => 'Mes processus sont trop manuels', 'value' => 'lg_processus_manuels', 'sort_order' => 30, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qProblem->id, 'label' => "J'ai trop d'outils dispersés", 'value' => 'lg_outils_dispersion', 'sort_order' => 31, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qProblem->id, 'label' => "Je n'ai pas de suivi de mon activité", 'value' => 'lg_pas_de_suivi', 'sort_order' => 32, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qProblem->id, 'label' => 'Mes employés perdent du temps', 'value' => 'lg_temps_perdu', 'sort_order' => 33, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qProblem->id, 'label' => 'Je veux automatiser certaines tâches', 'value' => 'lg_automatiser', 'sort_order' => 34, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qProblem->id, 'label' => 'La gestion des informations est désorganisée', 'value' => 'lg_desorganisation', 'sort_order' => 35, 'is_active' => true]);

        // Universels
        QuestionOption::create(['question_id' => $qProblem->id, 'label' => 'Autre', 'value' => 'autre', 'sort_order' => 90, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qProblem->id, 'label' => 'Je ne sais pas encore', 'value' => 'nsa', 'sort_order' => 91, 'is_active' => true]);

        // --- Comment actuellement ---
        $qHow = Question::create([
            'title' => 'Comment gérez-vous actuellement cette activité ?',
            'description' => 'Cela nous aide à comprendre ce que la future solution doit remplacer ou améliorer.',
            'type' => 'multiselect',
            'project_types' => ['site_vitrine', 'ecommerce', 'application_mobile', 'logiciel', 'autre'],
            'is_required' => false,
            'sort_order' => 7,
            'group_label' => 'Votre projet',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qHow->id, 'label' => 'WhatsApp', 'value' => 'whatsapp', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qHow->id, 'label' => 'Appels téléphoniques', 'value' => 'appels', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qHow->id, 'label' => 'Réseaux sociaux', 'value' => 'reseaux', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qHow->id, 'label' => 'Cahier / Papier', 'value' => 'cahier', 'sort_order' => 4, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qHow->id, 'label' => 'Excel ou tableur', 'value' => 'excel', 'sort_order' => 5, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qHow->id, 'label' => 'Site internet existant', 'value' => 'site_exist', 'sort_order' => 6, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qHow->id, 'label' => 'Application existante', 'value' => 'app_exist', 'sort_order' => 7, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qHow->id, 'label' => 'Plusieurs outils différents', 'value' => 'plusieurs_outils', 'sort_order' => 8, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qHow->id, 'label' => 'Rien pour le moment', 'value' => 'rien', 'sort_order' => 9, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qHow->id, 'label' => 'Autre', 'value' => 'autre', 'sort_order' => 10, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qHow->id, 'label' => 'Je ne sais pas encore', 'value' => 'nsa', 'sort_order' => 11, 'is_active' => true]);

        // --- Quel résultat attendu ---
        $qResult = Question::create([
            'title' => 'Quel résultat attendez-vous ?',
            'description' => 'Qu\'aimeriez-vous obtenir grâce à cette solution ?',
            'type' => 'multiselect',
            'project_types' => ['site_vitrine', 'ecommerce', 'application_mobile', 'logiciel', 'autre'],
            'is_required' => false,
            'sort_order' => 8,
            'group_label' => 'Votre projet',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qResult->id, 'label' => 'Obtenir plus de clients', 'value' => 'plus_clients', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qResult->id, 'label' => 'Augmenter les ventes', 'value' => 'ventes', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qResult->id, 'label' => 'Gagner du temps', 'value' => 'temps', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qResult->id, 'label' => 'Réduire les tâches manuelles', 'value' => 'reduire_manuel', 'sort_order' => 4, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qResult->id, 'label' => "Mieux organiser l'activité", 'value' => 'organiser', 'sort_order' => 5, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qResult->id, 'label' => "Améliorer l'expérience client", 'value' => 'experience_client', 'sort_order' => 6, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qResult->id, 'label' => 'Centraliser les informations', 'value' => 'centraliser', 'sort_order' => 7, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qResult->id, 'label' => 'Faciliter le travail des employés', 'value' => 'faciliter_employes', 'sort_order' => 8, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qResult->id, 'label' => 'Proposer un nouveau service', 'value' => 'nouveau_service', 'sort_order' => 9, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qResult->id, 'label' => 'Avoir une meilleure visibilité', 'value' => 'visibilite', 'sort_order' => 10, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qResult->id, 'label' => 'Autre', 'value' => 'autre', 'sort_order' => 11, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qResult->id, 'label' => 'Je ne sais pas encore', 'value' => 'nsa', 'sort_order' => 12, 'is_active' => true]);

        // --- Description courte (optionnel) ---
        $qDesc = Question::create([
            'title' => 'Décrivez brièvement votre projet',
            'description' => 'En quelques mots, que souhaitez-vous créer ? (optionnel)',
            'type' => 'textarea',
            'project_types' => ['site_vitrine', 'ecommerce', 'application_mobile', 'logiciel', 'autre'],
            'is_required' => false,
            'sort_order' => 9,
            'group_label' => 'Votre projet',
            'placeholder' => 'Ex : Je veux une application pour gérer les réservations de mes clients...',
            'help_text' => null,
            'is_active' => true,
        ]);

        // --- Utilisateurs (optionnel) ---
        $qUsers = Question::create([
            'title' => 'Qui seront les principaux utilisateurs ?',
            'description' => 'À qui s\'adresse cette solution ? (optionnel)',
            'type' => 'textarea',
            'project_types' => ['site_vitrine', 'ecommerce', 'application_mobile', 'logiciel', 'autre'],
            'is_required' => false,
            'sort_order' => 10,
            'group_label' => 'Votre projet',
            'placeholder' => 'Ex : Mes clients, mes employés, le grand public...',
            'help_text' => null,
            'is_active' => true,
        ]);

        // ============================================================
        // QUESTION 1 : Solutions souhaitez-vous créer (SÉLECTION MULTIPLE)
        // ============================================================
        $qType = Question::create([
            'title' => 'Quelle solution souhaitez-vous créer ?',
            'description' => 'Sélectionnez le type de projet qui correspond le mieux à votre besoin.',
            'type' => 'multiselect',
            'project_types' => ['site_vitrine', 'ecommerce', 'application_mobile', 'logiciel', 'autre'],
            'is_required' => true,
            'sort_order' => 1,
            'group_label' => 'Votre projet',
            'placeholder' => null,
            'help_text' => 'Vous pouvez sélectionner plusieurs solutions.',
            'is_active' => true,
        ]);

        $optVitrine = QuestionOption::create(['question_id' => $qType->id, 'label' => 'Site vitrine', 'value' => 'site_vitrine', 'sort_order' => 1, 'is_active' => true]);
        $optEcommerce = QuestionOption::create(['question_id' => $qType->id, 'label' => 'Boutique en ligne', 'value' => 'ecommerce', 'sort_order' => 2, 'is_active' => true]);
        $optMobile = QuestionOption::create(['question_id' => $qType->id, 'label' => 'Application mobile', 'value' => 'application_mobile', 'sort_order' => 3, 'is_active' => true]);
        $optLogiciel = QuestionOption::create(['question_id' => $qType->id, 'label' => 'Logiciel / ERP / CRM', 'value' => 'logiciel', 'sort_order' => 4, 'is_active' => true]);
        $optAutre = QuestionOption::create(['question_id' => $qType->id, 'label' => 'Autre', 'value' => 'autre', 'sort_order' => 5, 'is_active' => true]);

        // ============================================================
        // QUESTION 2 : Description du besoin
        // ============================================================
        Question::create([
            'title' => 'Décrivez votre projet en quelques mots',
            'description' => 'Expliquez-nous simplement ce que vous souhaitez créer. Plus nous comprenons votre idée, mieux nous pourrons vous aider.',
            'type' => 'textarea',
            'project_types' => ['site_vitrine', 'ecommerce', 'application_mobile', 'logiciel', 'autre'],
            'is_required' => true,
            'sort_order' => 2,
            'group_label' => 'Votre projet',
            'placeholder' => 'Ex : Je veux un site pour présenter mes services de coiffure et permettre aux clients de prendre rendez-vous en ligne...',
            'help_text' => null,
            'is_active' => true,
        ]);

        // ============================================================
        // QUESTION 3 : Objectifs
        // ============================================================
        $qObjectifs = Question::create([
            'title' => 'Quels sont vos objectifs principaux ?',
            'description' => 'Qu\'est-ce que vous voulez atteindre avec ce projet ?',
            'type' => 'multiselect',
            'project_types' => ['site_vitrine', 'ecommerce', 'application_mobile', 'logiciel', 'autre'],
            'is_required' => true,
            'sort_order' => 3,
            'group_label' => 'Votre projet',
            'placeholder' => null,
            'help_text' => 'Vous pouvez sélectionner plusieurs réponses.',
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qObjectifs->id, 'label' => 'Augmenter mes ventes', 'value' => 'ventes', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qObjectifs->id, 'label' => 'Renforcer ma visibilité', 'value' => 'visibilite', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qObjectifs->id, 'label' => 'Automatiser mes processus', 'value' => 'automatiser', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qObjectifs->id, 'label' => 'Améliorer la relation client', 'value' => 'relation_client', 'sort_order' => 4, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qObjectifs->id, 'label' => 'Gérer mieux mon entreprise', 'value' => 'gestion', 'sort_order' => 5, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qObjectifs->id, 'label' => 'Autre', 'value' => 'autre', 'sort_order' => 6, 'is_active' => true]);

        // ============================================================
        // QUESTION 4 : Utilisateurs cibles
        // ============================================================
        Question::create([
            'title' => 'Qui seront les utilisateurs de votre projet ?',
            'description' => 'À qui s\'adresse votre projet ? Décrivez-nous vos utilisateurs.',
            'type' => 'textarea',
            'project_types' => ['site_vitrine', 'ecommerce', 'application_mobile', 'logiciel', 'autre'],
            'is_required' => true,
            'sort_order' => 4,
            'group_label' => 'Votre projet',
            'placeholder' => 'Ex : Des particuliers de 25 à 45 ans qui cherchent un prestataire de services à Abidjan...',
            'help_text' => null,
            'is_active' => true,
        ]);

        // ============================================================
        // SITE VITRINE — Pages
        // ============================================================
        $qPages = Question::create([
            'title' => 'Quelles pages souhaitez-vous sur votre site ?',
            'description' => 'Sélectionnez les pages dont vous avez besoin.',
            'type' => 'multiselect',
            'project_types' => ['site_vitrine'],
            'is_required' => true,
            'sort_order' => 10,
            'group_label' => 'Site vitrine',
            'placeholder' => null,
            'help_text' => 'Vous pouvez sélectionner plusieurs pages.',
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qPages->id, 'label' => 'Accueil', 'value' => 'accueil', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPages->id, 'label' => 'À propos / Notre histoire', 'value' => 'apropos', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPages->id, 'label' => 'Nos services / prestations', 'value' => 'services', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPages->id, 'label' => 'Contact', 'value' => 'contact', 'sort_order' => 4, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPages->id, 'label' => 'Blog / Actualités', 'value' => 'blog', 'sort_order' => 5, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPages->id, 'label' => 'Galerie / Réalisations', 'value' => 'galerie', 'sort_order' => 6, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPages->id, 'label' => 'Témoignages / Avis clients', 'value' => 'temoignages', 'sort_order' => 7, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPages->id, 'label' => 'FAQ', 'value' => 'faq', 'sort_order' => 8, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPages->id, 'label' => 'Autre', 'value' => 'autre', 'sort_order' => 9, 'is_active' => true]);

        QuestionCondition::create([
            'question_id' => $qPages->id,
            'depends_on_question_id' => $qType->id,
            'operator' => 'equals',
            'option_value_id' => $optVitrine->id,
            'text_value' => null,
            'sort_order' => 1,
        ]);

        // ============================================================
        // E-COMMERCE
        // ============================================================
        $qNbProduits = Question::create([
            'title' => 'Combien de produits comptez-vous vendre en ligne ?',
            'description' => null,
            'type' => 'select',
            'project_types' => ['ecommerce'],
            'is_required' => true,
            'sort_order' => 20,
            'group_label' => 'Boutique en ligne',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qNbProduits->id, 'label' => 'Moins de 50 produits', 'value' => 'moins_50', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qNbProduits->id, 'label' => '50 à 200 produits', 'value' => '50_200', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qNbProduits->id, 'label' => '200 à 1000 produits', 'value' => '200_1000', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qNbProduits->id, 'label' => 'Plus de 1000 produits', 'value' => 'plus_1000', 'sort_order' => 4, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qNbProduits->id, 'label' => 'Autre', 'value' => 'autre', 'sort_order' => 5, 'is_active' => true]);

        QuestionCondition::create(['question_id' => $qNbProduits->id, 'depends_on_question_id' => $qType->id, 'operator' => 'equals', 'option_value_id' => $optEcommerce->id, 'text_value' => null, 'sort_order' => 1]);

        $qTypeProduits = Question::create([
            'title' => 'Quel type de produits vendez-vous ?',
            'description' => 'Cela nous aide à comprendre vos besoins spécifiques.',
            'type' => 'multiselect',
            'project_types' => ['ecommerce'],
            'is_required' => true,
            'sort_order' => 21,
            'group_label' => 'Boutique en ligne',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qTypeProduits->id, 'label' => 'Produits physiques (vêtements, accessoires...)', 'value' => 'physiques', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qTypeProduits->id, 'label' => 'Produits numériques (e-books, formations...)', 'value' => 'numeriques', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qTypeProduits->id, 'label' => 'Services (consulting, abonnements...)', 'value' => 'services', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qTypeProduits->id, 'label' => 'Autre', 'value' => 'autre', 'sort_order' => 4, 'is_active' => true]);

        QuestionCondition::create(['question_id' => $qTypeProduits->id, 'depends_on_question_id' => $qType->id, 'operator' => 'equals', 'option_value_id' => $optEcommerce->id, 'text_value' => null, 'sort_order' => 2]);

        $qFonctionnalitesEcommerce = Question::create([
            'title' => 'Quelles fonctionnalités souhaitez-vous pour votre boutique ?',
            'description' => 'Sélectionnez toutes les fonctionnalités dont vous avez besoin.',
            'type' => 'multiselect',
            'project_types' => ['ecommerce'],
            'is_required' => true,
            'sort_order' => 22,
            'group_label' => 'Boutique en ligne',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qFonctionnalitesEcommerce->id, 'label' => 'Panier d\'achat', 'value' => 'panier', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qFonctionnalitesEcommerce->id, 'label' => 'Paiement en ligne', 'value' => 'paiement', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qFonctionnalitesEcommerce->id, 'label' => 'Gestion des stocks', 'value' => 'stocks', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qFonctionnalitesEcommerce->id, 'label' => 'Compte client', 'value' => 'compte_client', 'sort_order' => 4, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qFonctionnalitesEcommerce->id, 'label' => 'Suivi de commandes', 'value' => 'suivi_commandes', 'sort_order' => 5, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qFonctionnalitesEcommerce->id, 'label' => 'Promotions et codes promo', 'value' => 'promos', 'sort_order' => 6, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qFonctionnalitesEcommerce->id, 'label' => 'Avis clients', 'value' => 'avis', 'sort_order' => 7, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qFonctionnalitesEcommerce->id, 'label' => 'Wishlist (liste de souhaits)', 'value' => 'wishlist', 'sort_order' => 8, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qFonctionnalitesEcommerce->id, 'label' => 'Autre', 'value' => 'autre', 'sort_order' => 9, 'is_active' => true]);

        QuestionCondition::create(['question_id' => $qFonctionnalitesEcommerce->id, 'depends_on_question_id' => $qType->id, 'operator' => 'equals', 'option_value_id' => $optEcommerce->id, 'text_value' => null, 'sort_order' => 3]);

        $qLivraison = Question::create([
            'title' => 'Comment souhaitez-vous gérer la livraison ?',
            'description' => null,
            'type' => 'select',
            'project_types' => ['ecommerce'],
            'is_required' => true,
            'sort_order' => 23,
            'group_label' => 'Boutique en ligne',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qLivraison->id, 'label' => 'Livraison à domicile', 'value' => 'domicile', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qLivraison->id, 'label' => 'Retrait en point relais', 'value' => 'relais', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qLivraison->id, 'label' => 'Les deux', 'value' => 'les_deux', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qLivraison->id, 'label' => 'Livraison numérique (pas de livraison physique)', 'value' => 'numerique', 'sort_order' => 4, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qLivraison->id, 'label' => 'Autre', 'value' => 'autre', 'sort_order' => 5, 'is_active' => true]);

        QuestionCondition::create(['question_id' => $qLivraison->id, 'depends_on_question_id' => $qType->id, 'operator' => 'equals', 'option_value_id' => $optEcommerce->id, 'text_value' => null, 'sort_order' => 4]);

        // ============================================================
        // APPLICATION MOBILE
        // ============================================================
        $qPlateforme = Question::create([
            'title' => 'Sur quelles plateformes voulez-vous votre application ?',
            'description' => null,
            'type' => 'multiselect',
            'project_types' => ['application_mobile'],
            'is_required' => true,
            'sort_order' => 30,
            'group_label' => 'Application mobile',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qPlateforme->id, 'label' => 'iPhone (iOS)', 'value' => 'ios', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPlateforme->id, 'label' => 'Android', 'value' => 'android', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPlateforme->id, 'label' => 'Les deux', 'value' => 'les_deux', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPlateforme->id, 'label' => 'Autre', 'value' => 'autre', 'sort_order' => 4, 'is_active' => true]);

        QuestionCondition::create(['question_id' => $qPlateforme->id, 'depends_on_question_id' => $qType->id, 'operator' => 'equals', 'option_value_id' => $optMobile->id, 'text_value' => null, 'sort_order' => 1]);

        $qFonctionnalitesMobile = Question::create([
            'title' => 'Quelles fonctionnalités souhaitez-vous dans votre application ?',
            'description' => 'Sélectionnez toutes les fonctionnalités pertinentes.',
            'type' => 'multiselect',
            'project_types' => ['application_mobile'],
            'is_required' => true,
            'sort_order' => 31,
            'group_label' => 'Application mobile',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qFonctionnalitesMobile->id, 'label' => 'Inscription / Connexion utilisateur', 'value' => 'auth', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qFonctionnalitesMobile->id, 'label' => 'Notifications push', 'value' => 'push', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qFonctionnalitesMobile->id, 'label' => 'Caméra / Photo', 'value' => 'camera', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qFonctionnalitesMobile->id, 'label' => 'Géolocalisation', 'value' => 'geo', 'sort_order' => 4, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qFonctionnalitesMobile->id, 'label' => 'Chat / Messagerie', 'value' => 'chat', 'sort_order' => 5, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qFonctionnalitesMobile->id, 'label' => 'Paiement intégré', 'value' => 'paiement', 'sort_order' => 6, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qFonctionnalitesMobile->id, 'label' => 'Scan de QR code', 'value' => 'qr', 'sort_order' => 7, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qFonctionnalitesMobile->id, 'label' => 'Mode hors ligne', 'value' => 'offline', 'sort_order' => 8, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qFonctionnalitesMobile->id, 'label' => 'Autre', 'value' => 'autre', 'sort_order' => 9, 'is_active' => true]);

        QuestionCondition::create(['question_id' => $qFonctionnalitesMobile->id, 'depends_on_question_id' => $qType->id, 'operator' => 'equals', 'option_value_id' => $optMobile->id, 'text_value' => null, 'sort_order' => 1]);

        // ============================================================
        // LOGICIEL / ERP / CRM
        // ============================================================
        $qProcessus = Question::create([
            'title' => 'Quels processus métier souhaitez-vous gérer ?',
            'description' => 'Décrivez les activités principales que le logiciel doit gérer.',
            'type' => 'textarea',
            'project_types' => ['logiciel'],
            'is_required' => true,
            'sort_order' => 40,
            'group_label' => 'Logiciel / ERP / CRM',
            'placeholder' => 'Ex : Gestion des clients, facturation, suivi des ventes, gestion des stocks, ressources humaines...',
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionCondition::create(['question_id' => $qProcessus->id, 'depends_on_question_id' => $qType->id, 'operator' => 'equals', 'option_value_id' => $optLogiciel->id, 'text_value' => null, 'sort_order' => 1]);

        $qUtilisateursLogiciel = Question::create([
            'title' => 'Combien d\'utilisateurs auront accès au logiciel ?',
            'description' => null,
            'type' => 'select',
            'project_types' => ['logiciel'],
            'is_required' => true,
            'sort_order' => 41,
            'group_label' => 'Logiciel / ERP / CRM',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qUtilisateursLogiciel->id, 'label' => '1 à 5 utilisateurs', 'value' => '1_5', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qUtilisateursLogiciel->id, 'label' => '6 à 20 utilisateurs', 'value' => '6_20', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qUtilisateursLogiciel->id, 'label' => '21 à 50 utilisateurs', 'value' => '21_50', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qUtilisateursLogiciel->id, 'label' => 'Plus de 50 utilisateurs', 'value' => 'plus_50', 'sort_order' => 4, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qUtilisateursLogiciel->id, 'label' => 'Autre', 'value' => 'autre', 'sort_order' => 5, 'is_active' => true]);

        QuestionCondition::create(['question_id' => $qUtilisateursLogiciel->id, 'depends_on_question_id' => $qType->id, 'operator' => 'equals', 'option_value_id' => $optLogiciel->id, 'text_value' => null, 'sort_order' => 2]);

        $qFonctionnalitesLogiciel = Question::create([
            'title' => 'Quelles fonctionnalités sont essentielles pour votre logiciel ?',
            'description' => 'Sélectionnez les modules ou fonctionnalités nécessaires.',
            'type' => 'multiselect',
            'project_types' => ['logiciel'],
            'is_required' => true,
            'sort_order' => 42,
            'group_label' => 'Logiciel / ERP / CRM',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qFonctionnalitesLogiciel->id, 'label' => 'Gestion des clients', 'value' => 'clients', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qFonctionnalitesLogiciel->id, 'label' => 'Gestion des ventes', 'value' => 'ventes', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qFonctionnalitesLogiciel->id, 'label' => 'Facturation', 'value' => 'facturation', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qFonctionnalitesLogiciel->id, 'label' => 'Gestion des stocks', 'value' => 'stocks', 'sort_order' => 4, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qFonctionnalitesLogiciel->id, 'label' => 'Tableau de bord / Rapports', 'value' => 'dashboard', 'sort_order' => 5, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qFonctionnalitesLogiciel->id, 'label' => 'Gestion des utilisateurs et droits d\'accès', 'value' => 'utilisateurs', 'sort_order' => 6, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qFonctionnalitesLogiciel->id, 'label' => 'Intégration avec d\'autres outils', 'value' => 'integrations', 'sort_order' => 7, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qFonctionnalitesLogiciel->id, 'label' => 'Autre', 'value' => 'autre', 'sort_order' => 8, 'is_active' => true]);

        QuestionCondition::create(['question_id' => $qFonctionnalitesLogiciel->id, 'depends_on_question_id' => $qType->id, 'operator' => 'equals', 'option_value_id' => $optLogiciel->id, 'text_value' => null, 'sort_order' => 3]);

        // ============================================================
        // IDENTITÉ VISUELLE
        // ============================================================
        $qIdentiteVisuelle = Question::create([
            'title' => 'Avez-vous déjà un logo et une identité visuelle ?',
            'description' => null,
            'type' => 'yes_no',
            'project_types' => ['site_vitrine', 'ecommerce', 'application_mobile', 'autre'],
            'is_required' => true,
            'sort_order' => 50,
            'group_label' => 'Identité visuelle',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        $qLogo = Question::create([
            'title' => 'Souhaitez-vous que nous créions votre logo ?',
            'description' => null,
            'type' => 'yes_no',
            'project_types' => ['site_vitrine', 'ecommerce', 'application_mobile', 'autre'],
            'is_required' => true,
            'sort_order' => 51,
            'group_label' => 'Identité visuelle',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionCondition::create(['question_id' => $qLogo->id, 'depends_on_question_id' => $qIdentiteVisuelle->id, 'operator' => 'equals', 'option_value_id' => null, 'text_value' => 'no', 'sort_order' => 1]);

        $qCouleurs = Question::create([
            'title' => 'Avez-vous des couleurs ou un style préféré ?',
            'description' => 'Décrivez-nous le style visuel que vous imaginez.',
            'type' => 'textarea',
            'project_types' => ['site_vitrine', 'ecommerce', 'application_mobile', 'autre'],
            'is_required' => false,
            'sort_order' => 52,
            'group_label' => 'Identité visuelle',
            'placeholder' => 'Ex : Des couleurs sobres (noir, blanc, doré), un style moderne et épuré...',
            'help_text' => null,
            'is_active' => true,
        ]);

        $qLogoFile = Question::create([
            'title' => 'Envoyez-nous votre logo',
            'description' => 'Formats acceptés : PNG, SVG, PDF. Taille maximale : 5 Mo.',
            'type' => 'file',
            'project_types' => ['site_vitrine', 'ecommerce', 'application_mobile', 'autre'],
            'is_required' => false,
            'sort_order' => 53,
            'group_label' => 'Identité visuelle',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionCondition::create(['question_id' => $qLogoFile->id, 'depends_on_question_id' => $qIdentiteVisuelle->id, 'operator' => 'equals', 'option_value_id' => null, 'text_value' => 'yes', 'sort_order' => 1]);

        // ============================================================
        // CONTENU
        // ============================================================
        $qContenu = Question::create([
            'title' => 'Avez-vous déjà le contenu prêt ?',
            'description' => 'Le contenu inclut les textes, les photos, les vidéos, etc.',
            'type' => 'yes_no',
            'project_types' => ['site_vitrine', 'ecommerce'],
            'is_required' => true,
            'sort_order' => 60,
            'group_label' => 'Contenu',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        $qAideContenu = Question::create([
            'title' => 'Avez-vous besoin d\'aide pour créer le contenu ?',
            'description' => null,
            'type' => 'multiselect',
            'project_types' => ['site_vitrine', 'ecommerce'],
            'is_required' => true,
            'sort_order' => 61,
            'group_label' => 'Contenu',
            'placeholder' => null,
            'help_text' => 'Sélectionnez les éléments pour lesquels vous avez besoin d\'aide.',
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qAideContenu->id, 'label' => 'Rédaction des textes', 'value' => 'textes', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qAideContenu->id, 'label' => 'Photos / Images', 'value' => 'photos', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qAideContenu->id, 'label' => 'Vidéos', 'value' => 'videos', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qAideContenu->id, 'label' => 'Logo', 'value' => 'logo', 'sort_order' => 4, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qAideContenu->id, 'label' => 'Autre', 'value' => 'autre', 'sort_order' => 5, 'is_active' => true]);

        QuestionCondition::create(['question_id' => $qAideContenu->id, 'depends_on_question_id' => $qContenu->id, 'operator' => 'equals', 'option_value_id' => null, 'text_value' => 'no', 'sort_order' => 1]);

        // ============================================================
        // INTÉGRATIONS
        // ============================================================
        $qIntegrations = Question::create([
            'title' => 'Avez-vous besoin de connecter votre projet à d\'autres outils ?',
            'description' => 'Ex : réseaux sociaux, outil de paiement, logiciel de comptabilité, CRM...',
            'type' => 'yes_no',
            'project_types' => ['site_vitrine', 'ecommerce', 'application_mobile', 'logiciel', 'autre'],
            'is_required' => true,
            'sort_order' => 70,
            'group_label' => 'Intégrations',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        $qIntegrationsDetails = Question::create([
            'title' => 'Quels outils souhaitez-vous connecter ?',
            'description' => 'Listez les outils ou services que vous utilisez déjà.',
            'type' => 'textarea',
            'project_types' => ['site_vitrine', 'ecommerce', 'application_mobile', 'logiciel', 'autre'],
            'is_required' => true,
            'sort_order' => 71,
            'group_label' => 'Intégrations',
            'placeholder' => 'Ex : Instagram, WhatsApp Business, Stripe, QuickBooks, Salesforce...',
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionCondition::create(['question_id' => $qIntegrationsDetails->id, 'depends_on_question_id' => $qIntegrations->id, 'operator' => 'equals', 'option_value_id' => null, 'text_value' => 'yes', 'sort_order' => 1]);

        // ============================================================
        // PAIEMENTS — E-COMMERCE
        // ============================================================
        $qPaiements = Question::create([
            'title' => 'Quels moyens de paiement souhaitez-vous accepter ?',
            'description' => null,
            'type' => 'multiselect',
            'project_types' => ['ecommerce'],
            'is_required' => true,
            'sort_order' => 75,
            'group_label' => 'Paiements',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qPaiements->id, 'label' => 'Carte bancaire (Visa, Mastercard)', 'value' => 'cb', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPaiements->id, 'label' => 'Mobile Money (Orange, MTN, Wave)', 'value' => 'mobile_money', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPaiements->id, 'label' => 'PayPal', 'value' => 'paypal', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPaiements->id, 'label' => 'Paiement à la livraison', 'value' => 'a_la_livraison', 'sort_order' => 4, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPaiements->id, 'label' => 'Virement bancaire', 'value' => 'virement', 'sort_order' => 5, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPaiements->id, 'label' => 'Autre', 'value' => 'autre', 'sort_order' => 6, 'is_active' => true]);

        QuestionCondition::create(['question_id' => $qPaiements->id, 'depends_on_question_id' => $qType->id, 'operator' => 'equals', 'option_value_id' => $optEcommerce->id, 'text_value' => null, 'sort_order' => 1]);

        // ============================================================
        // PAIEMENTS — APPLICATION MOBILE
        // ============================================================
        $qPaiementsMobile = Question::create([
            'title' => 'Quels moyens de paiement souhaitez-vous intégrer ?',
            'description' => null,
            'type' => 'multiselect',
            'project_types' => ['application_mobile'],
            'is_required' => true,
            'sort_order' => 76,
            'group_label' => 'Paiements',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qPaiementsMobile->id, 'label' => 'Carte bancaire (Visa, Mastercard)', 'value' => 'cb', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPaiementsMobile->id, 'label' => 'Mobile Money (Orange, MTN, Wave)', 'value' => 'mobile_money', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPaiementsMobile->id, 'label' => 'Apple Pay / Google Pay', 'value' => 'apple_google', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPaiementsMobile->id, 'label' => 'PayPal', 'value' => 'paypal', 'sort_order' => 4, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPaiementsMobile->id, 'label' => 'Autre', 'value' => 'autre', 'sort_order' => 5, 'is_active' => true]);

        QuestionCondition::create(['question_id' => $qPaiementsMobile->id, 'depends_on_question_id' => $qType->id, 'operator' => 'equals', 'option_value_id' => $optMobile->id, 'text_value' => null, 'sort_order' => 1]);

        // ============================================================
        // LANGUES
        // ============================================================
        $qLangues = Question::create([
            'title' => 'Dans quelle(s) langue(s) votre projet doit-il être disponible ?',
            'description' => null,
            'type' => 'select',
            'project_types' => ['site_vitrine', 'ecommerce', 'application_mobile', 'logiciel', 'autre'],
            'is_required' => true,
            'sort_order' => 80,
            'group_label' => 'Langues',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qLangues->id, 'label' => 'Français uniquement', 'value' => 'fr', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qLangues->id, 'label' => 'Anglais uniquement', 'value' => 'en', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qLangues->id, 'label' => 'Français et Anglais', 'value' => 'fr_en', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qLangues->id, 'label' => 'Plus de 2 langues', 'value' => 'plus_2', 'sort_order' => 4, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qLangues->id, 'label' => 'Autre', 'value' => 'autre', 'sort_order' => 5, 'is_active' => true]);

        // ============================================================
        // HÉBERGEMENT
        // ============================================================
        $qHebergement = Question::create([
            'title' => 'Avez-vous déjà un hébergement (nom de domaine, serveur) ?',
            'description' => 'L\'hébergement est l\'endroit où votre site ou application sera disponible en ligne.',
            'type' => 'yes_no',
            'project_types' => ['site_vitrine', 'ecommerce', 'application_mobile', 'logiciel', 'autre'],
            'is_required' => true,
            'sort_order' => 85,
            'group_label' => 'Hébergement',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        $qHebergementDetails = Question::create([
            'title' => 'Quel hébergement utilisez-vous ?',
            'description' => 'Précisez le nom de domaine et/ou l\'hébergeur.',
            'type' => 'text',
            'project_types' => ['site_vitrine', 'ecommerce', 'application_mobile', 'logiciel', 'autre'],
            'is_required' => true,
            'sort_order' => 86,
            'group_label' => 'Hébergement',
            'placeholder' => 'Ex : monsite.com, OVH, GoDaddy, AWS...',
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionCondition::create(['question_id' => $qHebergementDetails->id, 'depends_on_question_id' => $qHebergement->id, 'operator' => 'equals', 'option_value_id' => null, 'text_value' => 'yes', 'sort_order' => 1]);

        // ============================================================
        // DÉLAI
        // ============================================================
        $qDelai = Question::create([
            'title' => 'Quand souhaitez-vous que le projet soit terminé ?',
            'description' => null,
            'type' => 'select',
            'project_types' => ['site_vitrine', 'ecommerce', 'application_mobile', 'logiciel', 'autre'],
            'is_required' => true,
            'sort_order' => 90,
            'group_label' => 'Délai et budget',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qDelai->id, 'label' => 'Moins d\'1 mois', 'value' => 'moins_1_mois', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qDelai->id, 'label' => '1 à 3 mois', 'value' => '1_3_mois', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qDelai->id, 'label' => '3 à 6 mois', 'value' => '3_6_mois', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qDelai->id, 'label' => 'Plus de 6 mois', 'value' => 'plus_6_mois', 'sort_order' => 4, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qDelai->id, 'label' => 'Pas de délai particulier', 'value' => 'aucun', 'sort_order' => 5, 'is_active' => true]);

        // ============================================================
        // MAINTENANCE
        // ============================================================
        $qMaintenance = Question::create([
            'title' => 'Aurez-vous besoin d\'un suivi après la livraison ?',
            'description' => 'Mises à jour, corrections, évolutions du projet.',
            'type' => 'yes_no',
            'project_types' => ['site_vitrine', 'ecommerce', 'application_mobile', 'logiciel', 'autre'],
            'is_required' => true,
            'sort_order' => 95,
            'group_label' => 'Maintenance',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        // ============================================================
        // BUDGET
        // ============================================================
        $qBudget = Question::create([
            'title' => 'Quel est votre budget pour ce projet ?',
            'description' => null,
            'type' => 'select',
            'project_types' => ['site_vitrine', 'ecommerce', 'application_mobile', 'logiciel', 'autre'],
            'is_required' => true,
            'sort_order' => 100,
            'group_label' => 'Délai et budget',
            'placeholder' => null,
            'help_text' => 'Cela nous aide à vous proposer une solution adaptée.',
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qBudget->id, 'label' => 'Moins de 500 000 FCFA', 'value' => 'moins_500k', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qBudget->id, 'label' => '500 000 à 1 000 000 FCFA', 'value' => '500k_1m', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qBudget->id, 'label' => '1 000 000 à 3 000 000 FCFA', 'value' => '1m_3m', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qBudget->id, 'label' => 'Plus de 3 000 000 FCFA', 'value' => 'plus_3m', 'sort_order' => 4, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qBudget->id, 'label' => 'Je ne sais pas encore', 'value' => 'nsa', 'sort_order' => 5, 'is_active' => true]);

        // ============================================================
        // FORMATION
        // ============================================================
        $qFormation = Question::create([
            'title' => 'Aurez-vous besoin d\'une formation pour utiliser votre projet ?',
            'description' => 'Nous pouvons vous former à l\'utilisation de votre site, application ou logiciel.',
            'type' => 'yes_no',
            'project_types' => ['application_mobile', 'logiciel'],
            'is_required' => true,
            'sort_order' => 105,
            'group_label' => 'Formation',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        // ============================================================
        // REMARQUES
        // ============================================================
        Question::create([
            'title' => 'Avez-vous des remarques ou demandes particulières ?',
            'description' => 'Toute information supplémentaire qui pourrait nous être utile.',
            'type' => 'textarea',
            'project_types' => ['site_vitrine', 'ecommerce', 'application_mobile', 'logiciel', 'autre'],
            'is_required' => false,
            'sort_order' => 110,
            'group_label' => 'Remarques',
            'placeholder' => 'Ex : J\'ai vu un site que j\'aime bien, je peux vous l\'envoyer en référence...',
            'help_text' => null,
            'is_active' => true,
        ]);
    }
}
