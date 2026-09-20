import { defineConfig, devices } from '@playwright/test';

/**
 * E2E navigateur réel.
 *
 * Le serveur de test est lancé avec APP_ENV=testing : il utilise la base
 * form_db_test (voir tests/e2e/global-setup.js) et ne touche jamais la base de
 * développement. Le navigateur est le Chrome installé sur la machine
 * (`channel: 'chrome'`), aucun téléchargement supplémentaire n'est nécessaire.
 */
const PORT = Number(process.env.E2E_PORT ?? 8123);
/*
 * Le domaine des cookies de session est celui d'APP_URL / SESSION_DOMAIN
 * (`.env.testing`) : si l'application est configurée sur « localhost », le
 * navigateur n'acceptera jamais le cookie émis par « 127.0.0.1 ». L'hôte reste
 * surchargeable pour pouvoir faire tourner la suite sur les deux. 
 */
const HOST = process.env.E2E_HOST ?? '127.0.0.1';
const BASE_URL = `http://${HOST}:${PORT}`;

export default defineConfig({
  testDir: './tests/e2e',
  fullyParallel: false,
  workers: 1,
  forbidOnly: !!process.env.CI,
  retries: 0,
  timeout: 90_000,
  expect: { timeout: 10_000 },
  reporter: [['list']],
  globalSetup: './tests/e2e/global-setup.js',
  use: {
    baseURL: BASE_URL,
    locale: 'fr-FR',
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'], channel: 'chrome' },
    },
  ],
  webServer: {
    command: `php artisan serve --host=${HOST} --port=${PORT}`,
    url: `${BASE_URL}/up`,
    reuseExistingServer: false,
    timeout: 120_000,
    env: {
      ...process.env,
      APP_ENV: 'testing',
      APP_URL: BASE_URL,
      SESSION_DOMAIN: HOST,
    },
  },
});
