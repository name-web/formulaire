import { execSync } from 'node:child_process';
import process from 'node:process';

/**
 * Prépare la base E2E avant de démarrer le navigateur.
 *
 * Le serveur E2E tourne avec APP_ENV=testing : la base utilisée est donc
 * form_db_test (celle du phpunit.xml), jamais la base de développement.
 */
export default function globalSetup() {
  const env = { ...process.env, APP_ENV: 'testing' };

  execSync('php artisan migrate:fresh --force', { stdio: 'inherit', env });
  execSync('php artisan db:seed --class=E2ESeeder --force', { stdio: 'inherit', env });
}
