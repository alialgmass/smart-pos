<?php

namespace Modules\Reports\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Reports\Actions\GenerateDashboardMetricsAction;
use Modules\Tenancy\Models\Tenant;
use Tests\TestCase;

class DashboardReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_metrics_returns_all_keys(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $metrics = app(GenerateDashboardMetricsAction::class)->execute($tenant->id);

        $this->assertArrayHasKey('today_sales', $metrics);
        $this->assertArrayHasKey('transaction_count', $metrics);
        $this->assertArrayHasKey('avg_sale', $metrics);
        $this->assertArrayHasKey('top_product', $metrics);
        $this->assertArrayHasKey('low_stock_count', $metrics);
    }

    public function test_dashboard_metrics_returns_zeroes_when_no_data(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $metrics = app(GenerateDashboardMetricsAction::class)->execute($tenant->id);

        $this->assertSame(0, (int) $metrics['today_sales']);
        $this->assertSame(0, $metrics['transaction_count']);
        $this->assertSame(0, (int) $metrics['avg_sale']);
        $this->assertNull($metrics['top_product']);
        $this->assertSame(0, $metrics['low_stock_count']);
    }

    public function test_authenticated_user_can_view_dashboard_page(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->get(route('reports.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Reports/Dashboard'));
    }

    public function test_guest_cannot_view_dashboard_page(): void
    {
        $response = $this->get(route('reports.dashboard'));

        $response->assertRedirect(route('login'));
    }
}
