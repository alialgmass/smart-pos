<?php

namespace Modules\Customers\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Customers\Actions\RecordDebtAction;
use Modules\Customers\Enums\CustomerDebtStatus;
use Modules\Customers\Models\Customer;
use Modules\Customers\Models\CustomerDebt;
use Modules\Identity\Database\Seeders\TenantPermissionSeeder;
use Modules\Tenancy\Models\Tenant;
use Tests\TestCase;

class DeferredSaleDebtTest extends TestCase
{
    use RefreshDatabase;

    public function test_deferred_sale_creates_debt(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantPermissionSeeder::class)->seedForTenant($tenant);

        $admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $admin->assignRole('Admin');

        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);

        $action = app(RecordDebtAction::class);
        $debt = $action->execute($customer->id, null, 250.00);

        $this->assertInstanceOf(CustomerDebt::class, $debt);
        $this->assertSame($customer->id, $debt->customer_id);
        $this->assertSame(250.00, (float) $debt->amount);
        $this->assertSame(CustomerDebtStatus::Open, $debt->status);

        $customer->refresh();
        $this->assertSame(250.00, (float) $customer->debt_balance);
    }

    public function test_multiple_debts_increase_balance(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantPermissionSeeder::class)->seedForTenant($tenant);

        $admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $admin->assignRole('Admin');

        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);

        $action = app(RecordDebtAction::class);
        $action->execute($customer->id, null, 100.00);
        $action->execute($customer->id, null, 50.00);

        $customer->refresh();
        $this->assertSame(150.00, (float) $customer->debt_balance);

        $this->assertCount(2, CustomerDebt::where('customer_id', $customer->id)->get());
    }
}
