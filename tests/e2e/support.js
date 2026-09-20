/**
 * Comptes déterministes créés par Database\Seeders\E2ESeeder.
 * (voir tests/e2e/global-setup.js pour la préparation de la base).
 */
export const SUPER_ADMIN = { email: 'e2e-super@domitium.test', password: 'E2eSuper2026!' };

export const GENERAL_ADMIN = { email: 'e2e-admin@domitium.test', password: 'E2eAdmin2026!' };

export const DEVELOPER_B = { name: 'E2E Dev Deux', password: 'E2eDevB2026!' };

export const COMMERCIAL = { name: 'E2E Commercial', password: 'E2eCommercial2026!' };

export const ASSIGNED_PROSPECT = 'E2E Prospect Dev Deux';

export const UNASSIGNED_PROSPECT = 'E2E Prospect À Attribuer';

export const NEW_DEVELOPER = 'E2E Dev Un';

export const COMMERCIAL_PROSPECT = 'E2E Prospect Commercial';

export const ASSIGNABLE_PROSPECT = 'E2E Prospect Attribuable';

/** Connexion depuis l'écran de login du dashboard général (email + mot de passe). */
export async function signInToGeneralDashboard(page, { email, password }) {
  await page.goto('/login');
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', password);
  await page.click('button[type="submit"]');
  await page.waitForURL(/\/admin$/);
}

/** Connexion depuis l'écran de login Super Admin (email + mot de passe). */
export async function signInAsSuperAdmin(page, { email, password }) {
  await page.goto('/super-admin/login');
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', password);
  await page.click('button[type="submit"]');
  await page.waitForURL(/\/super-admin$/);
}

/** Connexion depuis l'écran privé développeur (nom + mot de passe). */
export async function signInAsDeveloper(page, { name, password }) {
  await page.goto('/developer/login');
  await page.fill('#developer-name', name);
  await page.fill('#developer-password', password);
  await page.click('button[type="submit"]');
  await page.waitForURL(/\/developer$/);
}

/** Connexion depuis l'écran privé commercial (nom + mot de passe). */
export async function signInAsCommercial(page, { name, password }) {
  await page.goto('/commercial/login');
  await page.fill('#commercial-name', name);
  await page.fill('#commercial-password', password);
  await page.click('button[type="submit"]');
  await page.waitForURL(/\/commercial$/);
}

/** Date au format attendu par les champs <input type="date">. */
export function isoDateInDays(days) {
  const date = new Date();
  date.setDate(date.getDate() + days);

  return date.toISOString().slice(0, 10);
}
