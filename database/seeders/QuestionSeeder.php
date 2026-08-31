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
        $allTypesExceptVitrineAndEcom = array_values(array_diff($allTypes, ['site_vitrine', 'ecommerce']));
        $allTypesExceptVitrineEcomMobile = array_values(array_diff($allTypes, ['site_vitrine', 'ecommerce', 'application_mobile']));
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
        // Q1 : Description du projet (tous les types sauf site vitrine, ecommerce et application mobile)
        // ============================================================
        Question::create([
            'title' => 'Décrivez votre projet en quelques mots',
            'description' => 'Expliquez simplement ce que vous voulez créer ou améliorer.',
            'type' => 'textarea',
            'project_types' => $allTypesExceptVitrineEcomMobile,
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
            'project_types' => $allTypesExceptVitrineEcomMobile,
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
            'project_types' => $allTypesExceptVitrineEcomMobile,
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
        // E-COMMERCE — Q4 à Q10
        // ============================================================

        // Q4 : Que souhaitez-vous vendre ?
        $qVendre = Question::create([
            'title' => 'Que souhaitez-vous vendre sur votre boutique ?',
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

        QuestionOption::create(['question_id' => $qVendre->id, 'label' => 'Produits physiques (vêtements, cosmétiques, alimentation...)', 'value' => 'physiques', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qVendre->id, 'label' => 'Services (formation, consultation, réservation...)', 'value' => 'services', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qVendre->id, 'label' => 'Produits numériques (ebooks, logiciels, formations...)', 'value' => 'numeriques', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qVendre->id, 'label' => 'Autre', 'value' => 'autre', 'sort_order' => 4, 'is_active' => true]);

        // Q5 : Nombre de produits
        $qNbProduits = Question::create([
            'title' => 'Combien de produits souhaitez-vous proposer ?',
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

        QuestionOption::create(['question_id' => $qNbProduits->id, 'label' => 'Moins de 20 produits', 'value' => 'moins_20', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qNbProduits->id, 'label' => '20 à 100 produits', 'value' => '20_100', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qNbProduits->id, 'label' => 'Plus de 100 produits', 'value' => 'plus_100', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qNbProduits->id, 'label' => 'Je ne sais pas encore', 'value' => 'nsa', 'sort_order' => 4, 'is_active' => true]);

        // Q6 : Moyens de paiement
        $qPaiement = Question::create([
            'title' => 'Comment souhaitez-vous que vos clients puissent payer ?',
            'description' => null,
            'type' => 'multiselect',
            'project_types' => $typesEcommerceOnly,
            'is_required' => true,
            'sort_order' => 6,
            'group_label' => 'Votre boutique en ligne',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qPaiement->id, 'label' => 'Mobile Money (Wave, Orange Money, MTN...)', 'value' => 'mobile_money', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPaiement->id, 'label' => 'Carte bancaire (Visa, Mastercard...)', 'value' => 'carte', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPaiement->id, 'label' => 'Paiement à la livraison (paiement à la réception...)', 'value' => 'livraison', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPaiement->id, 'label' => 'Autre', 'value' => 'autre', 'sort_order' => 4, 'is_active' => true]);

        // Q7 : Gestion quotidienne
        $qGestion = Question::create([
            'title' => 'Qui devra gérer la boutique au quotidien ?',
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

        QuestionOption::create(['question_id' => $qGestion->id, 'label' => 'Moi uniquement', 'value' => 'moi', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qGestion->id, 'label' => 'Mon équipe (2 à 5 personnes)', 'value' => 'equipe', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qGestion->id, 'label' => 'Plusieurs personnes (équipe plus large)', 'value' => 'plusieurs', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qGestion->id, 'label' => 'Je ne sais pas encore', 'value' => 'nsa', 'sort_order' => 4, 'is_active' => true]);

        // Q8 : Suivi des stocks
        $qStocks = Question::create([
            'title' => 'Souhaitez-vous suivre vos stocks ?',
            'description' => null,
            'type' => 'select',
            'project_types' => $typesEcommerceOnly,
            'is_required' => true,
            'sort_order' => 8,
            'group_label' => 'Votre boutique en ligne',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qStocks->id, 'label' => 'Oui, automatiquement (stocks mis à jour après chaque vente)', 'value' => 'auto', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qStocks->id, 'label' => 'Oui, simplement (suivi manuel)', 'value' => 'manuel', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qStocks->id, 'label' => 'Non', 'value' => 'non', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qStocks->id, 'label' => 'Je ne sais pas encore', 'value' => 'nsa', 'sort_order' => 4, 'is_active' => true]);

        // Q9 : Réception des commandes
        $qCommandes = Question::create([
            'title' => 'Comment souhaitez-vous recevoir et gérer les commandes ?',
            'description' => null,
            'type' => 'multiselect',
            'project_types' => $typesEcommerceOnly,
            'is_required' => true,
            'sort_order' => 9,
            'group_label' => 'Votre boutique en ligne',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qCommandes->id, 'label' => 'Depuis la boutique (commandes directement dans la plateforme)', 'value' => 'plateforme', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qCommandes->id, 'label' => 'Par WhatsApp (commande reçue sur WhatsApp)', 'value' => 'whatsapp', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qCommandes->id, 'label' => 'Par téléphone (commande par appel)', 'value' => 'telephone', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qCommandes->id, 'label' => 'Autre', 'value' => 'autre', 'sort_order' => 4, 'is_active' => true]);

        // Q10 : Budget (dernière question)
        $qBudgetEcom = Question::create([
            'title' => 'Quel budget avez-vous prévu pour ce projet ?',
            'description' => null,
            'type' => 'select',
            'project_types' => $typesEcommerceOnly,
            'is_required' => true,
            'sort_order' => 10,
            'group_label' => 'Votre boutique en ligne',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qBudgetEcom->id, 'label' => 'Moins de 500 000 FCFA', 'value' => 'moins_500k', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qBudgetEcom->id, 'label' => '500 000 à 1 000 000 FCFA', 'value' => '500k_1m', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qBudgetEcom->id, 'label' => '1 000 000 à 3 000 000 FCFA', 'value' => '1m_3m', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qBudgetEcom->id, 'label' => 'Plus de 3 000 000 FCFA', 'value' => 'plus_3m', 'sort_order' => 4, 'is_active' => true]);

        // ============================================================
        // APPLICATION MOBILE — Q4 à Q10
        // ============================================================

        // Q4 : Type d'application
        $qTypeApp = Question::create([
            'title' => 'Quel type d\'application souhaitez-vous créer ?',
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

        QuestionOption::create(['question_id' => $qTypeApp->id, 'label' => 'Application de services (réservation, livraison, rendez-vous...)', 'value' => 'services', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qTypeApp->id, 'label' => 'Application commerciale (vente, commandes, marketplace...)', 'value' => 'commerciale', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qTypeApp->id, 'label' => 'Application communautaire (réseau social, messagerie, communauté...)', 'value' => 'communautaire', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qTypeApp->id, 'label' => 'Autre', 'value' => 'autre', 'sort_order' => 4, 'is_active' => true]);

        // Q5 : Public cible
        $qPublic = Question::create([
            'title' => 'À qui s\'adresse votre application ?',
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

        QuestionOption::create(['question_id' => $qPublic->id, 'label' => 'Particuliers (clients, consommateurs...)', 'value' => 'particuliers', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPublic->id, 'label' => 'Entreprises (professionnels, sociétés...)', 'value' => 'entreprises', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPublic->id, 'label' => 'Membres d\'une communauté (élèves, adhérents, employés...)', 'value' => 'communaute', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPublic->id, 'label' => 'Autre', 'value' => 'autre', 'sort_order' => 4, 'is_active' => true]);

        // Q6 : Appareils
        $qAppareils = Question::create([
            'title' => 'Sur quels appareils souhaitez-vous utiliser votre application ?',
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

        QuestionOption::create(['question_id' => $qAppareils->id, 'label' => 'Android (Samsung, Tecno, Xiaomi...)', 'value' => 'android', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qAppareils->id, 'label' => 'iPhone / iPad (Apple)', 'value' => 'ios', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qAppareils->id, 'label' => 'Android + iPhone', 'value' => 'les_deux', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qAppareils->id, 'label' => 'Autre', 'value' => 'autre', 'sort_order' => 4, 'is_active' => true]);

        // Q7 : Connexion Internet
        $qInternet = Question::create([
            'title' => 'L\'application doit-elle fonctionner avec Internet ?',
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

        QuestionOption::create(['question_id' => $qInternet->id, 'label' => 'Oui, uniquement en ligne', 'value' => 'en_ligne', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qInternet->id, 'label' => 'Oui, avec certaines fonctions hors ligne', 'value' => 'hors_ligne_partiel', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qInternet->id, 'label' => 'Non, principalement hors ligne', 'value' => 'hors_ligne', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qInternet->id, 'label' => 'Je ne sais pas encore', 'value' => 'nsa', 'sort_order' => 4, 'is_active' => true]);

        // Q8 : Types d'utilisateurs
        $qUtilisateurs = Question::create([
            'title' => 'Quels types d\'utilisateurs devront utiliser l\'application ?',
            'description' => null,
            'type' => 'multiselect',
            'project_types' => $typesMobileOnly,
            'is_required' => true,
            'sort_order' => 8,
            'group_label' => 'Votre application mobile',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qUtilisateurs->id, 'label' => 'Un seul type d\'utilisateur (ex. clients)', 'value' => 'un_seul', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qUtilisateurs->id, 'label' => 'Clients + administrateur (gestion de l\'application)', 'value' => 'client_admin', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qUtilisateurs->id, 'label' => 'Plusieurs types d\'utilisateurs (ex. client, vendeur, livreur...)', 'value' => 'plusieurs', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qUtilisateurs->id, 'label' => 'Autre', 'value' => 'autre', 'sort_order' => 4, 'is_active' => true]);

        // Q9 : Paiements
        $qPaiementMobile = Question::create([
            'title' => 'Votre application devra-t-elle permettre des paiements ?',
            'description' => null,
            'type' => 'multiselect',
            'project_types' => $typesMobileOnly,
            'is_required' => true,
            'sort_order' => 9,
            'group_label' => 'Votre application mobile',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qPaiementMobile->id, 'label' => 'Mobile Money (Wave, Orange Money, MTN...)', 'value' => 'mobile_money', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPaiementMobile->id, 'label' => 'Carte bancaire (Visa, Mastercard...)', 'value' => 'carte', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPaiementMobile->id, 'label' => 'Paiement à la livraison (paiement à la réception)', 'value' => 'livraison', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qPaiementMobile->id, 'label' => 'Aucun paiement', 'value' => 'aucun', 'sort_order' => 4, 'is_active' => true]);

        // Q10 : Budget (dernière question)
        $qBudgetMobile = Question::create([
            'title' => 'Quel budget avez-vous prévu pour ce projet ?',
            'description' => null,
            'type' => 'select',
            'project_types' => $typesMobileOnly,
            'is_required' => true,
            'sort_order' => 10,
            'group_label' => 'Votre application mobile',
            'placeholder' => null,
            'help_text' => null,
            'is_active' => true,
        ]);

        QuestionOption::create(['question_id' => $qBudgetMobile->id, 'label' => 'Moins de 1 000 000 FCFA', 'value' => 'moins_1m', 'sort_order' => 1, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qBudgetMobile->id, 'label' => '1 000 000 à 2 000 000 FCFA', 'value' => '1m_2m', 'sort_order' => 2, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qBudgetMobile->id, 'label' => '2 000 000 à 5 000 000 FCFA', 'value' => '2m_5m', 'sort_order' => 3, 'is_active' => true]);
        QuestionOption::create(['question_id' => $qBudgetMobile->id, 'label' => 'Plus de 5 000 000 FCFA', 'value' => 'plus_5m', 'sort_order' => 4, 'is_active' => true]);

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
