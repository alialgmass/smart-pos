<?php

namespace Modules\Reports\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Reports\Exports\ReportExport;
use Modules\Tenancy\Models\Tenant;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_generates_csv_with_metadata(): void
    {
        $export = app(ReportExport::class);

        $csv = $export->generate(
            title: 'Top Products',
            rows: [
                ['name' => 'Coffee', 'qty' => '10', 'revenue' => '50.00'],
                ['name' => 'Tea', 'qty' => '5', 'revenue' => '25.00'],
            ],
            headers: ['name' => 'Name', 'qty' => 'Qty', 'revenue' => 'Revenue'],
            dateRange: '2024-01-01 to 2024-01-31',
            tenantName: 'Test Store',
        );

        $this->assertStringContainsString('# Top Products', $csv);
        $this->assertStringContainsString('Tenant: Test Store', $csv);
        $this->assertStringContainsString('Period: 2024-01-01 to 2024-01-31', $csv);
        $this->assertStringContainsString('Generated:', $csv);
        $this->assertStringContainsString('Name,Qty,Revenue', $csv);
        $this->assertStringContainsString('Coffee,10,50.00', $csv);
        $this->assertStringContainsString('Tea,5,25.00', $csv);
    }

    public function test_csv_escapes_fields_with_commas(): void
    {
        $export = app(ReportExport::class);

        $csv = $export->generate(
            title: 'Test',
            rows: [
                ['item' => 'Coffee, Black', 'price' => '3.50'],
            ],
            headers: ['item' => 'Item', 'price' => 'Price'],
            dateRange: 'N/A',
            tenantName: 'Store',
        );

        $this->assertStringContainsString('"Coffee, Black",3.50', $csv);
    }

    public function test_download_requires_authentication(): void
    {
        $response = $this->post(route('reports.export'), [
            'title' => 'Test',
            'rows' => [],
            'headers' => [],
            'date_range' => 'N/A',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_download_csv(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->post(route('reports.export'), [
            'title' => 'Test Report',
            'rows' => [['name' => 'Coffee']],
            'headers' => ['name' => 'Name'],
            'date_range' => 'Jan to Feb',
        ]);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $this->assertStringContainsString('attachment; filename="Test_Report_', $response->headers->get('Content-Disposition'));
    }
}
