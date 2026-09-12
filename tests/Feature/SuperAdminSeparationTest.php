<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminSeparationTest extends TestCase
{
    use RefreshDatabase;

    private const PROMOTION_MIGRATION = '2026_09_12_154216_promote_admin_users_to_super_admin.php';

    private function createAdmin(string $email = 'admin@test.com'): User
    {
        return User::factory()->create(['email' => $email, 'role' => 'admin']);
    }

    // ── Rôles distincts ─────────────────────────────────────────────────────

    public function test_general_admin_role_keeps_the_general_dashboard_without_the_private_space(): void
    {
        $admin = $this->createAdmin();

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isSuperAdmin());

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/super-admin')->assertForbidden();
    }

    public function test_nothing_is_promoted_by_default(): void
    {
        $this->assertSame([], config('private_spaces.super_admin_emails'));
    }

    // ── Migration : aucune promotion automatique ────────────────────────────

    public function test_promotion_migration_leaves_every_administrator_untouched(): void
    {
        $first = $this->createAdmin('admin1@test.com');
        $second = $this->createAdmin('admin2@test.com');

        $this->promotionMigration()->up();

        $this->assertSame('admin', $first->fresh()->role);
        $this->assertSame('admin', $second->fresh()->role);
    }

    public function test_promotion_migration_only_promotes_the_designated_emails(): void
    {
        $designated = $this->createAdmin('owner@test.com');
        $other = $this->createAdmin('admin2@test.com');

        config(['private_spaces.super_admin_emails' => ['owner@test.com']]);

        $migration = $this->promotionMigration();
        $migration->up();

        $this->assertSame('super_admin', $designated->fresh()->role);
        $this->assertSame('admin', $other->fresh()->role);

        // Le retour arrière ne redescend que le compte désigné.
        $migration->down();

        $this->assertSame('admin', $designated->fresh()->role);
        $this->assertSame('admin', $other->fresh()->role);
    }

    // ── Désignation explicite par commande ─────────────────────────────────

    public function test_promote_command_designates_an_existing_account(): void
    {
        $admin = $this->createAdmin('owner@test.com');

        $this->artisan('super-admin:promote', ['email' => 'owner@test.com'])->assertSuccessful();

        $this->assertSame('super_admin', $admin->fresh()->role);
        $this->assertTrue($admin->fresh()->isAdmin());
    }

    public function test_promote_command_can_revoke_the_super_admin_role(): void
    {
        $superAdmin = User::factory()->superAdmin()->create(['email' => 'owner@test.com']);

        $this->artisan('super-admin:promote', ['email' => 'owner@test.com', '--revoke' => true])
            ->assertSuccessful();

        $this->assertSame('admin', $superAdmin->fresh()->role);
        // Le compte garde l'accès au dashboard général.
        $this->actingAs($superAdmin->fresh())->get('/admin')->assertOk();
    }

    public function test_promote_command_fails_for_an_unknown_email(): void
    {
        $this->artisan('super-admin:promote', ['email' => 'inconnu@test.com'])->assertFailed();

        $this->assertDatabaseCount('users', 0);
    }

    // ── Aucune élévation depuis le dashboard général ────────────────────────

    public function test_general_admin_cannot_create_a_super_admin_account(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Escalade',
            'email' => 'escalade@test.com',
            'password' => 'Password2026!',
            'password_confirmation' => 'Password2026!',
            'role' => 'super_admin',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'escalade@test.com']);
    }

    public function test_general_admin_cannot_manage_a_super_admin_account(): void
    {
        $admin = $this->createAdmin();
        $superAdmin = User::factory()->superAdmin()->create(['name' => 'Super Admin']);

        $this->actingAs($admin)
            ->patch('/admin/users/'.$superAdmin->id, ['role' => 'admin'])
            ->assertForbidden();

        $this->actingAs($admin)
            ->patch('/admin/users/'.$superAdmin->id.'/toggle-active')
            ->assertForbidden();

        $this->assertSame('super_admin', $superAdmin->fresh()->role);
        $this->assertTrue($superAdmin->fresh()->is_active);
    }

    public function test_users_page_only_offers_the_super_admin_role_to_a_super_admin(): void
    {
        $admin = $this->createAdmin();
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk()
            ->assertDontSee('value="super_admin"', false);

        $this->actingAs($superAdmin)
            ->get('/admin/users')
            ->assertOk()
            ->assertSee('value="super_admin"', false);
    }

    public function test_super_admin_can_grant_the_super_admin_role(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->post('/admin/users', [
            'name' => 'Second Super Admin',
            'email' => 'second-super@test.com',
            'password' => 'Password2026!',
            'password_confirmation' => 'Password2026!',
            'role' => 'super_admin',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'second-super@test.com',
            'role' => 'super_admin',
        ]);
    }

    // ── Seeder : aucun secret versionné, aucune promotion automatique ────────

    public function test_database_seeder_creates_nothing_without_a_configured_password(): void
    {
        config(['admin.password' => null]);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_database_seeder_creates_a_general_admin_and_never_a_super_admin(): void
    {
        config(['admin.password' => 'MotDePasseSeed2026!']);

        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', config('admin.email'))->firstOrFail();

        $this->assertSame('admin', $admin->role);
        $this->assertFalse($admin->isSuperAdmin());
        $this->assertTrue($admin->isActive());
    }

    public function test_database_seeder_never_overwrites_an_existing_account(): void
    {
        config(['admin.password' => 'MotDePasseSeed2026!']);

        $existing = User::factory()->superAdmin()->create([
            'email' => config('admin.email'),
            'name' => 'Super Admin Existant',
        ]);

        $this->seed(DatabaseSeeder::class);

        // Le rôle désigné explicitement n'est jamais rétrogradé par le seeder.
        $this->assertSame('super_admin', $existing->fresh()->role);
        $this->assertSame('Super Admin Existant', $existing->fresh()->name);
    }

    private function promotionMigration(): Migration
    {
        return require database_path('migrations/'.self::PROMOTION_MIGRATION);
    }
}
