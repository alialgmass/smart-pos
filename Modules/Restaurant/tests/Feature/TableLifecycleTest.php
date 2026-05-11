<?php

namespace Modules\Restaurant\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Restaurant\Enums\TableStatus;
use Modules\Restaurant\Models\Table;
use Modules\Tenancy\Models\Tenant;
use Tests\TestCase;

class TableLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
    }

    public function test_admin_can_create_table(): void
    {
        $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($admin)->post(route('restaurant.tables.store'), [
            'name' => 'Table 1',
            'capacity' => 4,
        ]);

        $response->assertRedirect(route('restaurant.tables.index'));

        $table = Table::withoutGlobalScope('tenant')
            ->where('name', 'Table 1')
            ->firstOrFail();

        $this->assertSame($this->tenant->id, $table->tenant_id);
        $this->assertSame(4, $table->capacity);
        $this->assertSame(TableStatus::Available, $table->status);
    }

    public function test_admin_can_update_table(): void
    {
        $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $table = Table::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($admin)->put(route('restaurant.tables.update', $table), [
            'name' => 'Updated Table',
            'capacity' => 6,
            'status' => 2,
        ]);

        $response->assertRedirect(route('restaurant.tables.index'));

        $table->fresh();
        $this->assertSame('Updated Table', $table->fresh()->name);
        $this->assertSame(6, $table->fresh()->capacity);
        $this->assertSame(TableStatus::Occupied, $table->fresh()->status);
    }

    public function test_admin_can_delete_table(): void
    {
        $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $table = Table::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($admin)->delete(route('restaurant.tables.destroy', $table));

        $response->assertRedirect(route('restaurant.tables.index'));

        $this->assertModelMissing($table);
    }

    public function test_table_validation_fails_without_name(): void
    {
        $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($admin)->post(route('restaurant.tables.store'), [
            'name' => '',
            'capacity' => 4,
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_table_validation_fails_without_capacity(): void
    {
        $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($admin)->post(route('restaurant.tables.store'), [
            'name' => 'Table 1',
            'capacity' => '',
        ]);

        $response->assertSessionHasErrors(['capacity']);
    }

    public function test_table_starts_as_available(): void
    {
        $table = Table::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->assertSame(TableStatus::Available, $table->status);
    }
}
