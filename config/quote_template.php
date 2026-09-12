<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Modèle de devis par défaut (devis.html DOMINIUM)
    |--------------------------------------------------------------------------
    |
    | Contenu initial copié dans chaque nouveau devis afin qu'il puisse
    | évoluer indépendamment (textes, blocs, listes et tableaux modulables).
    | La section de type « budget » affiche les lignes financières du devis ;
    | elle est créée vide et reste positionnable comme n'importe quel bloc.
    |
    */

    'subject' => 'Gestion de la communication digitale',

    'description' => 'Gestion de la communication digitale (Facebook, TikTok, WhatsApp) pour la marque Sante Naturelle et Vie, dans le cadre de la promotion de sa gamme de produits naturels : Tisane Spéciale Anti-Palu, Santé des Femmes, et solution de nettoyage du côlon (Colopathie).',

    'payment_terms' => 'Règlement intégral effectué comptant à la signature du présent devis, qui vaut donc facture pour le client. Dominium Tech remercie le client pour sa confiance et s\'engage à démarrer la gestion des pages dans les meilleurs délais.',

    'notes' => 'Note : le taux de commission Dominium Tech appliqué est de 20 % du budget pour tout montant à partir de 100 000 FCFA, ou de 20 000 FCFA forfaitaires en dessous de ce seuil.',

    'prestations' => [
        'Création et/ou optimisation des pages Facebook, TikTok et WhatsApp Business, avec conception de visuels et de contenus adaptés à chaque produit (Anti-Palu, Santé des Femmes, Nettoyage du Côlon), publication régulière et animation sur la durée convenue',
        'Mise en avant des numéros de commande et réponse aux messages clients (WhatsApp), suivi des performances (portée, interactions, demandes générées) et diffusion ciblée grâce au budget promotion alloué',
    ],

    'items' => [
        [
            'designation' => 'Frais de gestion Dominium Tech',
            'description' => 'Stratégie de contenu, création de visuels, animation des pages Facebook / TikTok / WhatsApp, community management',
            'quantity' => 1,
            'unit_price' => 20000,
        ],
        [
            'designation' => 'Budget promotion / diffusion',
            'description' => 'Boost de publications, campagnes ciblées et actions de visibilité sur les réseaux sociaux',
            'quantity' => 1,
            'unit_price' => 30000,
        ],
    ],

    'sections' => [
        [
            'type' => 'budget',
            'title' => 'Répartition du budget',
            'content' => null,
        ],
        [
            'type' => 'list',
            'title' => 'Prestations incluses',
            'content' => [
                'items' => [
                    'Création et/ou optimisation des pages Facebook, TikTok et WhatsApp Business',
                    'Conception de visuels et de contenus adaptés à chaque produit',
                    'Publication régulière et animation des pages sur la durée convenue',
                    'Mise en avant des numéros de commande et réponse aux messages clients (WhatsApp)',
                    'Suivi des performances (portée, interactions, demandes générées)',
                    'Diffusion ciblée grâce au budget promotion alloué',
                ],
            ],
        ],
        [
            'type' => 'text',
            'title' => 'Analyse des produits & cible recommandée',
            'content' => [
                'text' => 'Les trois produits de la marque Santé Naturelle et Vie s\'adressent à des besoins de santé courants et récurrents, ce qui en fait de bons candidats pour une communication ciblée sur les réseaux sociaux les plus utilisés en Côte d\'Ivoire.',
            ],
        ],
        [
            'type' => 'product_table',
            'title' => 'Produits & cibles',
            'content' => [
                'headers' => ['Produit', 'Cible prioritaire', 'Canaux conseillés'],
                'rows' => [
                    ['Tisane Spéciale Anti-Palu (3 000 F)', 'Grand public, familles avec enfants, personnes sujettes au paludisme et à la fièvre typhoïde ; adultes 25-55 ans en zones urbaines et périurbaines', 'Facebook (groupes santé/famille), WhatsApp (commande directe)'],
                    ['Santé des Femmes (3 000 F)', 'Femmes de 22 à 45 ans : troubles menstruels, désir de conception, fibromes/kystes, préménopause', 'TikTok & Facebook (communautés féminines), WhatsApp'],
                    ['Nettoyage du Côlon (5 000 F)', 'Adultes 25-50 ans soucieux de leur digestion, ballonnements, confort intestinal ; public « bien-être / détox »', 'Facebook & TikTok (contenu bien-être), WhatsApp'],
                ],
            ],
        ],
        [
            'type' => 'text',
            'title' => 'Atouts à valoriser',
            'content' => [
                'text' => 'L\'origine « Produit du Niger » et l\'argument « 100% naturel, sans effets secondaires » sont des atouts à valoriser dans les contenus : ils rassurent une clientèle qui recherche des alternatives naturelles et locales aux traitements classiques.',
            ],
        ],
    ],

];
