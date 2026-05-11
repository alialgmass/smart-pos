<?php

namespace Modules\Inventory\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Modules\Identity\Database\Seeders\TenantPermissionSeeder;
use Modules\Inventory\Jobs\CheckLowStockJob;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Notifications\LowStockNotification;
use Modules\Tenancy\Models\Tenant;
use Tests\TestCase;

class LowStockNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_low_stock_job_dispatches_notifications(): void
    {
        Notification::fake();

        $tenant = Tenant::factory()->create();
        app(TenantPermissionSeeder::class)->seedForTenant($tenant);

        $admin = User::factory()->create(['tenant_id' => $tenant->id]);

        Product::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Low Stock Item',
            'stock_qty' => 2,
            'min_stock' => 5,
        ]);

        Product::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Well Stocked Item',
            'stock_qty' => 50,
            'min_stock' => 5,
        ]);

        $job = new CheckLowStockJob;
        $job->handle();

        Notification::assertSentTo(
            [$admin],
            LowStockNotification::class,
        );
    }

    public function test_low_stock_notification_has_correct_data(): void
    {
        $product = Product::factory()->make([
            'id' => 1,
            'name' => 'Test Product',
            'stock_qty' => 2,
            'min_stock' => 5,
        ]);

        $notification = new LowStockNotification($product);
        $user = User::factory()->make();
        $data = $notification->toArray($user);

        $this->assertSame('Test Product', $data['product_name']);
        $this->assertSame(2, (int) $data['current_stock']);
        $this->assertSame(5, (int) $data['min_stock']);
    }
}
