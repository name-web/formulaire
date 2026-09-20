import { expect, test } from '@playwright/test';
import {
  ASSIGNABLE_PROSPECT,
  COMMERCIAL,
  COMMERCIAL_PROSPECT,
  GENERAL_ADMIN,
  SUPER_ADMIN,
  isoDateInDays,
  signInAsCommercial,
  signInAsSuperAdmin,
  signInToGeneralDashboard,
} from './support.js';

/**
 * Parcours complet du journal d'activité central :
 * le commercial agit, l'Admin général suit ses prospects, le Super Admin suit
 * les commerciaux et attribue les prospects — dans trois sessions distinctes,
 * chacune avec son propre cookie.
 */
test.describe.configure({ mode: 'serial' });

const NOTE = 'E2E journal : premier appel, le prospect est interesse.';

test('le journal central trace les actions du commercial et reste cloisonné', async ({ browser }) => {
  const commercialPage = await browser.newPage();
  const adminPage = await browser.newPage();
  const superAdminPage = await browser.newPage();

  // ── 1. Le commercial consulte sa fiche, note le suivi et programme une relance ──
  await signInAsCommercial(commercialPage, COMMERCIAL);
  await expect(commercialPage.getByRole('heading', { name: 'Tableau de bord' })).toBeVisible();

  await commercialPage.goto('/commercial/prospects');
  const commercialRow = commercialPage.locator('.table-row', { hasText: COMMERCIAL_PROSPECT });
  await expect(commercialRow).toBeVisible();
  const detailUrl = await commercialRow.locator('a.open').getAttribute('href');

  await commercialPage.goto(detailUrl);
  await expect(commercialPage.getByRole('heading', { name: COMMERCIAL_PROSPECT })).toBeVisible();

  await commercialPage.fill('textarea[name="content"]', NOTE);
  await commercialPage.getByRole('button', { name: 'Ajouter le suivi' }).click();
  await expect(commercialPage.getByText(NOTE)).toBeVisible();

  const nextActionDate = isoDateInDays(3);
  await commercialPage.fill('#next_action', 'Envoyer le devis');
  await commercialPage.fill('#next_action_date', nextActionDate);
  await commercialPage.getByRole('button', { name: 'Enregistrer' }).click();
  await expect(commercialPage.locator('#next_action')).toHaveValue('Envoyer le devis');

  // Le commercial reste dans son espace : le journal ne lui est pas ouvert.
  const forbidden = await commercialPage.goto('/admin/activities');
  expect(forbidden.status()).toBe(403);

  // ── 2. L'Admin général comprend ce qui a été fait sans ouvrir la fiche ──
  await signInToGeneralDashboard(adminPage, GENERAL_ADMIN);
  await adminPage.goto('/admin/activities');
  await expect(adminPage.getByRole('heading', { name: 'Activités' })).toBeVisible();

  const journalRow = adminPage.locator('.activity-row', { hasText: NOTE });
  await expect(journalRow).toBeVisible();
  await expect(journalRow).toContainText(COMMERCIAL_PROSPECT);
  await expect(journalRow).toContainText(COMMERCIAL.name);
  await expect(journalRow).toContainText('Note ajoutée');

  // Filtre par type d'activité : seule la relance programmée reste affichée.
  await adminPage.selectOption('#activity-type', 'follow_up_scheduled');
  await adminPage.getByRole('button', { name: 'Filtrer' }).click();
  await expect(adminPage.locator('.activity-row')).toHaveCount(1);
  await expect(adminPage.locator('.activity-row').first()).toContainText('Relance programmée');
  await expect(adminPage.getByText(NOTE)).toHaveCount(0);

  // Aperçu des activités récentes sur le dashboard général.
  await adminPage.goto('/admin');
  await expect(adminPage.getByText('Activités récentes')).toBeVisible();
  await expect(adminPage.getByText('Voir toutes les activités')).toBeVisible();

  // ── 3. Le Super Admin suit le journal global et attribue un prospect ──
  await signInAsSuperAdmin(superAdminPage, SUPER_ADMIN);
  await superAdminPage.goto('/super-admin/activities');

  const globalRow = superAdminPage.locator('.activity-row', { hasText: NOTE });
  await expect(globalRow).toBeVisible();
  await expect(globalRow).toContainText(COMMERCIAL_PROSPECT);
  await expect(globalRow).toContainText(COMMERCIAL.name);

  await superAdminPage.goto('/super-admin/prospects');
  const assignableRow = superAdminPage.locator('.table-row', { hasText: ASSIGNABLE_PROSPECT });
  // La ligne porte deux formulaires (développeur et commercial) : on vise celui du commercial.
  const commercialForm = assignableRow
    .locator('form')
    .filter({ has: superAdminPage.locator('select[name="assigned_commercial_id"]') });
  await commercialForm.locator('select[name="assigned_commercial_id"]').selectOption({ label: COMMERCIAL.name });
  await commercialForm.locator('button[type="submit"]').click();
  await expect(assignableRow).toContainText(COMMERCIAL.name);

  // L'attribution faite par le Super Admin est tracée dans le journal global.
  await superAdminPage.goto('/super-admin/activities');
  await superAdminPage.selectOption('#activity-type', 'assignment_changed');
  await superAdminPage.getByRole('button', { name: 'Filtrer' }).click();

  const assignmentRow = superAdminPage.locator('.activity-row', { hasText: ASSIGNABLE_PROSPECT });
  await expect(assignmentRow).toBeVisible();
  await expect(assignmentRow).toContainText('Attribution modifiée');
  await expect(assignmentRow).toContainText(COMMERCIAL.name);

  // Aperçu des activités récentes sur la vue globale.
  await superAdminPage.goto('/super-admin');
  await expect(superAdminPage.getByText('Activités récentes').first()).toBeVisible();
  await expect(superAdminPage.getByText('Journal global')).toBeVisible();

  await commercialPage.close();
  await adminPage.close();
  await superAdminPage.close();
});
