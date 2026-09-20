import { expect, test } from '@playwright/test';
import { GENERAL_ADMIN, signInToGeneralDashboard } from './support.js';

test.describe.configure({ mode: 'serial' });

const CLIENT_NAME = 'E2E Prospect Devis';
const CLIENT_EMAIL = 'e2e-devis@domitium.test';

/** Coche la carte de choix portant la valeur demandée. */
async function chooseOption(page, name, value) {
  await page.locator(`label.opt-card:has(input[name="${name}"][value="${value}"])`).click();
}

/** Ouvre la fiche projet correspondant à une recherche depuis l'administration. */
async function openFirstProjectMatching(page, search) {
  await page.goto('/admin/projects?search=' + encodeURIComponent(search));

  const row = page.locator('.table-row').first();
  await expect(row).toBeVisible();

  const href = await row.locator('a.open').getAttribute('href');
  await page.goto(href);
}

test('parcours client complet jusqu’au devis provisoire', async ({ page }) => {
  // ── Étape 1 : coordonnées ───────────────────────────────────────────────
  await page.goto('/questionnaire');
  await expect(page.getByRole('heading', { name: 'Recevez votre devis en 2 minutes' })).toBeVisible();

  await page.fill('#nom', CLIENT_NAME);
  await page.fill('#entreprise', 'E2E Boutique Test');
  await page.fill('#ville', 'Abidjan');
  await page.fill('#tel', '+2250708091011');
  await page.fill('#email', CLIENT_EMAIL);
  await page.getByRole('button', { name: 'Commencer' }).click();
  await page.waitForURL(/\/questionnaire\/step\/2$/);

  // ── Étape 2 : type de projet ────────────────────────────────────────────
  await expect(page.getByRole('heading', { name: 'De quoi avez-vous besoin ?' })).toBeVisible();
  await chooseOption(page, 'type', 'site_vitrine');
  await page.getByRole('button', { name: 'Continuer' }).click();
  await page.waitForURL(/\/questionnaire\/step\/3$/);

  // ── Étape 3 : détail du besoin ──────────────────────────────────────────
  await expect(page.getByRole('heading', { name: 'Détail du besoin' })).toBeVisible();
  await page.fill('#pages', '5');
  await chooseOption(page, 'contenus', 'fournis');
  await chooseOption(page, 'opt[]', 'blog');
  await page.getByRole('button', { name: 'Continuer' }).click();
  await page.waitForURL(/\/questionnaire\/step\/4$/);

  // ── Étape 4 : hébergement et suivi ──────────────────────────────────────
  await expect(page.getByRole('heading', { name: 'Ce qui fait vivre votre outil' })).toBeVisible();
  await chooseOption(page, 'domaine', 'aucun');
  await chooseOption(page, 'hebergement', 'aucun');
  await page.fill('#emails', '0');
  await chooseOption(page, 'maintenance', 'aucune');
  await page.getByRole('button', { name: 'Continuer' }).click();
  await page.waitForURL(/\/questionnaire\/step\/5$/);

  // ── Étape 5 : niveau et délai ───────────────────────────────────────────
  await expect(page.getByRole('heading', { name: 'Quel niveau de finition ?' })).toBeVisible();
  await chooseOption(page, 'niveau', 'STANDARD');
  await chooseOption(page, 'delai', 'normal');
  await page.fill('#message', 'Projet de test du nouveau formulaire.');
  await page.getByRole('button', { name: 'Voir mon devis' }).click();
  await page.waitForURL(/\/questionnaire\/quote$/);

  // ── Devis provisoire ────────────────────────────────────────────────────
  await expect(page.getByText('DEVIS PROVISOIRE')).toBeVisible();
  await expect(page.getByText(/DT-\d{4}-\d{4}/)).toBeVisible();
  await expect(page.getByText(CLIENT_NAME).first()).toBeVisible();
  await expect(page.getByText('Total à payer TTC')).toBeVisible();

  // Calcul : site vitrine Standard, 5 pages, option blog, sans frais récurrents.
  await expect(page.getByText(/1\s*003\s*000 FCFA/).first()).toBeVisible();
  await expect(page.getByText(/401\s*200 FCFA/).first()).toBeVisible();
  await expect(page.getByText(/jours ouvrés/).first()).toBeVisible();

  // Hors coût de création : aucun frais annuel dans ce scénario.
  await expect(page.getByText(/Frais annuels récurrents/)).toHaveCount(0);
});

test('l’administration affiche les nouvelles questions et réponses', async ({ page }) => {
  await signInToGeneralDashboard(page, GENERAL_ADMIN);
  await openFirstProjectMatching(page, CLIENT_NAME);

  await expect(page.getByText('Détail du besoin')).toBeVisible();
  await expect(page.getByText('Nombre de pages')).toBeVisible();
  await expect(page.getByText('Niveau de finition')).toBeVisible();
  await expect(page.getByText(/STANDARD|Standard/).first()).toBeVisible();

  // Le devis provisoire généré est consultable depuis la fiche projet.
  await expect(page.getByText('Devis provisoire')).toBeVisible();
  await expect(page.getByText(/DT-\d{4}-\d{4}/).first()).toBeVisible();

  // L'ancien questionnaire n'est plus affiché pour un projet du nouveau formulaire.
  await expect(page.getByText('Aucune réponse enregistrée')).toHaveCount(0);
});

test('un prospect historique reste consultable avec ses anciennes réponses', async ({ page }) => {
  await signInToGeneralDashboard(page, GENERAL_ADMIN);
  await openFirstProjectMatching(page, 'E2E Prospect Dev Deux');

  // Réponses créées avec l'ancien questionnaire : elles restent visibles.
  await expect(page.getByText('Quel budget avez-vous prévu ?')).toBeVisible();
  await expect(page.getByText('1 500 000 FCFA')).toBeVisible();

  // Les sections du nouveau formulaire ne s'appliquent pas à l'historique.
  await expect(page.getByText('Niveau de finition')).toHaveCount(0);
});
