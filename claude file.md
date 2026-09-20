<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>DOMINIUM TECH — Devis automatique</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600&display=swap" rel="stylesheet">

<style>
:root{
  --paper:#ffffff;
  --field:#eef1f4;
  --rule:#d8dde3;
  --ink:#14202e;
  --ink-soft:#5c6874;
  --cachet:#a4252b;
  --valid:#1e6b45;
  --sans:'Archivo',system-ui,-apple-system,sans-serif;
  --serif:'Source Serif 4',Georgia,serif;
}
*{box-sizing:border-box}
html,body{margin:0;padding:0}
body{
  font-family:var(--sans);
  background:var(--field);
  color:var(--ink);
  font-size:16px;
  line-height:1.5;
  -webkit-text-size-adjust:100%;
}
.wrap{max-width:680px;margin:0 auto;padding:0 16px 64px}

/* ---------- barre ---------- */
.topbar{
  background:var(--ink);color:#fff;
  position:sticky;top:0;z-index:20;
}
.topbar .inner{
  max-width:680px;margin:0 auto;padding:12px 16px;
  display:flex;align-items:baseline;justify-content:space-between;gap:12px;
}
.mark{font-weight:700;letter-spacing:.14em;font-size:13px}
.mark span{font-weight:400;opacity:.62}
.switch{
  background:none;border:1px solid rgba(255,255,255,.3);color:#fff;
  font-family:var(--sans);font-size:12px;padding:5px 11px;border-radius:2px;cursor:pointer;
}
.switch:hover{background:rgba(255,255,255,.1)}
.switch:focus-visible{outline:2px solid #fff;outline-offset:2px}

/* ---------- progression ---------- */
.progress{height:3px;background:var(--rule)}
.progress i{display:block;height:100%;background:var(--cachet);width:0;transition:width .25s ease}

/* ---------- feuille ---------- */
.sheet{
  background:var(--paper);
  border:1px solid var(--rule);
  padding:28px 22px;
  margin-top:20px;
}
h1{
  font-family:var(--serif);font-weight:600;
  font-size:29px;line-height:1.18;margin:0 0 10px;letter-spacing:-.01em;
}
h2{font-family:var(--serif);font-weight:600;font-size:21px;margin:0 0 6px}
.lede{color:var(--ink-soft);font-size:15px;margin:0 0 26px;max-width:52ch}
.step-no{
  font-size:12px;color:var(--ink-soft);
  border-bottom:1px solid var(--rule);padding-bottom:7px;margin-bottom:18px;
  display:flex;justify-content:space-between;
}

/* ---------- champs ---------- */
label.f{display:block;margin:0 0 16px}
label.f > b{display:block;font-weight:600;font-size:14px;margin-bottom:5px}
label.f > em{display:block;font-style:normal;color:var(--ink-soft);font-size:13px;margin-bottom:6px}
input[type=text],input[type=email],input[type=tel],input[type=number],select,textarea{
  width:100%;font-family:var(--sans);font-size:16px;color:var(--ink);
  background:var(--paper);border:1px solid var(--rule);border-radius:2px;
  padding:11px 12px;
}
input:focus,select:focus,textarea:focus{outline:2px solid var(--ink);outline-offset:-1px;border-color:var(--ink)}
.two{display:grid;grid-template-columns:1fr 1fr;gap:0 14px}
@media(max-width:520px){.two{grid-template-columns:1fr}}

/* ---------- choix ---------- */
.choices{display:grid;gap:8px;margin:0 0 20px}
.choice{
  display:flex;gap:11px;align-items:flex-start;
  border:1px solid var(--rule);border-radius:2px;padding:12px 13px;cursor:pointer;background:var(--paper);
}
.choice:hover{border-color:var(--ink-soft)}
.choice input{margin:3px 0 0;accent-color:var(--cachet);flex:none;width:17px;height:17px}
.choice.on{border-color:var(--ink);box-shadow:inset 3px 0 0 var(--cachet)}
.choice b{display:block;font-weight:600;font-size:15px}
.choice small{display:block;color:var(--ink-soft);font-size:13px;line-height:1.4}

/* ---------- boutons ---------- */
.actions{display:flex;gap:10px;margin-top:26px;flex-wrap:wrap}
button.pri,button.sec,button.gho{
  font-family:var(--sans);font-size:15px;font-weight:600;
  padding:13px 22px;border-radius:2px;cursor:pointer;border:1px solid var(--ink);
}
button.pri{background:var(--ink);color:#fff;flex:1;min-width:150px}
button.pri:hover{background:#27374a}
button.sec{background:var(--paper);color:var(--ink)}
button.sec:hover{background:var(--field)}
button.gho{background:none;border-color:var(--rule);color:var(--ink-soft);font-weight:400;font-size:14px;padding:9px 14px}
button:focus-visible{outline:2px solid var(--cachet);outline-offset:2px}
button[disabled]{opacity:.4;cursor:not-allowed}

.err{color:var(--cachet);font-size:14px;margin-top:12px}
.hide{display:none !important}

/* ---------- document devis ---------- */
.doc{font-family:var(--serif);font-size:15px;line-height:1.55}
.doc-head{border-bottom:2px solid var(--ink);padding-bottom:14px;margin-bottom:16px}
.doc-head .no{font-family:var(--sans);font-size:12px;letter-spacing:.1em;color:var(--cachet);font-weight:600}
.doc-head h2{font-size:25px;margin:4px 0 2px}
.parties{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:20px;font-size:14px}
@media(max-width:520px){.parties{grid-template-columns:1fr;gap:14px}}
.parties h3{font-family:var(--sans);font-size:11px;letter-spacing:.1em;color:var(--ink-soft);margin:0 0 5px;font-weight:600}
.parties p{margin:0;line-height:1.45}

table.lines{width:100%;border-collapse:collapse;font-size:13.5px;margin-bottom:6px}
table.lines th{
  font-family:var(--sans);font-size:11px;letter-spacing:.05em;color:var(--ink-soft);
  text-align:left;border-bottom:1px solid var(--ink);padding:6px 6px 6px 0;font-weight:600;
}
table.lines td{border-bottom:1px solid var(--rule);padding:9px 6px 9px 0;vertical-align:top}
table.lines td.n,table.lines th.n{text-align:right;white-space:nowrap;padding-right:0}
table.lines .ref{font-family:var(--sans);font-size:11px;color:var(--ink-soft)}
table.lines .inc{display:block;color:var(--ink-soft);font-size:12.5px;line-height:1.35;margin-top:2px}
.grp{font-family:var(--sans);font-size:11px;letter-spacing:.08em;font-weight:600;color:var(--ink);background:var(--field);padding:6px 8px;margin:16px 0 0}

.totals{margin:18px 0 0;border-top:2px solid var(--ink);padding-top:10px}
.totals div{display:flex;justify-content:space-between;padding:4px 0;font-size:14.5px}
.totals div.big{
  font-family:var(--sans);font-weight:700;font-size:19px;
  border-top:1px solid var(--rule);margin-top:7px;padding-top:11px;color:var(--cachet);
}
.totals div.rem{color:var(--valid)}

.echeance{margin-top:22px;border:1px solid var(--rule);padding:14px}
.echeance h3{font-family:var(--sans);font-size:12px;letter-spacing:.08em;margin:0 0 8px}
.echeance div{display:flex;justify-content:space-between;font-size:14px;padding:3px 0}

.recur{margin-top:22px;border-left:3px solid var(--cachet);padding:2px 0 2px 14px}
.recur h3{font-family:var(--sans);font-size:13px;margin:0 0 4px}
.recur p{margin:0;font-size:13.5px;color:var(--ink-soft)}

.conds{margin-top:24px;font-size:13px;color:var(--ink-soft);line-height:1.55}
.conds li{margin-bottom:5px}
.stamp{
  margin-top:26px;border:1px dashed var(--rule);padding:20px;text-align:center;
  font-family:var(--sans);font-size:12px;color:var(--ink-soft);
}

/* ---------- admin ---------- */
.reg{width:100%;border-collapse:collapse;font-size:14px;background:var(--paper)}
.reg th{
  font-family:var(--sans);font-size:11px;letter-spacing:.05em;color:var(--ink-soft);
  text-align:left;padding:9px 8px;border-bottom:1px solid var(--ink);font-weight:600;
}
.reg td{padding:11px 8px;border-bottom:1px solid var(--rule);vertical-align:top}
.reg tr{cursor:pointer}
.reg tr:hover td{background:var(--field)}
.reg .n{text-align:right;white-space:nowrap}
.tag{
  display:inline-block;font-size:11px;font-family:var(--sans);font-weight:600;
  padding:2px 7px;border:1px solid;border-radius:2px;white-space:nowrap;
}
.t-nouveau{color:var(--cachet);border-color:var(--cachet)}
.t-envoye{color:var(--ink);border-color:var(--ink)}
.t-accepte{color:var(--valid);border-color:var(--valid)}
.t-refuse{color:var(--ink-soft);border-color:var(--rule)}
.empty{padding:44px 20px;text-align:center;color:var(--ink-soft);font-size:15px}

.edit-row{display:grid;grid-template-columns:1fr 62px 108px 108px 34px;gap:7px;align-items:center;margin-bottom:7px}
.edit-row .nm{font-size:13px;line-height:1.3}
.edit-row .nm b{display:block;font-size:11px;font-family:var(--sans);color:var(--ink-soft);font-weight:600}
.edit-row input,.edit-row select{padding:7px 8px;font-size:13px}
.edit-row button{
  border:1px solid var(--rule);background:none;color:var(--cachet);
  font-size:17px;line-height:1;padding:6px 0;cursor:pointer;border-radius:2px;
}
@media(max-width:560px){
  .edit-row{grid-template-columns:1fr 56px 1fr 34px;grid-template-areas:"n n n x" "q p t x";}
  .edit-row .nm{grid-area:n;margin-bottom:3px}
  .edit-row .q{grid-area:q}.edit-row .lv{display:none}
  .edit-row .pu{grid-area:p}.edit-row .tt{grid-area:t}
  .edit-row button{grid-area:x}
}
.summary{
  display:grid;grid-template-columns:repeat(3,1fr);gap:1px;background:var(--rule);
  border:1px solid var(--rule);margin-bottom:18px;
}
.summary div{background:var(--paper);padding:13px 12px}
.summary b{display:block;font-size:21px;font-family:var(--sans);font-weight:700}
.summary small{color:var(--ink-soft);font-size:12px}

.note{
  background:#fff8f8;border-left:3px solid var(--cachet);
  padding:12px 14px;font-size:13.5px;line-height:1.5;margin:18px 0 0;
}

@media print{
  body{background:#fff}
  .topbar,.progress,.actions,.noprint{display:none !important}
  .sheet{border:0;padding:0;margin:0}
  .wrap{max-width:none;padding:0}
}
@media(prefers-reduced-motion:reduce){*{transition:none !important}}
</style>

<div class="topbar">
  <div class="inner">
    <div class="mark">DOMINIUM <span>TECH</span></div>
    <button class="switch" id="btnSwitch">Espace interne</button>
  </div>
</div>
<div class="progress"><i id="bar"></i></div>

<div class="wrap">
  <div class="sheet" id="app"></div>
</div>

<script>
/* =========================================================
   1. PARAMÈTRES  (feuille PARAMETRES du classeur)
   ========================================================= */
const P = {
  societe:"DOMINIUM GROUP SARL",
  branche:"DOMINIUM TECH — Agence digitale",
  adresse:"Abidjan, Biabou Portail — 200 m derrière pharmacie Route d'Alépé",
  tel:"+225 05 54 09 42 42 / +225 07 59 20 36 90",
  email:"info@dominium-civ.com",
  web:"www.dominium-civ.com",
  tva:0.18, tvaActive:true,
  remiseMax:0.05,
  acompte:0.40, deuxieme:0.30, solde:0.30,
  validite:30,
  majAccelere:0.30,
  penalite:0.02,
  garantie:30,
  codeInterne:"DOMINIUM2026"
};

/* =========================================================
   2. CATALOGUE  (feuille CATALOGUE_TARIFS — 100 prestations)
   ref, catégorie, prestation, ce qui est inclus, unité, PU E/S/P, jours
   ========================================================= */
const RAW=[
["A-01","ÉTUDE & CONCEPTION","Réunion de cadrage et recueil du besoin","Entretien sur site ou en visioconférence, analyse de l'activité, identification des objectifs, compte rendu écrit remis au client","Forfait",15000,25000,40000,1],
["A-02","ÉTUDE & CONCEPTION","Rédaction du cahier des charges","Document détaillé : arborescence, fonctionnalités, contraintes techniques, livrables, planning, critères de recette","Forfait",20000,35000,60000,2],
["A-03","ÉTUDE & CONCEPTION","Audit d'un site ou d'un outil existant","Analyse technique, ergonomie, vitesse, sécurité et référencement + rapport écrit de recommandations priorisées","Forfait",25000,50000,90000,3],
["A-04","ÉTUDE & CONCEPTION","Étude concurrentielle du secteur","Analyse de 5 acteurs du marché, positionnement, recommandations de différenciation","Forfait",20000,40000,75000,2],
["A-05","ÉTUDE & CONCEPTION","Architecture de l'information","Plan complet du site, parcours utilisateur, schéma de navigation validé par le client","Forfait",15000,25000,45000,1],
["B-01","DESIGN & IDENTITÉ","Maquette graphique — page d'accueil","2 propositions graphiques, 2 tours de correction, livrée en format image et fichier source","Page",35000,45000,80000,4],
["B-02","DESIGN & IDENTITÉ","Maquette graphique — page intérieure","1 proposition, 1 tour de correction, déclinée du style validé sur la page d'accueil","Page",10000,20000,35000,1],
["B-03","DESIGN & IDENTITÉ","Création de logo","3 pistes créatives, 2 tours de correction, fichiers vectoriels, déclinaisons couleur / noir et blanc / monochrome","Forfait",50000,100000,200000,7],
["B-04","DESIGN & IDENTITÉ","Charte graphique complète","Couleurs, typographies, règles d'utilisation du logo, exemples d'application, document PDF remis au client","Forfait",60000,120000,250000,7],
["B-05","DESIGN & IDENTITÉ","Déclinaison papeterie","Carte de visite, papier en-tête, signature e-mail, enveloppe — fichiers prêts à imprimer","Lot",30000,50000,90000,3],
["B-06","DESIGN & IDENTITÉ","Jeu d'icônes ou d'illustrations sur mesure","10 éléments graphiques originaux aux couleurs de la marque","Lot",25000,45000,80000,4],
["B-07","DESIGN & IDENTITÉ","Adaptation des maquettes au mobile","Reprise complète des maquettes pour téléphone et tablette","Forfait",25000,40000,70000,2],
["C-01","SITE WEB","Développement de la page d'accueil","Intégration de la maquette, blocs dynamiques, animations légères, adaptation mobile, tests","Page",45000,65000,110000,4],
["C-02","SITE WEB","Développement d'une page intérieure","Intégration, mise en page des contenus, adaptation mobile, tests","Page",20000,30000,50000,1],
["C-03","SITE WEB","Page Réalisations avec galerie","Galerie filtrable par catégorie, ouverture en plein écran, optimisation automatique des images","Page",25000,40000,70000,2],
["C-04","SITE WEB","Blog / actualités avec gestion des articles","Page liste, page article, catégories, back-office de publication autonome","Forfait",40000,60000,110000,3],
["C-05","SITE WEB","Formulaire de contact sécurisé","Champs personnalisés, protection anti-robot, envoi vers la boîte e-mail, message de confirmation","Unité",15000,25000,40000,1],
["C-06","SITE WEB","Formulaire avancé (devis, candidature, inscription)","Champs conditionnels, pièces jointes, enregistrement en base de données, export des réponses","Unité",30000,50000,90000,3],
["C-07","SITE WEB","Bouton WhatsApp et appel direct","Bouton flottant, message pré-rempli, appel en un clic depuis le téléphone","Forfait",10000,15000,25000,1],
["C-08","SITE WEB","Module de prise de rendez-vous","Calendrier, gestion des créneaux, confirmation par e-mail et SMS, back-office de gestion","Forfait",60000,100000,180000,5],
["C-09","SITE WEB","Espace membre et connexion sécurisée","Inscription, connexion, mot de passe oublié, gestion des rôles et des droits","Forfait",75000,125000,220000,7],
["C-10","SITE WEB","Site multilingue (français / anglais)","Duplication de la structure, sélecteur de langue, gestion des contenus par langue","Forfait",40000,70000,130000,3],
["C-11","SITE WEB","Intégration des contenus fournis par le client","Mise en page des textes et photos fournis, jusqu'à 10 pages, retouche légère des images","Forfait",20000,30000,50000,2],
["C-12","SITE WEB","Rédaction de contenu web","Texte original de 400 à 700 mots, optimisé pour le référencement, ton adapté à la marque","Page",15000,25000,45000,1],
["C-13","SITE WEB","Optimisation mobile et tests d'affichage","Vérification sur 5 appareils et 3 navigateurs, corrections d'affichage","Forfait",25000,35000,55000,2],
["C-14","SITE WEB","Optimisation de la vitesse de chargement","Compression des images, mise en cache, allègement du code, mesure avant et après","Forfait",20000,35000,60000,1],
["C-15","SITE WEB","Mise en ligne et configuration technique","Transfert sur le serveur, configuration du nom de domaine, certificat de sécurité, adresses e-mail, redirections","Forfait",15000,25000,40000,1],
["C-16","SITE WEB","Migration ou refonte d'un site existant","Récupération des contenus, redirections permanentes, conservation du référencement acquis","Forfait",50000,90000,160000,4],
["C-17","SITE WEB","Tests et recette finale","Tests fonctionnels complets, vérification multi-navigateurs et multi-appareils, corrections","Forfait",10000,20000,35000,1],
["C-18","SITE WEB","Garantie de corrections après livraison","Correction sans frais de toute anomalie signalée pendant 30, 60 ou 90 jours","Forfait",15000,25000,45000,0],
["D-01","E-COMMERCE","Mise en place de la boutique en ligne","Configuration du catalogue, catégories, panier, tunnel de commande, comptes clients","Forfait",100000,175000,300000,8],
["D-02","E-COMMERCE","Intégration du paiement mobile","Connexion Wave, Orange Money et MTN MoMo, tests réels, gestion des échecs, tableau de suivi des transactions","Forfait",100000,150000,250000,5],
["D-03","E-COMMERCE","Intégration du paiement par carte bancaire","Connexion au prestataire de paiement, tests, sécurisation 3D Secure","Forfait",75000,125000,200000,4],
["D-04","E-COMMERCE","Saisie et paramétrage d'un produit","Fiche complète : photos, description, prix, variantes, stock, catégories","Produit",1500,2500,4000,0],
["D-05","E-COMMERCE","Gestion des livraisons et des zones","Zones de livraison, tarifs, délais, suivi de commande côté client","Forfait",30000,50000,90000,2],
["D-06","E-COMMERCE","Codes promotionnels et campagnes","Génération de codes, règles d'application, dates de validité, suivi d'utilisation","Forfait",15000,30000,55000,1],
["D-07","E-COMMERCE","Notifications automatiques au client","E-mail et SMS : commande reçue, paiement confirmé, commande expédiée, commande livrée","Forfait",25000,45000,80000,2],
["D-08","E-COMMERCE","Tableau de bord vendeur","Ventes du jour et du mois, produits les plus vendus, état du stock, export comptable","Forfait",40000,70000,130000,3],
["D-09","E-COMMERCE","Prise de vue de produits","Photographie en studio, fond blanc, retouche et détourage — par produit","Produit",2500,4000,7000,0],
["E-01","APPLICATION MOBILE","Conception UX/UI de l'application","Parcours utilisateur, maquette de chaque écran, prototype cliquable validé avant développement","Écran",20000,35000,60000,1],
["E-02","APPLICATION MOBILE","Développement Android","Développement de l'écran, logique métier, tests sur 3 appareils réels","Écran",45000,75000,130000,2],
["E-03","APPLICATION MOBILE","Développement iOS","Développement de l'écran, tests, conformité aux règles de publication Apple","Écran",55000,90000,150000,2],
["E-04","APPLICATION MOBILE","Serveur et interface de programmation (API)","Base de données, API sécurisée, gestion des comptes, documentation technique","Forfait",150000,300000,600000,15],
["E-05","APPLICATION MOBILE","Notifications push","Configuration, segmentation des destinataires, envoi depuis le back-office","Forfait",40000,70000,120000,3],
["E-06","APPLICATION MOBILE","Mode hors connexion et synchronisation","Stockage local, file d'attente des opérations, synchronisation automatique au retour du réseau","Forfait",75000,130000,250000,7],
["E-07","APPLICATION MOBILE","Publication sur Play Store et App Store","Création des fiches, visuels, textes, soumission et suivi jusqu'à validation","Forfait",40000,65000,100000,6],
["E-08","APPLICATION MOBILE","Mise à jour majeure de l'application","Nouvelle version, tests complets, nouvelle soumission aux magasins","Forfait",50000,100000,200000,5],
["F-01","LOGICIEL DE GESTION","Analyse métier et modélisation des données","Entretiens avec les équipes, cartographie des processus, modèle de données validé","Forfait",50000,100000,200000,5],
["F-02","LOGICIEL DE GESTION","Module gestion de stock","Entrées, sorties, seuils d'alerte, inventaire, valorisation, historique des mouvements","Module",100000,175000,300000,12],
["F-03","LOGICIEL DE GESTION","Module caisse / point de vente","Enregistrement des ventes, tickets, moyens de paiement, clôture de caisse journalière","Module",100000,175000,300000,12],
["F-04","LOGICIEL DE GESTION","Module facturation et devis","Édition, numérotation automatique, TVA, relances, export PDF et impression","Module",75000,135000,250000,9],
["F-05","LOGICIEL DE GESTION","Module clients (CRM)","Fiches clients, historique des échanges, relances, segmentation, notes commerciales","Module",75000,130000,240000,9],
["F-06","LOGICIEL DE GESTION","Module ressources humaines","Dossiers employés, présences, congés, éléments variables de paie","Module",90000,160000,290000,12],
["F-07","LOGICIEL DE GESTION","Module comptabilité simplifiée","Recettes, dépenses, catégories, rapprochement, exports pour le comptable","Module",90000,160000,290000,12],
["F-08","LOGICIEL DE GESTION","Tableaux de bord et rapports","Indicateurs clés, graphiques, filtres par période, export Excel et PDF","Forfait",50000,90000,170000,6],
["F-09","LOGICIEL DE GESTION","Gestion des utilisateurs et des droits","Création des rôles, permissions par module, journal des connexions","Forfait",30000,55000,100000,3],
["F-10","LOGICIEL DE GESTION","Reprise des données existantes","Import depuis Excel ou depuis l'ancien logiciel, contrôle de cohérence, corrections","Forfait",40000,80000,160000,5],
["F-11","LOGICIEL DE GESTION","Installation et paramétrage sur poste","Installation, configuration, test de fonctionnement — par poste de travail","Poste",10000,15000,25000,1],
["G-01","PLATEFORME SCOLAIRE","Configuration de l'établissement","Classes, niveaux, matières, coefficients, périodes, année scolaire","Forfait",50000,90000,150000,3],
["G-02","PLATEFORME SCOLAIRE","Gestion des élèves et des inscriptions","Dossiers élèves, documents, photos, historique de scolarité","Module",60000,110000,190000,5],
["G-03","PLATEFORME SCOLAIRE","Saisie des notes et calcul des moyennes","Saisie par matière et par enseignant, coefficients, moyennes, rangs, statistiques de classe","Module",70000,125000,220000,6],
["G-04","PLATEFORME SCOLAIRE","Génération des bulletins","Modèle personnalisé avec logo et en-tête, appréciations, édition en masse au format PDF","Module",60000,110000,190000,5],
["G-05","PLATEFORME SCOLAIRE","Absences et discipline","Saisie des absences et retards, justificatifs, alertes aux parents","Module",45000,80000,140000,4],
["G-06","PLATEFORME SCOLAIRE","Espace parents","Consultation des notes, absences, messages de l'école et situation de scolarité","Module",80000,150000,260000,8],
["G-07","PLATEFORME SCOLAIRE","Scolarité et paiements","Échéanciers, encaissements, reste à payer, relances automatiques, reçus","Module",80000,150000,260000,8],
["G-08","PLATEFORME SCOLAIRE","Emploi du temps","Création, gestion des salles et des enseignants, publication aux élèves et parents","Module",60000,110000,190000,5],
["G-09","PLATEFORME SCOLAIRE","Envoi de SMS aux parents","Configuration de la passerelle SMS et des modèles de messages","Forfait",30000,55000,95000,2],
["H-01","HÉBERGEMENT & DOMAINE","Nom de domaine .com / .net / .org","Réservation au nom du client, configuration, renouvellement annuel","An",15000,20000,25000,0],
["H-02","HÉBERGEMENT & DOMAINE","Nom de domaine .ci","Réservation au nom du client auprès du registre ivoirien, renouvellement annuel","An",25000,30000,40000,0],
["H-03","HÉBERGEMENT & DOMAINE","Hébergement mutualisé géré","Espace disque, trafic, panneau de gestion, surveillance et assistance","An",75000,120000,180000,0],
["H-04","HÉBERGEMENT & DOMAINE","Hébergement sur serveur dédié virtuel (VPS)","Ressources garanties, administration système, supervision, sauvegardes","An",180000,350000,700000,0],
["H-05","HÉBERGEMENT & DOMAINE","Certificat de sécurité (SSL)","Installation, configuration et renouvellement automatique","An",0,25000,60000,0],
["H-06","HÉBERGEMENT & DOMAINE","Adresse e-mail professionnelle","Boîte aux lettres au nom du domaine, configuration sur téléphone et ordinateur — par boîte","Boîte / an",12000,18000,30000,0],
["H-07","HÉBERGEMENT & DOMAINE","Sauvegarde externalisée quotidienne","Copie complète chaque jour, historique de 7 à 30 jours, restauration testée","An",60000,100000,180000,0],
["H-08","HÉBERGEMENT & DOMAINE","Hébergement applicatif (serveur d'application mobile)","Serveur, base de données, supervision et sauvegardes","An",150000,280000,550000,0],
["I-01","RÉFÉRENCEMENT","Optimisation technique de base","Balises, structure des titres, plan du site, fichier robots, vitesse, données structurées","Forfait",25000,45000,80000,2],
["I-02","RÉFÉRENCEMENT","Recherche de mots-clés","Analyse du vocabulaire réellement utilisé par le marché, liste priorisée, plan de contenu","Forfait",30000,55000,100000,3],
["I-03","RÉFÉRENCEMENT","Création et optimisation de la fiche Google","Création, vérification de l'établissement, photos, horaires, description, catégories","Forfait",25000,40000,70000,4],
["I-04","RÉFÉRENCEMENT","Rédaction d'un article optimisé","800 à 1 200 mots, structure optimisée, images, publication","Article",20000,35000,60000,2],
["I-05","RÉFÉRENCEMENT","Suivi de positionnement mensuel","Rapport des positions, trafic, pages performantes et recommandations","Mois",30000,55000,100000,0],
["I-06","RÉFÉRENCEMENT","Inscription dans les annuaires professionnels","10 annuaires locaux et internationaux, fiches cohérentes","Forfait",20000,35000,60000,3],
["J-01","RÉSEAUX & CONTENU","Création et habillage des comptes","Photo de profil, bannière, description, informations de contact, publication épinglée","Compte",15000,25000,40000,1],
["J-02","RÉSEAUX & CONTENU","Gestion mensuelle des réseaux sociaux","Calendrier éditorial, publications, réponses aux messages et commentaires, rapport mensuel","Mois",75000,150000,300000,0],
["J-03","RÉSEAUX & CONTENU","Création d'un visuel","Publication, story ou affiche conçue aux couleurs de la marque","Visuel",5000,10000,20000,0],
["J-04","RÉSEAUX & CONTENU","Vidéo courte format vertical","Écriture du script, tournage, montage, sous-titres, habillage graphique","Vidéo",40000,75000,150000,3],
["J-05","RÉSEAUX & CONTENU","Vidéo institutionnelle","Écriture, tournage, montage, voix off, habillage — par minute de vidéo finale","Minute",100000,200000,400000,8],
["J-06","RÉSEAUX & CONTENU","Paramétrage d'une campagne sponsorisée","Ciblage, création des annonces, suivi et rapport de performance","Campagne",30000,60000,120000,2],
["J-07","RÉSEAUX & CONTENU","Shooting photo d'équipe ou d'entreprise","Demi-journée de prise de vue, sélection, retouche, livraison en trois formats","Demi-journée",100000,175000,300000,4],
["K-01","MAINTENANCE & ABONNEMENT","Abonnement ESSENTIEL","Hébergement, domaine, certificat, sauvegardes quotidiennes, mises à jour, 2 heures de modifications par mois, assistance aux heures ouvrées, rapport trimestriel","Mois",35000,50000,75000,0],
["K-02","MAINTENANCE & ABONNEMENT","Abonnement BUSINESS","Tout l'Essentiel + 6 heures de modifications par mois, suivi du référencement, gestion de la fiche Google, délai d'intervention garanti, point mensuel avec un chef de projet","Mois",100000,150000,250000,0],
["K-03","MAINTENANCE & ABONNEMENT","Abonnement PARTENAIRE","Tout le Business + chef de projet dédié, feuille de route annuelle, formation régulière des équipes, production de contenus, audit de sécurité annuel","Mois",250000,400000,750000,0],
["K-04","MAINTENANCE & ABONNEMENT","Intervention ponctuelle hors abonnement","Correction, modification ou dépannage facturé au temps passé, minimum une heure","Heure",15000,25000,40000,0],
["K-05","MAINTENANCE & ABONNEMENT","Intervention urgente hors heures ouvrées","Nuit, week-end et jours fériés — minimum deux heures","Heure",30000,50000,80000,0],
["K-06","MAINTENANCE & ABONNEMENT","Audit de sécurité annuel","Analyse des vulnérabilités, test des sauvegardes, rapport et plan de correction","Forfait",100000,200000,400000,4],
["K-07","MAINTENANCE & ABONNEMENT","Assistance téléphonique et WhatsApp","Ligne d'assistance aux heures ouvrées, réponse sous 4 heures","Mois",20000,35000,60000,0],
["L-01","FORMATION","Formation à l'espace de gestion du site","Séance pratique sur site ou à distance, guide écrit remis, questions-réponses","Séance",20000,35000,60000,1],
["L-02","FORMATION","Formation au logiciel métier","Formation par groupe de 5 personnes maximum, exercices pratiques, support écrit","Jour",75000,125000,200000,1],
["L-03","FORMATION","Guide utilisateur personnalisé","Document illustré avec captures d'écran et procédures pas à pas, aux couleurs du client","Forfait",25000,45000,80000,3],
["L-04","FORMATION","Vidéo tutorielle","Capture d'écran commentée de 3 à 5 minutes, montée et sous-titrée","Vidéo",20000,35000,60000,1],
["L-05","FORMATION","Accompagnement au démarrage sur site","Présence d'un technicien pendant les premiers jours d'utilisation réelle","Jour",50000,80000,130000,1],
["M-01","DIVERS & FRAIS","Déplacement dans le Grand Abidjan","Déplacement d'un technicien sur le site du client","Déplacement",10000,15000,25000,0],
["M-02","DIVERS & FRAIS","Mission à l'intérieur du pays","Transport, hébergement et indemnité journalière — sur justificatifs","Jour",50000,75000,120000,0],
["M-03","DIVERS & FRAIS","Licences, photos et polices sous licence","Achat refacturé au coût réel majoré de 15 % de frais de gestion","Unité",10000,20000,40000,0],
["M-04","DIVERS & FRAIS","Reprise après validation écrite du client","Modification majeure demandée après validation formelle d'une étape","Forfait",30000,60000,120000,0],
["M-05","DIVERS & FRAIS","Cession des sources et documentation technique","Remise du code source commenté et de la documentation d'exploitation","Forfait",75000,150000,350000,3]
];
const CAT={};
RAW.forEach(r=>CAT[r[0]]={ref:r[0],cat:r[1],nom:r[2],inc:r[3],unite:r[4],pu:{ESSENTIEL:r[5],STANDARD:r[6],PREMIUM:r[7]},jours:r[8]});

const RECUR=new Set(["H-01","H-02","H-03","H-04","H-05","H-06","H-07","H-08","K-01","K-02","K-03","K-07","I-05","J-02"]);

/* =========================================================
   3. STOCKAGE
   ========================================================= */
const mem={};
const Store={
  async get(k){
    try{ if(window.storage){const r=await window.storage.get(k,false); return r?JSON.parse(r.value):null;} }catch(e){}
    return mem[k]!==undefined?mem[k]:null;
  },
  async set(k,v){
    mem[k]=v;
    try{ if(window.storage) await window.storage.set(k,JSON.stringify(v),false); }catch(e){}
    return true;
  }
};

/* =========================================================
   4. MOTEUR DE DEVIS
   ========================================================= */
function L(ref,qte,niv){
  const c=CAT[ref]; if(!c||qte<=0) return null;
  return {ref, nom:c.nom, inc:c.inc, unite:c.unite, cat:c.cat,
          niveau:niv, qte:Math.round(qte), pu:c.pu[niv], jours:c.jours, recur:RECUR.has(ref)};
}

function construire(a){
  const N=a.niveau, out=[], add=(r,q,n)=>{const l=L(r,q,n||N); if(l) out.push(l);};

  add("A-01",1); add("A-02",1);

  /* ---- identité graphique (commune) ---- */
  if(a.identite==="logo"){ add("B-03",1); }
  if(a.identite==="logo_charte"){ add("B-03",1); add("B-04",1); }

  /* ---- SITE VITRINE / PROFESSIONNEL / REFONTE ---- */
  if(a.type==="site"||a.type==="refonte"){
    const pages=Math.max(1,+a.pages||5);
    if(a.type==="refonte") add("C-16",1);
    if(N!=="ESSENTIEL") add("A-05",1);
    add("B-01",1);
    // Essentiel : un seul gabarit intérieur, décliné. Standard et plus : jusqu'à 3.
    add("B-02", N==="ESSENTIEL" ? 1 : Math.min(pages-1,3));
    if(N!=="ESSENTIEL") add("B-07",1);
    add("C-01",1);
    // La page Réalisations est facturée à part (C-03), on ne la compte pas deux fois.
    add("C-02", pages-1-(a.opt.includes("galerie")?1:0));
    if(a.opt.includes("galerie")) add("C-03",1);
    if(a.opt.includes("blog")) add("C-04",1);
    add("C-05",1);
    if(a.opt.includes("form_avance")) add("C-06",1);
    if(a.opt.includes("rdv")) add("C-08",1);
    if(a.opt.includes("membre")) add("C-09",1);
    if(a.opt.includes("multilingue")) add("C-10",1);
    add("C-07",1);
    // Rédaction : une page sur deux porte un vrai texte de fond.
    if(a.contenus==="fournis") add("C-11",1); else add("C-12",Math.ceil(pages/2));
    add("C-13",1);
    if(N!=="ESSENTIEL") add("C-14",1);
    add("I-01",1);
    if(N!=="ESSENTIEL") add("I-03",1);
    if(N==="PREMIUM") add("I-02",1);
    add("C-15",1);
    if(N!=="ESSENTIEL") add("C-17",1);
    add("L-01",1);
    if(N!=="ESSENTIEL") add("L-03",1);
    add("C-18",1);
  }

  /* ---- BOUTIQUE EN LIGNE ---- */
  if(a.type==="boutique"){
    const pages=Math.max(1,+a.pages||4), prod=Math.max(1,+a.produits||50);
    add("B-01",1); add("B-02",Math.min(pages-1,3)); add("B-07",1);
    add("C-01",1); add("C-02",pages-1);
    add("D-01",1);
    if(a.paiement==="mobile"||a.paiement==="les_deux") add("D-02",1);
    if(a.paiement==="carte"||a.paiement==="les_deux") add("D-03",1);
    add("D-04",prod);
    if(a.opt.includes("photos_produits")) add("D-09",prod);
    add("D-05",1);
    if(a.opt.includes("promos")) add("D-06",1);
    if(a.opt.includes("notifs")) add("D-07",1);
    if(a.opt.includes("dashboard")) add("D-08",1);
    add("C-13",1); add("C-14",1); add("I-01",1);
    add("C-15",1); add("C-17",1);
    add("L-01",1); add("L-03",1); add("C-18",1);
  }

  /* ---- LOGICIEL DE GESTION ---- */
  if(a.type==="logiciel"){
    add("F-01",1);
    const map={stock:"F-02",caisse:"F-03",facturation:"F-04",crm:"F-05",rh:"F-06",compta:"F-07"};
    a.modules.forEach(m=>{ if(map[m]) add(map[m],1); });
    add("F-08",1); add("F-09",1);
    if(a.reprise==="oui") add("F-10",1);
    add("F-11",Math.max(1,+a.postes||3));
    add("L-02",Math.max(1,+a.formation||1));
    add("L-03",1);
    if(a.opt.includes("demarrage")) add("L-05",1);
    add("C-18",1);
  }

  /* ---- PLATEFORME SCOLAIRE ---- */
  if(a.type==="scolaire"){
    add("G-01",1);
    const map={eleves:"G-02",notes:"G-03",bulletins:"G-04",absences:"G-05",parents:"G-06",paiements:"G-07",edt:"G-08",sms:"G-09"};
    a.modules.forEach(m=>{ if(map[m]) add(map[m],1); });
    add("F-09",1);
    if(a.reprise==="oui") add("F-10",1);
    add("L-02",2); add("L-03",1); add("L-05",2); add("C-18",1);
  }

  /* ---- APPLICATION MOBILE ---- */
  if(a.type==="app"){
    const ec=Math.max(1,+a.ecrans||10);
    add("E-01",ec);
    if(a.plateforme==="android"||a.plateforme==="les_deux") add("E-02",ec);
    if(a.plateforme==="ios"||a.plateforme==="les_deux") add("E-03",ec);
    add("E-04",1);
    if(a.opt.includes("push")) add("E-05",1);
    if(a.opt.includes("horsligne")) add("E-06",1);
    add("E-07",1);
    add("L-02",1); add("L-04",3); add("C-18",1);
  }

  /* ---- FRAIS ANNUELS ---- */
  if(a.domaine==="com") add("H-01",1);
  if(a.domaine==="ci")  add("H-02",1);
  if(a.hebergement==="mutualise") add("H-03",1);
  if(a.hebergement==="vps")       add("H-04",1);
  if(a.type==="app")              add("H-08",1);
  if(a.hebergement!=="aucun")     add("H-05",1);
  const bal=+a.emails||0; if(bal>0) add("H-06",bal);
  if(a.opt.includes("sauvegarde")) add("H-07",1);
  if(a.maintenance&&a.maintenance!=="aucune"){
    add({essentiel:"K-01",business:"K-02",partenaire:"K-03"}[a.maintenance],12);
  }
  return out;
}

function calculer(d){
  const cr=l=>l.qte*l.pu;
  const creation=d.lignes.filter(l=>!l.recur), recur=d.lignes.filter(l=>l.recur);
  const stHT=creation.reduce((s,l)=>s+cr(l),0);
  const maj=d.accelere?Math.round(stHT*P.majAccelere):0;
  const remise=Math.round((stHT+maj)*(d.remise||0));
  const netHT=stHT+maj-remise;
  const tva=P.tvaActive?Math.round(netHT*P.tva):0;
  const ttc=netHT+tva;
  const recHT=recur.reduce((s,l)=>s+cr(l),0);
  const recTTC=recHT+(P.tvaActive?Math.round(recHT*P.tva):0);
  let j=creation.reduce((s,l)=>s+l.jours*(l.qte>1?Math.min(l.qte,6)*0.55:1),0);
  j=Math.max(7,Math.round(j*0.62));
  if(d.accelere) j=Math.max(5,Math.round(j*0.7));
  return {stHT,maj,remise,netHT,tva,ttc,recHT,recTTC,
          acompte:Math.round(ttc*P.acompte),
          deuxieme:Math.round(ttc*P.deuxieme),
          solde:ttc-Math.round(ttc*P.acompte)-Math.round(ttc*P.deuxieme),
          jours:j, creation, recur};
}

const fr=n=>new Intl.NumberFormat('fr-FR').format(Math.round(n))+" FCFA";
const esc=s=>String(s==null?"":s).replace(/[&<>"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
const dfr=d=>new Date(d).toLocaleDateString('fr-FR',{day:'2-digit',month:'long',year:'numeric'});

/* =========================================================
   5. ÉTAT
   ========================================================= */
const app=document.getElementById('app'), bar=document.getElementById('bar');
let mode="client", step=0, devisId=null, admOK=false;
let A={ nom:"",entreprise:"",tel:"",email:"",ville:"",
        type:"",pages:5,produits:50,postes:3,ecrans:10,formation:1,eleves:"",
        opt:[],modules:[],identite:"aucune",contenus:"fournis",paiement:"mobile",
        plateforme:"android",reprise:"non",domaine:"com",hebergement:"mutualise",
        emails:3,maintenance:"essentiel",niveau:"STANDARD",accelere:false,message:"" };

const TYPES=[
  ["site","Site internet vitrine ou professionnel","Présenter votre activité, être trouvé sur Google, recevoir des demandes"],
  ["boutique","Boutique en ligne","Vendre vos produits avec paiement Wave, Orange Money ou MTN MoMo"],
  ["logiciel","Logiciel de gestion","Stock, caisse, facturation, clients — pour commerce, dépôt, pharmacie"],
  ["scolaire","Plateforme scolaire","Élèves, notes, bulletins, espace parents, scolarité"],
  ["app","Application mobile","Android ou iPhone, pour suivre votre activité à distance"],
  ["refonte","Refonte d'un site existant","Vous avez déjà un site et vous voulez le reprendre à neuf"]
];

/* =========================================================
   6. PETITS COMPOSANTS
   ========================================================= */
function champ(k,lab,type,ph,hint){
  return `<label class="f"><b>${lab}</b>${hint?`<em>${hint}</em>`:''}
    <input type="${type}" data-k="${k}" value="${esc(A[k])}" placeholder="${ph||''}"></label>`;
}
function radios(k,opts){
  return `<div class="choices">`+opts.map(o=>`
    <label class="choice ${A[k]===o[0]?'on':''}">
      <input type="radio" name="${k}" value="${o[0]}" ${A[k]===o[0]?'checked':''} data-radio="${k}">
      <span><b>${o[1]}</b>${o[2]?`<small>${o[2]}</small>`:''}</span>
    </label>`).join('')+`</div>`;
}
function checks(k,opts){
  return `<div class="choices">`+opts.map(o=>`
    <label class="choice ${A[k].includes(o[0])?'on':''}">
      <input type="checkbox" value="${o[0]}" ${A[k].includes(o[0])?'checked':''} data-check="${k}">
      <span><b>${o[1]}</b>${o[2]?`<small>${o[2]}</small>`:''}</span>
    </label>`).join('')+`</div>`;
}
function nav(back,next,lab){
  return `<div class="actions">
    ${back?`<button class="sec" data-go="${step-1}">Retour</button>`:''}
    <button class="pri" data-next="1">${lab||'Continuer'}</button></div>`;
}

/* =========================================================
   7. PARCOURS CLIENT
   ========================================================= */
const NB=5;
function rendreClient(){
  bar.style.width=(step/NB*100)+"%";
  if(step===0){
    app.innerHTML=`
      <h1>Recevez votre devis en 2 minutes</h1>
      <p class="lede">Répondez à quelques questions sur votre projet. Vous recevrez
      immédiatement un devis détaillé, ligne par ligne, avec le prix de chaque élément.
      Aucun engagement.</p>
      <div class="step-no"><span>Étape 1 sur 5</span><span>Vos coordonnées</span></div>
      ${champ("nom","Vos nom et prénoms","text","")}
      <div class="two">${champ("entreprise","Votre entreprise","text","")}${champ("ville","Ville","text","Abidjan")}</div>
      <div class="two">${champ("tel","Téléphone (WhatsApp)","tel","07 00 00 00 00")}${champ("email","Adresse e-mail","email","vous@exemple.com")}</div>
      <p class="err hide" id="err"></p>
      ${nav(false,true,"Commencer")}`;
  }
  else if(step===1){
    app.innerHTML=`
      <div class="step-no"><span>Étape 2 sur 5</span><span>Votre projet</span></div>
      <h2>De quoi avez-vous besoin ?</h2>
      <p class="lede">Choisissez ce qui ressemble le plus à votre projet.</p>
      ${radios("type",TYPES)}
      <p class="err hide" id="err"></p>
      ${nav(true,true)}`;
  }
  else if(step===2){ app.innerHTML=detail(); }
  else if(step===3){
    app.innerHTML=`
      <div class="step-no"><span>Étape 4 sur 5</span><span>Hébergement et suivi</span></div>
      <h2>Ce qui fait vivre votre outil</h2>
      <p class="lede">Ces frais sont annuels et se renouvellent. Ils sont toujours présentés
      séparément du coût de création.</p>
      ${A.type==="app"?"":`
      <label class="f"><b>Nom de domaine</b><em>L'adresse de votre site sur internet</em></label>
      ${radios("domaine",[["com",".com — le plus courant",""],["ci",".ci — domaine ivoirien",""],["aucun","J'en ai déjà un",""]])}
      <label class="f"><b>Hébergement</b><em>L'espace où votre site est installé</em></label>
      ${radios("hebergement",[
        ["mutualise","Hébergement géré","Convient à un site vitrine ou professionnel"],
        ["vps","Serveur dédié","Recommandé pour une boutique ou un logiciel de gestion"],
        ["aucun","J'en ai déjà un",""]])}
      ${champ("emails","Adresses e-mail professionnelles","number","3","Exemple : contact@votreentreprise.ci")}
      `}
      <label class="f"><b>Suivi et maintenance</b><em>Mises à jour, sauvegardes, assistance</em></label>
      ${radios("maintenance",[
        ["essentiel","Essentiel","Sauvegardes, mises à jour, 2 h de modifications par mois"],
        ["business","Business","6 h de modifications, suivi du référencement, chef de projet"],
        ["partenaire","Partenaire","Chef de projet dédié, feuille de route annuelle, formation"],
        ["aucune","Pas de maintenance pour l'instant",""]])}
      ${checks("opt",[["sauvegarde","Ajouter la sauvegarde externalisée quotidienne","Copie complète chaque jour, restauration testée"]].concat(
        A.opt.filter(o=>o!=="sauvegarde").length?[]:[]))}
      ${nav(true,true)}`;
  }
  else if(step===4){
    app.innerHTML=`
      <div class="step-no"><span>Étape 5 sur 5</span><span>Niveau et délai</span></div>
      <h2>Quel niveau de finition ?</h2>
      <p class="lede">Le même projet peut être réalisé à trois niveaux d'exigence.
      Le niveau Standard est celui que nous recommandons.</p>
      ${radios("niveau",[
        ["ESSENTIEL","Essentiel","Le strict nécessaire, bien fait. Modèle adapté, un tour de correction."],
        ["STANDARD","Standard — recommandé","Création sur mesure, deux tours de correction, optimisation, formation."],
        ["PREMIUM","Premium","Sur mesure complet, exigences élevées, accompagnement renforcé."]])}
      <label class="f"><b>Délai souhaité</b></label>
      ${radios("delai",[["normal","Délai normal",""],["accelere","Livraison prioritaire","Majoration de 30 % — mobilisation d'équipe renforcée"]])}
      <label class="f"><b>Précisions sur votre projet</b><em>Facultatif — tout ce qui nous aidera à affiner</em>
        <textarea data-k="message" rows="3" placeholder="Exemple : je gère une quincaillerie à Yopougon, 4 vendeurs, je perds du stock.">${esc(A.message)}</textarea></label>
      ${nav(true,true,"Voir mon devis")}`;
  }
  else if(step===5){ rendreDevis(); }
  cablerFormulaire();
}

function detail(){
  const h=`<div class="step-no"><span>Étape 3 sur 5</span><span>Détail du besoin</span></div>`;
  const id=`<label class="f"><b>Identité visuelle</b><em>Logo et couleurs de votre marque</em></label>
    ${radios("identite",[["aucune","J'ai déjà mon logo et mes couleurs",""],
      ["logo","Créer mon logo",""],["logo_charte","Créer logo et charte graphique complète",""]])}`;

  if(A.type==="site"||A.type==="refonte") return h+`
    <h2>Votre site</h2>
    ${champ("pages","Combien de pages ?","number","5","Accueil, À propos, Services, Réalisations, Contact = 5 pages")}
    <label class="f"><b>Fonctions souhaitées</b></label>
    ${checks("opt",[
      ["blog","Blog / actualités","Publier vos articles vous-même — très bon pour Google"],
      ["galerie","Galerie de réalisations","Photos de vos travaux, filtrables par catégorie"],
      ["form_avance","Formulaire de devis en ligne","Champs conditionnels, pièces jointes, enregistrement des demandes"],
      ["rdv","Prise de rendez-vous","Calendrier et confirmation automatique"],
      ["membre","Espace client sécurisé","Connexion, mots de passe, droits d'accès"],
      ["multilingue","Site en français et en anglais",""]])}
    <label class="f"><b>Les textes et photos</b></label>
    ${radios("contenus",[["fournis","Je fournis tout","Textes et images prêts"],
      ["rediger","DOMINIUM rédige les textes","Contenu original optimisé pour Google"]])}
    ${id}${nav(true,true)}`;

  if(A.type==="boutique") return h+`
    <h2>Votre boutique</h2>
    ${champ("produits","Combien de produits à mettre en ligne ?","number","50")}
    ${champ("pages","Pages de présentation en plus de la boutique","number","4")}
    <label class="f"><b>Moyens de paiement</b></label>
    ${radios("paiement",[["mobile","Paiement mobile","Wave, Orange Money, MTN MoMo"],
      ["carte","Carte bancaire",""],["les_deux","Les deux",""]])}
    <label class="f"><b>Fonctions souhaitées</b></label>
    ${checks("opt",[
      ["notifs","Notifications automatiques","Commande reçue, payée, expédiée, livrée"],
      ["dashboard","Tableau de bord vendeur","Ventes du jour, produits les plus vendus, état du stock"],
      ["promos","Codes promotionnels",""],
      ["photos_produits","Photos de mes produits en studio","Réalisé par DOMINIUM COMM — par produit"]])}
    ${id}${nav(true,true)}`;

  if(A.type==="logiciel") return h+`
    <h2>Votre logiciel</h2>
    <label class="f"><b>Quels modules ?</b><em>Chaque module est facturé séparément</em></label>
    ${checks("modules",[
      ["stock","Gestion de stock","Entrées, sorties, seuils d'alerte, inventaire, valorisation"],
      ["caisse","Caisse / point de vente","Ventes, tickets, clôture de caisse journalière"],
      ["facturation","Facturation et devis","Numérotation automatique, TVA, relances, export PDF"],
      ["crm","Clients (CRM)","Fiches, historique, relances, segmentation"],
      ["rh","Ressources humaines","Employés, présences, congés, éléments de paie"],
      ["compta","Comptabilité simplifiée","Recettes, dépenses, exports pour le comptable"]])}
    ${champ("postes","Sur combien de postes ?","number","3","Nombre d'ordinateurs où le logiciel sera installé")}
    ${champ("formation","Jours de formation","number","1","Groupes de 5 personnes maximum")}
    <label class="f"><b>Vos données actuelles</b></label>
    ${radios("reprise",[["oui","Reprendre mes données existantes","Import depuis Excel ou ancien logiciel"],
      ["non","Partir de zéro",""]])}
    ${checks("opt",[["demarrage","Accompagnement sur site au démarrage","Un technicien présent les premiers jours"]])}
    ${nav(true,true)}`;

  if(A.type==="scolaire") return h+`
    <h2>Votre établissement</h2>
    ${champ("eleves","Nombre d'élèves environ","number","400")}
    <label class="f"><b>Quels modules ?</b></label>
    ${checks("modules",[
      ["eleves","Élèves et inscriptions","Dossiers, documents, photos, historique de scolarité"],
      ["notes","Notes et moyennes","Saisie par matière, coefficients, rangs, statistiques"],
      ["bulletins","Bulletins","Modèle personnalisé, appréciations, édition en masse en PDF"],
      ["absences","Absences et discipline","Retards, justificatifs, alertes aux parents"],
      ["parents","Espace parents","Notes, absences, messages, situation de scolarité"],
      ["paiements","Scolarité et paiements","Échéanciers, encaissements, reste à payer, reçus"],
      ["edt","Emploi du temps","Salles, enseignants, publication aux élèves"],
      ["sms","SMS aux parents","Passerelle SMS et modèles de messages"]])}
    <label class="f"><b>Vos données actuelles</b></label>
    ${radios("reprise",[["oui","Reprendre mes fichiers existants",""],["non","Partir de zéro",""]])}
    ${nav(true,true)}`;

  if(A.type==="app") return h+`
    <h2>Votre application</h2>
    <label class="f"><b>Sur quels téléphones ?</b></label>
    ${radios("plateforme",[["android","Android uniquement","Le plus répandu en Côte d'Ivoire"],
      ["ios","iPhone uniquement",""],["les_deux","Android et iPhone","Double le coût de développement"]])}
    ${champ("ecrans","Combien d'écrans ?","number","10","Accueil, connexion, liste, détail, profil… comptez large")}
    <label class="f"><b>Fonctions souhaitées</b></label>
    ${checks("opt",[
      ["push","Notifications push","Envoyer des messages depuis votre back-office"],
      ["horsligne","Fonctionne sans connexion","Synchronisation automatique au retour du réseau"]])}
    ${id}${nav(true,true)}`;

  return h+`<p>Choisissez d'abord un type de projet.</p>${nav(true,false)}`;
}

/* =========================================================
   8. VALIDATION + NAVIGATION
   ========================================================= */
function valider(){
  const e=document.getElementById('err');
  const ko=m=>{ if(e){e.textContent=m;e.classList.remove('hide');} return false; };
  if(step===0){
    if(!A.nom.trim()) return ko("Indiquez vos nom et prénoms.");
    if(!A.tel.trim()) return ko("Indiquez un numéro de téléphone.");
    if(!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(A.email)) return ko("L'adresse e-mail n'est pas valide — c'est là que part le devis.");
  }
  if(step===1 && !A.type) return ko("Choisissez un type de projet.");
  if(step===2){
    if(A.type==="logiciel"&&!A.modules.length) return ko("Choisissez au moins un module.");
    if(A.type==="scolaire"&&!A.modules.length) return ko("Choisissez au moins un module.");
  }
  if(e) e.classList.add('hide');
  return true;
}

function cablerFormulaire(){
  app.querySelectorAll('[data-k]').forEach(el=>{
    el.addEventListener('input',()=>{ A[el.dataset.k]=el.value; });
  });
  app.querySelectorAll('[data-radio]').forEach(el=>{
    el.addEventListener('change',()=>{
      const k=el.dataset.radio;
      if(k==="delai"){ A.accelere=(el.value==="accelere"); A.delai=el.value; }
      else A[k]=el.value;
      app.querySelectorAll(`[data-radio="${k}"]`).forEach(r=>r.closest('.choice').classList.toggle('on',r.checked));
    });
  });
  app.querySelectorAll('[data-check]').forEach(el=>{
    el.addEventListener('change',()=>{
      const k=el.dataset.check, v=el.value;
      if(el.checked){ if(!A[k].includes(v)) A[k].push(v); }
      else A[k]=A[k].filter(x=>x!==v);
      el.closest('.choice').classList.toggle('on',el.checked);
    });
  });
  app.querySelectorAll('[data-next]').forEach(b=>b.addEventListener('click',()=>{
    if(!valider()) return;
    step++; if(step===5) enregistrer(); else rendreClient();
  }));
  app.querySelectorAll('[data-go]').forEach(b=>b.addEventListener('click',()=>{
    step=+b.dataset.go; rendreClient(); window.scrollTo(0,0);
  }));
  window.scrollTo(0,0);
}

/* =========================================================
   9. ENREGISTREMENT ET DOCUMENT
   ========================================================= */
async function enregistrer(){
  const reg=(await Store.get('devis'))||[];
  const seq=String(reg.length+1).padStart(3,'0');
  const d={
    id:"DG-TECH-"+new Date().getFullYear()+"-"+seq,
    date:new Date().toISOString(),
    client:{nom:A.nom,entreprise:A.entreprise,tel:A.tel,email:A.email,ville:A.ville},
    objet:objet(),
    type:A.type, niveau:A.niveau, accelere:A.accelere, message:A.message,
    remise:0, statut:"Nouveau",
    lignes:construire(A)
  };
  reg.unshift(d); await Store.set('devis',reg);
  devisId=d.id; rendreDevis(d);
}
function objet(){
  const t={site:"Création d'un site internet professionnel",boutique:"Création d'une boutique en ligne avec paiement mobile",
    logiciel:"Développement d'un logiciel de gestion",scolaire:"Mise en place d'une plateforme scolaire",
    app:"Développement d'une application mobile",refonte:"Refonte d'un site internet existant"}[A.type];
  return t||"Prestation numérique";
}

async function rendreDevis(dd){
  bar.style.width="100%";
  const reg=(await Store.get('devis'))||[];
  const d=dd||reg.find(x=>x.id===devisId);
  if(!d){ app.innerHTML="<p>Devis introuvable.</p>"; return; }
  app.innerHTML=docHTML(d,true);
  const b=document.getElementById('btnPrint'); if(b) b.onclick=()=>window.print();
  const n=document.getElementById('btnNew'); if(n) n.onclick=()=>{ step=0; A.opt=[]; A.modules=[]; A.type=""; rendreClient(); };
  window.scrollTo(0,0);
}

function docHTML(d,prov){
  const t=calculer(d);
  const exp=new Date(new Date(d.date).getTime()+P.validite*864e5);
  const rows=ls=>{
    let cur="",h="";
    ls.forEach(l=>{
      if(l.cat!==cur){ cur=l.cat; h+=`<tr><td colspan="4" style="padding:0;border:0"><div class="grp">${cur}</div></td></tr>`; }
      h+=`<tr><td><span class="ref">${l.ref}</span><br>${esc(l.nom)}<span class="inc">${esc(l.inc)}</span></td>
          <td class="n">${l.qte}</td><td class="n">${fr(l.pu)}</td><td class="n"><b>${fr(l.qte*l.pu)}</b></td></tr>`;
    });
    return h;
  };
  return `
  <div class="doc">
    <div class="doc-head">
      <div class="no">${prov?"DEVIS PROVISOIRE":"DEVIS"} · ${d.id}</div>
      <h2>${esc(d.objet)}</h2>
      <div style="font-family:var(--sans);font-size:12.5px;color:var(--ink-soft)">
        Émis le ${dfr(d.date)} · Valable jusqu'au ${dfr(exp)} · Niveau ${d.niveau.toLowerCase()}
      </div>
    </div>

    <div class="parties">
      <div><h3>PRESTATAIRE</h3><p><b>${P.societe}</b><br>${P.branche}<br>${P.adresse}<br>${P.tel}<br>${P.email}</p></div>
      <div><h3>CLIENT</h3><p><b>${esc(d.client.entreprise||d.client.nom)}</b><br>${esc(d.client.nom)}<br>${esc(d.client.ville)}<br>${esc(d.client.tel)}<br>${esc(d.client.email)}</p></div>
    </div>

    ${prov?`<div class="note"><b>Ce devis est provisoire.</b> Il a été établi automatiquement à partir de vos réponses,
    sur la base de notre grille tarifaire officielle. Un chargé de clientèle vous appelle sous 24 h ouvrées pour valider
    le périmètre et vous remettre le devis définitif signé.</div>`:''}

    <table class="lines">
      <thead><tr><th>Prestation et détail de ce qui est inclus</th><th class="n">Qté</th><th class="n">Prix unitaire</th><th class="n">Total HT</th></tr></thead>
      <tbody>${rows(t.creation)}</tbody>
    </table>

    <div class="totals">
      <div><span>Sous-total hors taxes</span><span>${fr(t.stHT)}</span></div>
      ${t.maj?`<div><span>Majoration livraison prioritaire (30 %)</span><span>${fr(t.maj)}</span></div>`:''}
      ${t.remise?`<div class="rem"><span>Remise commerciale (${Math.round((d.remise||0)*100)} %)</span><span>− ${fr(t.remise)}</span></div>`:''}
      <div><span>Net commercial HT</span><span>${fr(t.netHT)}</span></div>
      <div><span>TVA 18 %</span><span>${fr(t.tva)}</span></div>
      <div class="big"><span>Total à payer TTC</span><span>${fr(t.ttc)}</span></div>
    </div>

    <div class="echeance">
      <h3>ÉCHÉANCIER DE PAIEMENT</h3>
      <div><span>À la signature — 40 %</span><b>${fr(t.acompte)}</b></div>
      <div><span>À la validation de la maquette — 30 %</span><b>${fr(t.deuxieme)}</b></div>
      <div><span>À la livraison — 30 %</span><b>${fr(t.solde)}</b></div>
      <div style="border-top:1px solid var(--rule);margin-top:7px;padding-top:7px">
        <span>Délai de réalisation estimé</span><b>${t.jours} jours ouvrés</b></div>
    </div>

    ${t.recur.length?`
    <div class="recur">
      <h3>Frais annuels récurrents — ${fr(t.recTTC)} TTC par an</h3>
      <p>Ces lignes ne font pas partie du prix de création et se renouvellent chaque année :</p>
      <table class="lines" style="margin-top:9px">
        <tbody>${t.recur.map(l=>`<tr><td><span class="ref">${l.ref}</span> ${esc(l.nom)}${l.qte>1?` × ${l.qte}`:''}</td><td class="n">${fr(l.qte*l.pu)}</td></tr>`).join('')}</tbody>
      </table>
    </div>`:''}

    <div class="conds">
      <ul style="padding-left:18px;margin:0">
        <li>Devis valable ${P.validite} jours à compter de la date d'émission.</li>
        <li>Aucun travail ne démarre avant réception de l'acompte et retour du devis signé avec la mention « bon pour accord ».</li>
        <li>Garantie de correction des anomalies : ${P.garantie} jours après la livraison.</li>
        <li>Tout retard de paiement entraîne une pénalité de ${P.penalite*100} % par mois.</li>
        <li>Le nom de domaine et tous les accès sont enregistrés au nom du client et lui sont remis à la livraison.</li>
        <li>Toute demande hors du périmètre décrit fera l'objet d'un avenant chiffré et validé par écrit avant exécution.</li>
      </ul>
    </div>

    <div class="stamp">Pour ${P.societe} — signature et cachet &nbsp;·&nbsp; Pour le client — « bon pour accord », date, signature et cachet</div>
  </div>

  <div class="actions noprint">
    <button class="pri" id="btnPrint">Enregistrer en PDF ou imprimer</button>
    ${prov?`<button class="sec" id="btnNew">Nouveau devis</button>`:''}
  </div>`;
}

/* =========================================================
   10. ESPACE INTERNE
   ========================================================= */
async function rendreAdmin(){
  bar.style.width="0%";
  if(!admOK){
    app.innerHTML=`<h2>Espace interne</h2>
      <p class="lede">Réservé à l'équipe DOMINIUM TECH.</p>
      <label class="f"><b>Code d'accès</b><input type="password" id="pw" autocomplete="off"></label>
      <p class="err hide" id="err"></p>
      <div class="actions"><button class="pri" id="ok">Ouvrir</button></div>`;
    document.getElementById('ok').onclick=()=>{
      if(document.getElementById('pw').value===P.codeInterne){ admOK=true; rendreAdmin(); }
      else { const e=document.getElementById('err'); e.textContent="Code incorrect."; e.classList.remove('hide'); }
    };
    document.getElementById('pw').addEventListener('keydown',e=>{ if(e.key==='Enter') document.getElementById('ok').click(); });
    return;
  }
  const reg=(await Store.get('devis'))||[];
  if(devisId){ return rendreAdminDevis(reg.find(x=>x.id===devisId)); }

  const tot=reg.reduce((s,d)=>s+calculer(d).ttc,0);
  const nouv=reg.filter(d=>d.statut==="Nouveau").length;
  app.innerHTML=`
    <h2>Registre des devis</h2>
    <p class="lede">Chaque demande reçue via le formulaire produit un devis chiffré automatiquement.
    Ouvrez-le pour l'ajuster avant de l'envoyer.</p>
    <div class="summary">
      <div><b>${reg.length}</b><small>devis émis</small></div>
      <div><b style="color:var(--cachet)">${nouv}</b><small>à traiter</small></div>
      <div><b>${new Intl.NumberFormat('fr-FR',{notation:'compact'}).format(tot)}</b><small>FCFA en portefeuille</small></div>
    </div>
    ${reg.length?`<table class="reg"><thead><tr>
      <th>Devis</th><th>Client</th><th class="n">Montant TTC</th><th>Statut</th></tr></thead><tbody>
      ${reg.map(d=>{const t=calculer(d);return `<tr data-open="${d.id}">
        <td><b style="font-size:12px;font-family:var(--sans)">${d.id}</b><br>
            <span style="color:var(--ink-soft);font-size:12.5px">${new Date(d.date).toLocaleDateString('fr-FR')} · ${esc(d.objet.slice(0,34))}</span></td>
        <td>${esc(d.client.entreprise||d.client.nom)}<br><span style="color:var(--ink-soft);font-size:12.5px">${esc(d.client.tel)}</span></td>
        <td class="n"><b>${fr(t.ttc)}</b>${t.recTTC?`<br><span style="color:var(--ink-soft);font-size:12px">+ ${fr(t.recTTC)}/an</span>`:''}</td>
        <td><span class="tag t-${d.statut.toLowerCase().replace('é','e')}">${d.statut}</span></td></tr>`}).join('')}
      </tbody></table>`
    :`<div class="empty">Aucun devis pour le moment.<br>Les demandes du formulaire apparaîtront ici.</div>`}
    <div class="actions">
      <button class="sec" id="exp">Exporter le registre</button>
      <button class="gho" id="quit">Revenir au formulaire client</button>
    </div>`;
  app.querySelectorAll('[data-open]').forEach(r=>r.onclick=()=>{ devisId=r.dataset.open; rendreAdmin(); });
  document.getElementById('quit').onclick=()=>{ mode="client"; step=0; rendreClient(); majBouton(); };
  document.getElementById('exp').onclick=()=>{
    const b=new Blob([JSON.stringify(reg,null,2)],{type:'application/json'});
    const a=document.createElement('a'); a.href=URL.createObjectURL(b);
    a.download='registre-devis-dominium.json'; a.click();
  };
}

async function rendreAdminDevis(d){
  if(!d){ devisId=null; return rendreAdmin(); }
  const t=calculer(d);
  const opts=Object.values(CAT).map(c=>`<option value="${c.ref}">${c.ref} — ${c.nom}</option>`).join('');
  app.innerHTML=`
    <div class="step-no"><span>${d.id}</span><span>${esc(d.client.entreprise||d.client.nom)}</span></div>
    <h2>Ajuster le devis</h2>
    <p class="lede">Modifiez les quantités, le niveau ou le prix unitaire. Le total se recalcule seul.</p>
    ${d.message?`<div class="note"><b>Message du client :</b> ${esc(d.message)}</div>`:''}
    <div style="margin:20px 0 6px;font-family:var(--sans);font-size:11px;letter-spacing:.06em;color:var(--ink-soft)">
      PRESTATION &nbsp;·&nbsp; QTÉ &nbsp;·&nbsp; NIVEAU &nbsp;·&nbsp; PRIX UNITAIRE</div>
    <div id="lignes">${d.lignes.map((l,i)=>`
      <div class="edit-row">
        <div class="nm"><b>${l.ref}${l.recur?' · annuel':''}</b>${esc(l.nom)}</div>
        <input class="q" type="number" min="0" value="${l.qte}" data-i="${i}" data-f="qte">
        <select class="lv" data-i="${i}" data-f="niveau">
          ${["ESSENTIEL","STANDARD","PREMIUM"].map(n=>`<option ${l.niveau===n?'selected':''}>${n}</option>`).join('')}
        </select>
        <input class="pu" type="number" step="500" value="${l.pu}" data-i="${i}" data-f="pu">
        <button data-del="${i}" title="Retirer">×</button>
      </div>`).join('')}</div>

    <div class="two" style="margin-top:16px">
      <label class="f"><b>Ajouter une prestation</b>
        <select id="addRef"><option value="">— choisir dans le catalogue —</option>${opts}</select></label>
      <label class="f"><b>Remise commerciale (%)</b>
        <em>Au-delà de ${P.remiseMax*100} %, accord de la Direction requis</em>
        <input type="number" id="rem" min="0" max="30" value="${Math.round((d.remise||0)*100)}"></label>
    </div>
    <label class="f"><b>Statut</b>
      <select id="st">${["Nouveau","Envoyé","Accepté","Refusé"].map(s=>`<option ${d.statut===s?'selected':''}>${s}</option>`).join('')}</select></label>

    <div class="summary" style="margin-top:18px">
      <div><b>${fr(t.netHT)}</b><small>net HT</small></div>
      <div><b style="color:var(--cachet)">${fr(t.ttc)}</b><small>total TTC</small></div>
      <div><b>${t.jours} j</b><small>délai estimé</small></div>
    </div>
    ${(d.remise||0)>P.remiseMax?`<div class="note">Remise supérieure à ${P.remiseMax*100} % — validation de la Direction obligatoire avant envoi.</div>`:''}

    <div class="actions">
      <button class="pri" id="voir">Voir le devis définitif</button>
      <button class="sec" id="back">Retour au registre</button>
    </div>`;

  const save=async()=>{ const reg=(await Store.get('devis'))||[];
    const i=reg.findIndex(x=>x.id===d.id); if(i>=0) reg[i]=d; await Store.set('devis',reg); };

  app.querySelectorAll('[data-f]').forEach(el=>el.addEventListener('change',async()=>{
    const l=d.lignes[+el.dataset.i], f=el.dataset.f;
    if(f==="niveau"){ l.niveau=el.value; l.pu=CAT[l.ref].pu[el.value]; }
    else l[f]=+el.value;
    await save(); rendreAdminDevis(d);
  }));
  app.querySelectorAll('[data-del]').forEach(b=>b.onclick=async()=>{
    d.lignes.splice(+b.dataset.del,1); await save(); rendreAdminDevis(d); });
  document.getElementById('addRef').onchange=async e=>{
    if(!e.target.value) return;
    d.lignes.push(L(e.target.value,1,d.niveau)); await save(); rendreAdminDevis(d); };
  document.getElementById('rem').onchange=async e=>{
    d.remise=Math.max(0,Math.min(30,+e.target.value))/100; await save(); rendreAdminDevis(d); };
  document.getElementById('st').onchange=async e=>{ d.statut=e.target.value; await save(); rendreAdminDevis(d); };
  document.getElementById('back').onclick=()=>{ devisId=null; rendreAdmin(); };
  document.getElementById('voir').onclick=()=>{
    app.innerHTML=docHTML(d,false)+`<div class="actions noprint"><button class="sec" id="r">Retour à l'ajustement</button></div>`;
    document.getElementById('btnPrint').onclick=()=>window.print();
    document.getElementById('r').onclick=()=>rendreAdminDevis(d);
    window.scrollTo(0,0);
  };
  window.scrollTo(0,0);
}

/* =========================================================
   11. DÉMARRAGE
   ========================================================= */
function majBouton(){
  document.getElementById('btnSwitch').textContent = mode==="client" ? "Espace interne" : "Formulaire client";
}
document.getElementById('btnSwitch').onclick=()=>{
  if(mode==="client"){ mode="admin"; devisId=null; rendreAdmin(); }
  else { mode="client"; step=0; rendreClient(); }
  majBouton();
};
rendreClient();
</script>