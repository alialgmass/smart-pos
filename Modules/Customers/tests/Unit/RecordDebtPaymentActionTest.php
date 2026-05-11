<?php

namespace Modules\Customers\Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Customers\Actions\RecordDebtAction;
use Modules\Customers\Actions\RecordDebtPaymentAction;
use Modules\Customers\Enums\CustomerDebtStatus;
use Modules\Customers\Models\Customer;
use Modules\Customers\Models\DebtPayment;
use Modules\Identity\Database\Seeders\TenantPermissionSeeder;
use Modules\Shared\Enums\PaymentMethod;
use Modules\Tenancy\Models\Tenant;
use Tests\TestCase;

class RecordDebtPaymentActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_debt_payment_updates_balances(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantPermissionSeeder::class)->seedForTenant($tenant);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $user->assignRole('Admin');
        $this->actingAs($user);

        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);

        $recordDebt = app(RecordDebtAction::class);
        $debt = $recordDebt->execute($customer->id, null, 200.00);

        $this->assertSame(200.00, (float) $customer->fresh()->debt_balance);

        $paymentAction = app(RecordDebtPaymentAction::class);
        $payment = $paymentAction->execute($debt->id, 80.00, PaymentMethod::Cash->value);

        $this->assertInstanceOf(DebtPayment::class, $payment);
        $this->assertSame(80.00, (float) $payment->amount);

        $debt->refresh();
        $this->assertSame(80.00, (float) $debt->paid_amount);
        $this->assertSame(CustomerDebtStatus::PartialPaid, $debt->status);

        $this->assertSame(120.00, (float) $customer->fresh()->debt_balance);
    }

    public function test_full_payment_marks_debt_as_paid(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantPermissionSeeder::class)->seedForTenant($tenant);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $user->assignRole('Admin');
        $this->actingAs($user);

        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);

        $recordDebt = app(RecordDebtAction::class);
        $debt = $recordDebt->execute($customer->id, null, 200.00);

        $paymentAction = app(RecordDebtPaymentAction::class);
        $paymentAction->execute($debt->id, 200.00, PaymentMethod::Cash->value);

        $debt->refresh();
        $this->assertSame(CustomerDebtStatus::Paid, $debt->status);
        $this->assertSame(200.00, (float) $debt->paid_amount);
        $this->assertSame(0.00, (float) $customer->fresh()->debt_balance);
    }

    public function test_payment_exceeding_debt_throws_exception(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantPermissionSeeder::class)->seedForTenant($tenant);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $user->assignRole('Admin');
        $this->actingAs($user);

        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);

        $recordDebt = app(RecordDebtAction::class);
        $debt = $recordDebt->execute($customer->id, null, 100.00);

        $paymentAction = app(RecordDebtPaymentAction::class);

        $this->expectException(\RuntimeException::class);

        $paymentAction->execute($debt->id, 150.00, PaymentMethod::Cash->value);
    }
}
