<?php

namespace Modules\Settings\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Settings\Actions\UpdateTenantSettingsAction;
use Modules\Settings\DTOs\TenantSettingsData;
use Modules\Tenancy\Models\Tenant;
use Tests\TestCase;

class InvoiceTaxSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_invoice_settings(): void
    {
        $response = $this->get(route('settings.invoice.edit'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_tax_settings(): void
    {
        $response = $this->get(route('settings.tax.edit'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_invoice_settings(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->get(route('settings.invoice.edit'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Settings/Invoice'));
    }

    public function test_authenticated_user_can_update_invoice_settings(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->put(route('settings.invoice.update'), [
            'prefix' => 'INV-',
            'format' => '{prefix}{year}{month}{seq}',
            'show_logo' => true,
            'show_address' => false,
            'footer_text' => 'Thank you',
        ]);

        $response->assertRedirect(route('settings.invoice.edit'));
        $tenant->refresh();

        $this->assertSame('INV-', $tenant->settings['invoice']['prefix']);
        $this->assertFalse($tenant->settings['invoice']['show_address']);
    }

    public function test_authenticated_user_can_update_tax_settings(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->put(route('settings.tax.update'), [
            'rate' => 10.5,
            'name' => 'VAT',
            'apply_to' => 'all',
            'enabled' => true,
        ]);

        $response->assertRedirect(route('settings.tax.edit'));
        $tenant->refresh();

        $this->assertSame(10.5, (float) $tenant->settings['tax']['rate']);
        $this->assertSame('VAT', $tenant->settings['tax']['name']);
        $this->assertTrue($tenant->settings['tax']['enabled']);
    }

    public function test_tenant_settings_dto_can_be_created_from_array(): void
    {
        $dto = TenantSettingsData::fromArray([
            'invoice' => ['prefix' => 'INV-'],
            'tax' => ['rate' => 8.0],
            'printer' => ['type' => 'thermal'],
        ]);

        $this->assertSame('INV-', $dto->invoice['prefix']);
        $this->assertSame(8.0, $dto->tax['rate']);
        $this->assertSame('thermal', $dto->printer['type']);
    }

    public function test_update_action_merges_settings(): void
    {
        $tenant = Tenant::factory()->create([
            'settings' => ['invoice' => ['prefix' => 'OLD-']],
        ]);

        app(UpdateTenantSettingsAction::class)->execute($tenant, [
            'tax' => ['rate' => 5.0],
        ]);

        $tenant->refresh();

        $this->assertSame('OLD-', $tenant->settings['invoice']['prefix']);
        $this->assertEquals(5.0, $tenant->settings['tax']['rate']);
    }
}
