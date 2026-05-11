<?php

namespace Modules\Shared\Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Modules\Shared\Concerns\BelongsToTenant;
use Modules\Shared\Enums\PaymentMethod;
use Modules\Shared\Support\CurrentTenant;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BelongsToTenantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('tenant_scoped_test_records');
        Schema::create('tenant_scoped_test_records', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('name');
            $table->timestamps();
        });
    }

    #[Test]
    public function current_tenant_reports_resolution_state(): void
    {
        $tenant = new CurrentTenant(5, 'Store');

        $this->assertTrue($tenant->isResolved());
        $this->assertSame(5, $tenant->id);
    }

    #[Test]
    public function payment_method_enum_uses_expected_values(): void
    {
        $this->assertSame(1, PaymentMethod::Cash->value);
        $this->assertSame(4, PaymentMethod::Deferred->value);
    }

    #[Test]
    public function tenant_scope_assigns_and_filters_records_by_current_tenant(): void
    {
        $this->app->instance(CurrentTenant::class, new CurrentTenant(10, 'Tenant A'));

        $record = TenantScopedTestRecord::query()->create(['name' => 'Visible']);

        $this->assertSame(10, $record->tenant_id);
        $this->assertSame(1, TenantScopedTestRecord::query()->count());

        TenantScopedTestRecord::withoutGlobalScopes()->create([
            'tenant_id' => 20,
            'name' => 'Hidden',
        ]);

        $this->assertSame(1, TenantScopedTestRecord::query()->count());

        $this->app->instance(CurrentTenant::class, new CurrentTenant(20, 'Tenant B'));

        $this->assertSame('Hidden', TenantScopedTestRecord::query()->sole()->name);
    }
}

class TenantScopedTestRecord extends Model
{
    use BelongsToTenant;

    protected $table = 'tenant_scoped_test_records';

    protected $fillable = [
        'tenant_id',
        'name',
    ];
}
