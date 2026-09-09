<?php

namespace Tests\Feature;

use App\Models\CompanySetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanySettingsTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::create([
            'name' => 'Admin Settings',
            'email' => 'admin-settings-test@example.com',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_guest_cannot_access_company_settings(): void
    {
        $this->get(route('admin.company-settings.edit'))->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_company_settings(): void
    {
        $user = User::create([
            'name' => 'Simple User',
            'email' => 'simple-settings@example.com',
            'password' => 'password',
            'role' => 'user',
            'is_active' => true,
        ]);

        $this->actingAs($user)->get(route('admin.company-settings.edit'))->assertForbidden();
    }

    public function test_edit_page_renders_and_submits_a_single_color_field(): void
    {
        $user = $this->createAdmin();

        $response = $this->actingAs($user)->get(route('admin.company-settings.edit'));

        $response->assertOk();
        $content = $response->getContent();

        // Chaque couleur ne doit être soumise que par UN champ (le champ texte).
        // Un doublon (ex. color picker + texte avec le même name) écraserait la
        // valeur choisie au submit.
        $this->assertSame(1, substr_count($content, 'name="primary_color"'));
        $this->assertSame(1, substr_count($content, 'name="secondary_color"'));
    }

    public function test_colors_and_commission_are_persisted(): void
    {
        $user = $this->createAdmin();

        $response = $this->actingAs($user)->patch(route('admin.company-settings.update'), [
            'name' => 'Dominium Tech',
            'description' => 'Services digitaux',
            'primary_color' => '#123456',
            'secondary_color' => '#654321',
            'commission_threshold' => 150000,
            'commission_percent' => 25,
            'commission_flat' => 15000,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $company = CompanySetting::get();
        $this->assertSame('#123456', $company->primary_color);
        $this->assertSame('#654321', $company->secondary_color);
        $this->assertSame(150000, $company->commission_threshold);
        $this->assertSame(25, $company->commission_percent);
        $this->assertSame(15000, $company->commission_flat);
    }

    public function test_invalid_color_is_rejected(): void
    {
        $user = $this->createAdmin();

        $response = $this->actingAs($user)
            ->from(route('admin.company-settings.edit'))
            ->patch(route('admin.company-settings.update'), [
                'name' => 'Dominium Tech',
                'primary_color' => 'not-a-color',
            ]);

        $response->assertSessionHasErrors('primary_color');
    }
}
