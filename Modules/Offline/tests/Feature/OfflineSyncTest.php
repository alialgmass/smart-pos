<?php

namespace Modules\Offline\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Billing\Models\Plan;
use Modules\Offline\Actions\ProcessOfflineSaleAction;
use Modules\Sales\Models\Sale;
use Modules\Shared\Enums\OfflineSyncStatus;
use Modules\Tenancy\Models\Tenant;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OfflineSyncTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_processes_a_batch_of_offline_sales(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->actingAs($user);

        $action = app(ProcessOfflineSaleAction::class);

        $result1 = $action->execute([
            'offline_local_id' => 'offline-001',
            'subtotal' => 100.00,
            'total' => 114.00,
            'paid_amount' => 114.00,
            'payment_method' => 1,
        ], $tenant->id);

        $this->assertSame(OfflineSyncStatus::Ok, $result1['status']);
        $this->assertDatabaseHas('sales', ['offline_local_id' => 'offline-001']);
    }

    #[Test]
    public function it_skips_duplicate_offline_local_id(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->actingAs($user);

        Sale::factory()->create([
            'tenant_id' => $tenant->id,
            'offline_local_id' => 'offline-002',
        ]);

        $action = app(ProcessOfflineSaleAction::class);

        $result = $action->execute([
            'offline_local_id' => 'offline-002',
            'subtotal' => 50.00,
            'total' => 57.00,
            'paid_amount' => 57.00,
            'payment_method' => 1,
        ], $tenant->id);

        $this->assertSame(OfflineSyncStatus::Skipped, $result['status']);
    }

    #[Test]
    public function it_rejects_sale_without_offline_local_id(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->actingAs($user);

        $action = app(ProcessOfflineSaleAction::class);

        $result = $action->execute([
            'subtotal' => 50.00,
            'total' => 57.00,
            'paid_amount' => 57.00,
        ], $tenant->id);

        $this->assertSame(OfflineSyncStatus::Error, $result['status']);
    }

    #[Test]
    public function it_returns_per_item_statuses_from_controller(): void
    {
        $tenant = Tenant::factory()->create();
        Plan::factory()->create(); // needed for subscription factory but not here
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->actingAs($user);

        Sale::factory()->create([
            'tenant_id' => $tenant->id,
            'offline_local_id' => 'existing-001',
        ]);

        $response = $this->postJson(route('api.offline.sync'), [
            'sales' => [
                [
                    'offline_local_id' => 'new-001',
                    'subtotal' => 100,
                    'total' => 114,
                    'paid_amount' => 114,
                    'payment_method' => 1,
                ],
                [
                    'offline_local_id' => 'existing-001',
                    'subtotal' => 50,
                    'total' => 57,
                    'paid_amount' => 57,
                    'payment_method' => 1,
                ],
            ],
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'results' => [
                '*' => ['offline_local_id', 'status', 'message'],
            ],
            'summary' => ['total', 'synced', 'skipped', 'errors'],
        ]);

        $this->assertSame(1, $response->json('summary.synced'));
        $this->assertSame(1, $response->json('summary.skipped'));
    }

    #[Test]
    public function it_requires_authentication(): void
    {
        $this->postJson('/api/v1/offline/sync', ['sales' => []])
            ->assertUnauthorized();
    }
}
