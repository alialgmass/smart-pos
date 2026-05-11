<?php

namespace Modules\Offline\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Offline\Actions\ProcessOfflineSaleAction;
use Modules\Shared\Enums\OfflineSyncStatus;
use Modules\Tenancy\Models\Tenant;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OfflineIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function same_sale_can_be_sent_multiple_times_without_duplicates(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->actingAs($user);

        $action = app(ProcessOfflineSaleAction::class);

        $data = [
            'offline_local_id' => 'idempotent-001',
            'subtotal' => 100.00,
            'total' => 114.00,
            'paid_amount' => 114.00,
            'payment_method' => 1,
        ];

        $first = $action->execute($data, $tenant->id);
        $this->assertSame(OfflineSyncStatus::Ok, $first['status']);

        $second = $action->execute($data, $tenant->id);
        $this->assertSame(OfflineSyncStatus::Skipped, $second['status']);

        $third = $action->execute($data, $tenant->id);
        $this->assertSame(OfflineSyncStatus::Skipped, $third['status']);

        $this->assertDatabaseCount('sales', 1);
    }

    #[Test]
    public function different_tenants_can_use_same_offline_local_id(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        $userA = User::factory()->create(['tenant_id' => $tenantA->id]);
        $userB = User::factory()->create(['tenant_id' => $tenantB->id]);

        $action = app(ProcessOfflineSaleAction::class);

        $data = [
            'offline_local_id' => 'same-local-id',
            'subtotal' => 100.00,
            'total' => 114.00,
            'paid_amount' => 114.00,
            'payment_method' => 1,
        ];

        $this->actingAs($userA);
        $resultA = $action->execute($data, $tenantA->id);
        $this->assertSame(OfflineSyncStatus::Ok, $resultA['status']);

        $this->actingAs($userB);
        $resultB = $action->execute($data, $tenantB->id);
        $this->assertSame(OfflineSyncStatus::Ok, $resultB['status']);

        $this->assertDatabaseCount('sales', 2);
    }
}
