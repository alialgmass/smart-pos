<?php

namespace Modules\Reports\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Tenancy\Models\Tenant;
use Tests\TestCase;

class ProfitReportAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_profit_report(): void
    {
        $response = $this->get(route('reports.profit'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_without_permission_cannot_access_profit_report(): void
    {
        $this->markTestSkipped(
            'Permission checks require Spatie permission setup. This test will be enabled when proper permission seeding is available.'
        );

        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->get(route('reports.profit'));

        $response->assertForbidden();
    }

    public function test_user_with_permission_can_access_profit_report(): void
    {
        $this->markTestSkipped(
            'Permission checks require Spatie permission setup. This test will be enabled when proper permission seeding is available.'
        );

        $tenant = Tenant::factory()->create();
        $user = tap(User::factory()->create(['tenant_id' => $tenant->id]), function (User $user): void {
            $user->givePermissionTo('reports.profit');
        });

        $response = $this->actingAs($user)->get(route('reports.profit'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Reports/Profit'));
    }
}
