<?php

namespace Modules\Shared\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Modules\Shared\Concerns\BelongsToTenant;
use Modules\Shared\Support\CurrentTenant;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('tenant_isolation_test_records');
        Schema::create('tenant_isolation_test_records', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('name');
            $table->timestamps();
        });
    }

    #[Test]
    public function tenant_owned_records_are_not_visible_to_other_tenants(): void
    {
        TenantIsolationTestRecord::withoutGlobalScopes()->create([
            'tenant_id' => 1,
            'name' => 'Tenant A product',
        ]);

        TenantIsolationTestRecord::withoutGlobalScopes()->create([
            'tenant_id' => 2,
            'name' => 'Tenant B product',
        ]);

        $this->app->instance(CurrentTenant::class, new CurrentTenant(1, 'Tenant A'));

        $this->assertSame(['Tenant A product'], TenantIsolationTestRecord::query()->pluck('name')->all());

        $this->app->instance(CurrentTenant::class, new CurrentTenant(2, 'Tenant B'));

        $this->assertSame(['Tenant B product'], TenantIsolationTestRecord::query()->pluck('name')->all());
    }
}

class TenantIsolationTestRecord extends Model
{
    use BelongsToTenant;

    protected $table = 'tenant_isolation_test_records';

    protected $fillable = [
        'tenant_id',
        'name',
    ];
}
