import { expect, test } from '@playwright/test';
import { ASSIGNED_PROSPECT, GENERAL_ADMIN, UNASSIGNED_PROSPECT, signInToGeneralDashboard } from './support.js';

/**
 * Smoke test : le dashboard général (déjà en ligne) ne doit pas être cassé par
 * les espaces privés.
 */
test('le dashboard général reste fonctionnel et n\'expose aucun espace privé', async ({ page }) => {
  await signInToGeneralDashboard(page, GENERAL_ADMIN);
  await expect(page.getByRole('heading', { name: 'Tableau de bord' })).toBeVisible();

  const sidebar = page.locator('#adminSidebar');

  // Le nom accessible des liens inclut l'icône Font Awesome : correspondance partielle.
  for (const label of ['Dashboard', 'Projets', 'Prospects', 'Clients', 'Commentaires', 'Devis', 'Utilisateurs']) {
    await expect(sidebar.getByRole('link', { name: label })).toBeVisible();
  }

  // Aucune entrée des espaces privés dans la sidebar générale.
  await expect(sidebar.getByText('Vue globale')).toHaveCount(0);
  await expect(sidebar.getByText('Mes suivis')).toHaveCount(0);

  for (const path of ['/admin/projects', '/admin/clients', '/admin/client-clients', '/admin/comments', '/admin/quotes', '/admin/users']) {
    const response = await page.goto(path);
    expect(response.status(), `${path} doit répondre 200`).toBe(200);
  }

  // Les prospects issus des demandes existantes sont toujours listés.
  await page.goto('/admin/clients');
  await expect(page.getByText(ASSIGNED_PROSPECT).first()).toBeVisible();
  await expect(page.getByText(UNASSIGNED_PROSPECT).first()).toBeVisible();

  // Un admin général n'atteint jamais l'espace privé Super Admin.
  const privateSpace = await page.goto('/super-admin');
  expect(privateSpace.status()).toBe(403);
});
