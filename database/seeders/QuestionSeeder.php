<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionCondition;
use App\Models\QuestionOption;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        $allTypes = array_values(array_keys(config('project_types.types')));
        $allTypesExceptVitrine = array_values(array_diff($allTypes, ['site_vitrine']));
        $typesVitrineOnly = ['site_vitrine'];
        $typesEcommerceOnly = ['ecommerce'];
        $typesMobileOnly = ['application_mobile'];
        $typesLogicielOnly = ['logiciel'];
        $typesAutreOnly = ['autre'];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        QuestionCondition::query()->delete();
        QuestionOption::query()->delete();
        Question::withTrashed()->forceDelete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // ============================================================
        // Q1 : Description du projet (tous les types sauf site vitrine)
        // ============================================================
        Question::create([
            'title' => 'Décrivez votre projet en quelques mots',
            'description' => 'Expliquez simplement ce que vous voulez créer ou améliorer.',
            'type' => 'textarea',
            'project_types' => $allTypesExceptVitrine,
            'is_required' => true,
            'sort_order' => 1,
            'group_label' => 'Votre projet',
            'placeholder' => 'Ex : Je veux un site pour mon restaurant…',
            'help_text' => null,
            'is_active' => true,
        ]);

        // ============================================================
        // Q2 : Objectif principal (tous les types)
        // ============================================================
        $qObjectif = Question::create([
            'title' => 'Quel est votre objectif principal ?',
            'description' => null,
            'type' => 'select',
            'project_types' => $allTypesExceptVitrine,
            'is_required' => true,
            'sort_order' => 2,
            'group_label' => 'Votre projet',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qObjectif->id, 'label' => 'Augmenter mes ventes', 'value' => 'ventes', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qObjectif->id, 'label' => 'Renforcer ma visibilité', 'value' => 'visibilite', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qObjectif->id, 'label' => 'Automatiser mes processus', 'value' => 'automatiser', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qObjectif->id, 'label' => 'Améliorer la relation client', 'value' => 'relation_client', 'sort_order' => 4, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qObjectif->id, 'label' => 'Gérer mieux mon entreprise', 'value' => 'gestion', 'sort_order' => 5, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qObjectif->id, 'label' => 'Je ne sais pas encore', 'value' => 'nsa', 'sort_order' => 6, 'is_active' => true]);

        // ============================================================
        // Q3 : Fonctionnalités essentielles (tous les types)
        // ============================================================
        $qFonc = Question::create([
            'title' => 'Quelles fonctionnalités sont essentielles ?',
            'description' => null,
            'type' => 'multiselect',
            'project_types' => $allTypesExceptVitrine,
            'is_required' => true,
            'sort_order' => 3,
            'group_label' => 'Votre projet',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qFonc->id, 'label' => 'Inscription / Connexion utilisateur', 'value' => 'auth', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qFonc->id, 'label' => 'Paiement en ligne', 'value' => 'paiement', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qFonc->id, 'label' => 'Notifications (email, SMS, push)', 'value' => 'notifications', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qFonc->id, 'label' => 'Tableau de bord / Statistiques', 'value' => 'dashboard', 'sort_order' => 4, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qFonc->id, 'label' => 'Gestion des utilisateurs', 'value' => 'utilisateurs', 'sort_order' => 5, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qFonc->id, 'label' => 'Chat ou messagerie', 'value' => 'chat', 'sort_order' => 6, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qFonc->id, 'label' => 'Aucune de ces fonctionnalités', 'value' => 'aucune', 'sort_order' => 7, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qFonc->id, 'label' => 'Je ne sais pas encore', 'value' => 'nsa', 'sort_order' => 8, 'is_active' => true]);

        // ============================================================
        // SITE VITRINE — Q4 à Q7
        // ============================================================

        // Q4 : Nombre de pages
        $qNbPages = Question::create([
            'title' => 'Combien de pages souhaitez-vous ?',
            'description' => null,
            'type' => 'select',
            'project_types' => $typesVitrineOnly,
            'is_required' => true,
            'sort_order' => 4,
            'group_label' => 'Votre site vitrine',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qNbPages->id, 'label' => '1 page', 'value' => '1_page', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qNbPages->id, 'label' => '2 à 4 pages', 'value' => '2_4_pages', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qNbPages->id, 'label' => '5 pages ou plus', 'value' => '5_plus_pages', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qNbPages->id, 'label' => 'Je ne sais pas encore', 'value' => 'nsa', 'sort_order' => 4, 'is_active' => true]);

        // Q5 : Ce que le client veut présenter
        $qPresenter = Question::create([
            'title' => 'Que souhaitez-vous principalement présenter ?',
            'description' => null,
            'type' => 'multiselect',
            'project_types' => $typesVitrineOnly,
            'is_required' => true,
            'sort_order' => 5,
            'group_label' => 'Votre site vitrine',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qPresenter->id, 'label' => 'Votre entreprise', 'value' => 'entreprise', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPresenter->id, 'label' => 'Vos services', 'value' => 'services', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPresenter->id, 'label' => 'Vos produits', 'value' => 'produits', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPresenter->id, 'label' => 'Autre', 'value' => 'autre', 'sort_order' => 4, 'is_active' => true]);

        // Q6 : Actions des visiteurs
        $qActions = Question::create([
            'title' => 'Que souhaitez-vous permettre aux visiteurs de faire ?',
            'description' => null,
            'type' => 'multiselect',
            'project_types' => $typesVitrineOnly,
            'is_required' => true,
            'sort_order' => 6,
            'group_label' => 'Votre site vitrine',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qActions->id, 'label' => 'Vous contacter', 'value' => 'contacter', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qActions->id, 'label' => 'Vous appeler / WhatsApp', 'value' => 'appeler', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qActions->id, 'label' => 'Prendre rendez-vous', 'value' => 'rendez_vous', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qActions->id, 'label' => 'Autre', 'value' => 'autre', 'sort_order' => 4, 'is_active' => true]);

        // Q7 : Contenus à présenter
        $qContenus = Question::create([
            'title' => 'Quels contenus souhaitez-vous présenter ?',
            'description' => null,
            'type' => 'multiselect',
            'project_types' => $typesVitrineOnly,
            'is_required' => true,
            'sort_order' => 7,
            'group_label' => 'Votre site vitrine',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qContenus->id, 'label' => 'Photos', 'value' => 'photos', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qContenus->id, 'label' => 'Vidéos', 'value' => 'videos', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qContenus->id, 'label' => 'Textes / informations', 'value' => 'textes', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qContenus->id, 'label' => 'Autre', 'value' => 'autre', 'sort_order' => 4, 'is_active' => true]);

        // Q8 : Éléments déjà possédés
        $qDeja = Question::create([
            'title' => 'Quels éléments avez-vous déjà ?',
            'description' => null,
            'type' => 'multiselect',
            'project_types' => $typesVitrineOnly,
            'is_required' => true,
            'sort_order' => 8,
            'group_label' => 'Votre site vitrine',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qDeja->id, 'label' => 'Logo', 'value' => 'logo', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qDeja->id, 'label' => 'Photos / vidéos', 'value' => 'medias', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qDeja->id, 'label' => 'Textes', 'value' => 'textes', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qDeja->id, 'label' => 'Autre', 'value' => 'autre', 'sort_order' => 4, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qDeja->id, 'label' => 'Rien pour le moment', 'value' => 'rien', 'sort_order' => 5, 'is_active' => true]);

        // Q9 : Style
        $qStyle = Question::create([
            'title' => 'Quel style souhaitez-vous ?',
            'description' => null,
            'type' => 'select',
            'project_types' => $typesVitrineOnly,
            'is_required' => true,
            'sort_order' => 9,
            'group_label' => 'Votre site vitrine',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qStyle->id, 'label' => 'Simple et élégant', 'value' => 'simple', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qStyle->id, 'label' => 'Moderne et professionnel', 'value' => 'moderne', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qStyle->id, 'label' => 'Premium', 'value' => 'premium', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qStyle->id, 'label' => 'Je vous laisse proposer', 'value' => 'laisser', 'sort_order' => 4, 'is_active' => true]);

        // Q10 : Budget (dernière question)
        $qBudgetVitrine = Question::create([
            'title' => 'Quel budget avez-vous prévu pour ce projet ?',
            'description' => null,
            'type' => 'select',
            'project_types' => $typesVitrineOnly,
            'is_required' => true,
            'sort_order' => 10,
            'group_label' => 'Votre site vitrine',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qBudgetVitrine->id, 'label' => 'Moins de 250 000 FCFA', 'value' => 'moins_250k', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qBudgetVitrine->id, 'label' => '250 000 à 500 000 FCFA', 'value' => '250k_500k', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qBudgetVitrine->id, 'label' => '500 000 à 1 000 000 FCFA', 'value' => '500k_1m', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qBudgetVitrine->id, 'label' => 'Je ne sais pas encore', 'value' => 'nsa', 'sort_order' => 4, 'is_active' => true]);

        // ============================================================
        // E-COMMERCE — Q4 à Q7
        // ============================================================

        // Q4 : Nombre de produits
        $qNbProduits = Question::create([
            'title' => 'Combien de produits comptez-vous vendre ?',
            'description' => null,
            'type' => 'select',
            'project_types' => $typesEcommerceOnly,
            'is_required' => true,
            'sort_order' => 4,
            'group_label' => 'Votre boutique en ligne',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qNbProduits->id, 'label' => 'Moins de 50 produits', 'value' => 'moins_50', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qNbProduits->id, 'label' => '50 à 200 produits', 'value' => '50_200', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qNbProduits->id, 'label' => 'Plus de 200 produits', 'value' => 'plus_200', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qNbProduits->id, 'label' => 'Je ne sais pas encore', 'value' => 'nsa', 'sort_order' => 4, 'is_active' => true]);

        // Q5 : Type de produits
        $qTypeProduits = Question::create([
            'title' => 'Quel type de produits vendez-vous ?',
            'description' => null,
            'type' => 'select',
            'project_types' => $typesEcommerceOnly,
            'is_required' => true,
            'sort_order' => 5,
            'group_label' => 'Votre boutique en ligne',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qTypeProduits->id, 'label' => 'Produits physiques (vêtements, accessoires…)', 'value' => 'physiques', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qTypeProduits->id, 'label' => 'Produits numériques (e-books, formations…)', 'value' => 'numeriques', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qTypeProduits->id, 'label' => 'Services (consulting, abonnements…)', 'value' => 'services', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qTypeProduits->id, 'label' => 'Je ne sais pas encore', 'value' => 'nsa', 'sort_order' => 4, 'is_active' => true]);

        // Q6 : Livraison
        $qLivraison = Question::create([
            'title' => 'Comment souhaitez-vous gérer la livraison ?',
            'description' => null,
            'type' => 'select',
            'project_types' => $typesEcommerceOnly,
            'is_required' => true,
            'sort_order' => 6,
            'group_label' => 'Votre boutique en ligne',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qLivraison->id, 'label' => 'Livraison à domicile', 'value' => 'domicile', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qLivraison->id, 'label' => 'Retrait en point relais', 'value' => 'relais', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qLivraison->id, 'label' => 'Les deux', 'value' => 'les_deux', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qLivraison->id, 'label' => 'Pas de livraison physique', 'value' => 'numerique', 'sort_order' => 4, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qLivraison->id, 'label' => 'Je ne sais pas encore', 'value' => 'nsa', 'sort_order' => 5, 'is_active' => true]);

        // Q7 : Budget
        $qBudgetEcom = Question::create([
            'title' => 'Quel est votre budget ?',
            'description' => null,
            'type' => 'select',
            'project_types' => $typesEcommerceOnly,
            'is_required' => true,
            'sort_order' => 7,
            'group_label' => 'Votre boutique en ligne',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qBudgetEcom->id, 'label' => 'Moins de 500 000 FCFA', 'value' => 'moins_500k', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qBudgetEcom->id, 'label' => '500 000 à 1 000 000 FCFA', 'value' => '500k_1m', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qBudgetEcom->id, 'label' => '1 000 000 à 3 000 000 FCFA', 'value' => '1m_3m', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qBudgetEcom->id, 'label' => 'Plus de 3 000 000 FCFA', 'value' => 'plus_3m', 'sort_order' => 4, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qBudgetEcom->id, 'label' => 'Je ne sais pas encore', 'value' => 'nsa', 'sort_order' => 5, 'is_active' => true]);

        // ============================================================
        // APPLICATION MOBILE — Q4 à Q7
        // ============================================================

        // Q4 : Plateformes
        $qPlateforme = Question::create([
            'title' => 'Sur quelles plateformes ?',
            'description' => null,
            'type' => 'select',
            'project_types' => $typesMobileOnly,
            'is_required' => true,
            'sort_order' => 4,
            'group_label' => 'Votre application mobile',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qPlateforme->id, 'label' => 'iPhone (iOS)', 'value' => 'ios', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPlateforme->id, 'label' => 'Android', 'value' => 'android', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPlateforme->id, 'label' => 'Les deux', 'value' => 'les_deux', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPlateforme->id, 'label' => 'Je ne sais pas encore', 'value' => 'nsa', 'sort_order' => 4, 'is_active' => true]);

        // Q5 : Paiements
        $qPaiementMobile = Question::create([
            'title' => 'Quels moyens de paiement ?',
            'description' => null,
            'type' => 'multiselect',
            'project_types' => $typesMobileOnly,
            'is_required' => true,
            'sort_order' => 5,
            'group_label' => 'Votre application mobile',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qPaiementMobile->id, 'label' => 'Carte bancaire', 'value' => 'cb', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPaiementMobile->id, 'label' => 'Mobile Money', 'value' => 'mobile_money', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPaiementMobile->id, 'label' => 'Apple Pay / Google Pay', 'value' => 'apple_google', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPaiementMobile->id, 'label' => 'Pas de paiement dans l\'app', 'value' => 'aucun', 'sort_order' => 4, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPaiementMobile->id, 'label' => 'Je ne sais pas encore', 'value' => 'nsa', 'sort_order' => 5, 'is_active' => true]);

        // Q6 : Utilisateurs
        $qUtilisateursMobile = Question::create([
            'title' => 'Qui utilisera votre application ?',
            'description' => null,
            'type' => 'multiselect',
            'project_types' => $typesMobileOnly,
            'is_required' => true,
            'sort_order' => 6,
            'group_label' => 'Votre application mobile',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qUtilisateursMobile->id, 'label' => 'Mes clients', 'value' => 'clients', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qUtilisateursMobile->id, 'label' => 'Mes employés', 'value' => 'employes', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qUtilisateursMobile->id, 'label' => 'Le grand public', 'value' => 'grand_public', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qUtilisateursMobile->id, 'label' => 'Je ne sais pas encore', 'value' => 'nsa', 'sort_order' => 4, 'is_active' => true]);

        // Q7 : Budget
        $qBudgetMobile = Question::create([
            'title' => 'Quel est votre budget ?',
            'description' => null,
            'type' => 'select',
            'project_types' => $typesMobileOnly,
            'is_required' => true,
            'sort_order' => 7,
            'group_label' => 'Votre application mobile',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qBudgetMobile->id, 'label' => 'Moins de 1 000 000 FCFA', 'value' => 'moins_1m', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qBudgetMobile->id, 'label' => '1 000 000 à 3 000 000 FCFA', 'value' => '1m_3m', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qBudgetMobile->id, 'label' => '3 000 000 à 5 000 000 FCFA', 'value' => '3m_5m', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qBudgetMobile->id, 'label' => 'Plus de 5 000 000 FCFA', 'value' => 'plus_5m', 'sort_order' => 4, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qBudgetMobile->id, 'label' => 'Je ne sais pas encore', 'value' => 'nsa', 'sort_order' => 5, 'is_active' => true]);

        // ============================================================
        // LOGICIEL / ERP / CRM — Q4 à Q7
        // ============================================================

        // Q4 : Processus
        $qProcessus = Question::create([
            'title' => 'Quels processus devez-vous gérer ?',
            'description' => null,
            'type' => 'multiselect',
            'project_types' => $typesLogicielOnly,
            'is_required' => true,
            'sort_order' => 4,
            'group_label' => 'Votre logiciel',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qProcessus->id, 'label' => 'Gestion des ventes', 'value' => 'ventes', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qProcessus->id, 'label' => 'Gestion des clients', 'value' => 'clients', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qProcessus->id, 'label' => 'Gestion des stocks', 'value' => 'stocks', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qProcessus->id, 'label' => 'Facturation', 'value' => 'facturation', 'sort_order' => 4, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qProcessus->id, 'label' => 'Comptabilité', 'value' => 'comptabilite', 'sort_order' => 5, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qProcessus->id, 'label' => 'Je ne sais pas encore', 'value' => 'nsa', 'sort_order' => 6, 'is_active' => true]);

        // Q5 : Nombre d'utilisateurs
        $qNbUsers = Question::create([
            'title' => 'Combien de personnes l\'utiliseront ?',
            'description' => null,
            'type' => 'select',
            'project_types' => $typesLogicielOnly,
            'is_required' => true,
            'sort_order' => 5,
            'group_label' => 'Votre logiciel',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qNbUsers->id, 'label' => '1 à 5 personnes', 'value' => '1_5', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qNbUsers->id, 'label' => '6 à 20 personnes', 'value' => '6_20', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qNbUsers->id, 'label' => 'Plus de 20 personnes', 'value' => 'plus_20', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qNbUsers->id, 'label' => 'Je ne sais pas encore', 'value' => 'nsa', 'sort_order' => 4, 'is_active' => true]);

        // Q6 : Intégrations
        Question::create([
            'title' => 'Avez-vous besoin de connecter d\'autres outils ?',
            'description' => 'Ex : logiciel de comptabilité, CRM, outil de paie…',
            'type' => 'yes_no',
            'project_types' => $typesLogicielOnly,
            'is_required' => true,
            'sort_order' => 6,
            'group_label' => 'Votre logiciel',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        // Q7 : Budget
        $qBudgetLogiciel = Question::create([
            'title' => 'Quel est votre budget ?',
            'description' => null,
            'type' => 'select',
            'project_types' => $typesLogicielOnly,
            'is_required' => true,
            'sort_order' => 7,
            'group_label' => 'Votre logiciel',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qBudgetLogiciel->id, 'label' => 'Moins de 1 000 000 FCFA', 'value' => 'moins_1m', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qBudgetLogiciel->id, 'label' => '1 000 000 à 3 000 000 FCFA', 'value' => '1m_3m', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qBudgetLogiciel->id, 'label' => '3 000 000 à 5 000 000 FCFA', 'value' => '3m_5m', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qBudgetLogiciel->id, 'label' => 'Plus de 5 000 000 FCFA', 'value' => 'plus_5m', 'sort_order' => 4, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qBudgetLogiciel->id, 'label' => 'Je ne sais pas encore', 'value' => 'nsa', 'sort_order' => 5, 'is_active' => true]);

        // ============================================================
        // AUTRE — Q4 à Q7
        // ============================================================

        // Q4 : Description détaillée
        Question::create([
            'title' => 'Décrivez votre projet en détail',
            'description' => 'Plus vous donnez de détails, mieux nous pourrons vous aider.',
            'type' => 'textarea',
            'project_types' => $typesAutreOnly,
            'is_required' => false,
            'sort_order' => 4,
            'group_label' => 'Votre projet sur mesure',
            'placeholder' => 'Décrivez votre idée, vos besoins, vos attentes…',
            'help_text' => null,
            'is_active' => true,
        ]);

        // Q5 : Type de solution
        $qTypeSolution = Question::create([
            'title' => 'Quel type de solution envisagez-vous ?',
            'description' => null,
            'type' => 'select',
            'project_types' => $typesAutreOnly,
            'is_required' => true,
            'sort_order' => 5,
            'group_label' => 'Votre projet sur mesure',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qTypeSolution->id, 'label' => 'Site web', 'value' => 'site', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qTypeSolution->id, 'label' => 'Application mobile', 'value' => 'app', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qTypeSolution->id, 'label' => 'Logiciel / Outil', 'value' => 'logiciel', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qTypeSolution->id, 'label' => 'Autre', 'value' => 'autre', 'sort_order' => 4, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qTypeSolution->id, 'label' => 'Je ne sais pas encore', 'value' => 'nsa', 'sort_order' => 5, 'is_active' => true]);

        // Q6 : Délai
        $qDelaiAutre = Question::create([
            'title' => 'Quand souhaitez-vous que ce soit prêt ?',
            'description' => null,
            'type' => 'select',
            'project_types' => $typesAutreOnly,
            'is_required' => true,
            'sort_order' => 6,
            'group_label' => 'Votre projet sur mesure',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qDelaiAutre->id, 'label' => 'Moins d\'1 mois', 'value' => 'moins_1_mois', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qDelaiAutre->id, 'label' => '1 à 3 mois', 'value' => '1_3_mois', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qDelaiAutre->id, 'label' => '3 à 6 mois', 'value' => '3_6_mois', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qDelaiAutre->id, 'label' => 'Pas de délai particulier', 'value' => 'aucun', 'sort_order' => 4, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qDelaiAutre->id, 'label' => 'Je ne sais pas encore', 'value' => 'nsa', 'sort_order' => 5, 'is_active' => true]);

        // Q7 : Budget
        $qBudgetAutre = Question::create([
            'title' => 'Quel est votre budget ?',
            'description' => null,
            'type' => 'select',
            'project_types' => $typesAutreOnly,
            'is_required' => true,
            'sort_order' => 7,
            'group_label' => 'Votre projet sur mesure',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qBudgetAutre->id, 'label' => 'Moins de 500 000 FCFA', 'value' => 'moins_500k', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qBudgetAutre->id, 'label' => '500 000 à 1 000 000 FCFA', 'value' => '500k_1m', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qBudgetAutre->id, 'label' => '1 000 000 à 3 000 000 FCFA', 'value' => '1m_3m', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qBudgetAutre->id, 'label' => 'Plus de 3 000 000 FCFA', 'value' => 'plus_3m', 'sort_order' => 4, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qBudgetAutre->id, 'label' => 'Je ne sais pas encore', 'value' => 'nsa', 'sort_order' => 5, 'is_active' => true]);
    }
}
