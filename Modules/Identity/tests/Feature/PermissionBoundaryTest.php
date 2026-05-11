<?php

namespace Modules\Identity\Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Modules\Identity\Database\Seeders\TenantPermissionSeeder;
use Modules\Identity\Policies\UserPolicy;
use Modules\Tenancy\Models\Tenant;
use Tests\TestCase;

class PermissionBoundaryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('tenants')) {
            Schema::create('tenants', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->json('settings')->nullable();
                $table->unsignedBigInteger('plan_id')->nullable();
                $table->timestamp('trial_ends_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function test_admin_only_policy_bounds_apply_within_the_same_tenant(): void
    {
        $tenant = Tenant::factory()->create();

        app(TenantPermissionSeeder::class)->seedForTenant($tenant);

        $admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'is_active' => true,
        ]);
        $admin->assignRole('Admin');

        $cashier = User::factory()->create([
            'tenant_id' => $tenant->id,
            'is_active' => true,
        ]);
        $cashier->assignRole('Cashier');

        $policy = app(UserPolicy::class);

        $this->assertTrue($policy->viewAny($admin));
        $this->assertFalse($policy->viewAny($cashier));
        $this->assertFalse($policy->toggleActive($admin, $admin));
        $this->assertTrue($policy->toggleActive($admin, $cashier));
    }
}
