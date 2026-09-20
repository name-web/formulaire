import { expect, test } from '@playwright/test';
import {
  ASSIGNED_PROSPECT,
  DEVELOPER_B,
  NEW_DEVELOPER,
  SUPER_ADMIN,
  UNASSIGNED_PROSPECT,
  isoDateInDays,
  signInAsDeveloper,
  signInAsSuperAdmin,
} from './support.js';

test.describe.configure({ mode: 'serial' });

test('parcours complet : Super Admin → développeur → suivi → conversion', async ({ page, browser }) => {
  // ── 1. Connexion Super Admin ────────────────────────────────────────────
  await signInAsSuperAdmin(page, SUPER_ADMIN);

  // ── 2. Accès au dashboard Super Admin privé ─────────────────────────────
  await page.goto('/super-admin');
  await expect(page.getByRole('heading', { name: 'Super Admin' })).toBeVisible();
  await expect(page.getByRole('link', { name: 'Vue globale' })).toBeVisible();

  // ── 3. Création d'un développeur (nom uniquement) ───────────────────────
  await page.goto('/super-admin/developers');
  await page.fill('#developer-name', NEW_DEVELOPER);
  await page.getByRole('button', { name: 'Créer le développeur' }).click();

  const generatedPasswordField = page.locator('#generatedPassword');
  await expect(generatedPasswordField).toBeVisible();
  const generatedPassword = (await generatedPasswordField.textContent()).trim();
  expect(generatedPassword.length).toBeGreaterThanOrEqual(12);

  // ── 4. Attribution d'un prospect existant au développeur créé ───────────
  await page.goto('/super-admin/prospects');

  const unassignedRow = () => page.locator('.table-row', { hasText: UNASSIGNED_PROSPECT });
  // Fiche telle que consultée depuis l'espace Super Admin.
  const superAdminDetailUrl = await unassignedRow().locator('a.open').getAttribute('href');
  const prospectId = superAdminDetailUrl.split('/').pop();
  // Fiche telle que consultée depuis l'espace développeur.
  const developerDetailUrl = `/developer/prospects/${prospectId}`;

  const foreignProspectId = (await page.locator('.table-row', { hasText: ASSIGNED_PROSPECT })
    .locator('a.open').getAttribute('href')).split('/').pop();

  await unassignedRow().locator('select[name="assigned_developer_id"]').selectOption({ label: NEW_DEVELOPER });
  // La ligne porte deux formulaires (développeur et commercial) : on cible celui du développeur.
  const developerForm = unassignedRow()
    .locator('form')
    .filter({ has: page.locator('select[name="assigned_developer_id"]') });
  await developerForm.locator('button[type="submit"]').click();

  await expect(unassignedRow()).toContainText(NEW_DEVELOPER);
  await expect(unassignedRow().locator('select[name="assigned_developer_id"] option:checked')).toHaveText(NEW_DEVELOPER);

  // ── 5. Connexion développeur avec le mot de passe généré ────────────────
  const developerA = await browser.newPage();
  await signInAsDeveloper(developerA, { name: NEW_DEVELOPER, password: generatedPassword });

  // ── 6. Le prospect attribué apparaît chez le bon développeur ────────────
  await expect(developerA.getByRole('heading', { name: 'Mes suivis' })).toBeVisible();
  await expect(developerA.getByText(UNASSIGNED_PROSPECT).first()).toBeVisible();

  // ── 7. Isolation : aucun accès au portefeuille d'un autre développeur ───
  await developerA.goto('/developer/prospects');
  await expect(developerA.getByText(ASSIGNED_PROSPECT)).toHaveCount(0);

  const foreignAccess = await developerA.goto(`/developer/prospects/${foreignProspectId}`);
  expect(foreignAccess.status()).toBe(404);

  // ── 8. Suivi : commentaire puis prochaine action ────────────────────────
  await developerA.goto(developerDetailUrl);
  const followUpComment = 'E2E suivi : premier appel, le prospect demande un devis.';
  await developerA.fill('textarea[name="content"]', followUpComment);
  await developerA.getByRole('button', { name: 'Ajouter le suivi' }).click();
  await expect(developerA.getByText(followUpComment)).toBeVisible();

  const nextActionDate = isoDateInDays(2);
  await developerA.fill('#next_action', 'Envoyer le devis');
  await developerA.fill('#next_action_date', nextActionDate);
  await developerA.getByRole('button', { name: 'Enregistrer' }).click();
  await expect(developerA.locator('#next_action')).toHaveValue('Envoyer le devis');
  await expect(developerA.locator('#next_action_date')).toHaveValue(nextActionDate);

  // Le suivi planifié remonte dans son tableau de bord.
  await developerA.goto('/developer');
  await expect(developerA.getByText(UNASSIGNED_PROSPECT).first()).toBeVisible();

  // ── 9. Conversion prospect → client, historique conservé ────────────────
  await developerA.goto(developerDetailUrl);
  developerA.on('dialog', (dialog) => dialog.accept());
  await developerA.getByRole('button', { name: 'Rendre client' }).click();

  await expect(developerA.getByText('CLIENT').first()).toBeVisible();
  await expect(developerA.getByText(followUpComment)).toBeVisible();

  // ── 10. Le Super Admin voit la conversion, l'historique et son espace ───
  await page.goto('/super-admin/prospects');
  const convertedRow = page.locator('.table-row', { hasText: UNASSIGNED_PROSPECT });
  await expect(convertedRow).toContainText('CLIENT');
  await expect(convertedRow).toContainText(NEW_DEVELOPER);

  await page.goto(superAdminDetailUrl);
  await expect(page.getByText('CLIENT').first()).toBeVisible();
  await expect(page.getByText(followUpComment)).toBeVisible();

  // La fiche consultée depuis le Super Admin reste dans son espace.
  await expect(page.getByRole('link', { name: 'Retour' }))
    .toHaveAttribute('href', /\/super-admin\/prospects$/);

  await developerA.close();
});

test("un développeur ne voit jamais le portefeuille d'un autre développeur", async ({ page }) => {
  await signInAsDeveloper(page, DEVELOPER_B);

  // Il retrouve son propre prospect…
  await expect(page.getByText(ASSIGNED_PROSPECT).first()).toBeVisible();
  // …et jamais celui attribué à un autre.
  await expect(page.getByText(UNASSIGNED_PROSPECT)).toHaveCount(0);

  await page.goto('/developer/prospects');
  await expect(page.getByText(UNASSIGNED_PROSPECT)).toHaveCount(0);

  // Ni le dashboard général, ni l'espace Super Admin ne lui sont ouverts.
  const generalSpace = await page.goto('/admin');
  expect(generalSpace.status()).toBe(403);

  const privateSpace = await page.goto('/super-admin');
  expect(privateSpace.status()).toBe(403);
});
