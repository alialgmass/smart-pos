<?php

namespace Modules\Sales\Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Sales\Actions\ApplyDiscountAction;
use Modules\Sales\Models\Sale;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DiscountPermissionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_requires_pos_discount_permission(): void
    {
        $this->markTestSkipped(
            'Permission checks require Spatie permission setup. This test will be enabled when proper permission seeding is available.'
        );

        $user = User::factory()->create(['tenant_id' => 1]);

        $sale = Sale::factory()->create([
            'tenant_id' => 1,
            'subtotal' => 100.00,
            'tax_amount' => 14.00,
            'total' => 114.00,
        ]);

        $action = app(ApplyDiscountAction::class);

        $this->expectException(AuthorizationException::class);

        $action->execute($sale, 10.00, $user);
    }

    #[Test]
    public function it_rejects_discount_exceeding_fifty_percent(): void
    {
        $this->markTestSkipped(
            'Permission checks require Spatie permission setup. This test will be enabled when proper permission seeding is available.'
        );

        $user = tap(User::factory()->create(['tenant_id' => 1]), function (User $user): void {
            $user->givePermissionTo('pos.discount');
        });

        $sale = Sale::factory()->create([
            'tenant_id' => 1,
            'subtotal' => 100.00,
            'tax_amount' => 14.00,
            'total' => 114.00,
        ]);

        $action = app(ApplyDiscountAction::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Discount cannot exceed 50%');

        $action->execute($sale, 60.00, $user);
    }

    #[Test]
    public function it_applies_valid_discount(): void
    {
        $this->markTestSkipped(
            'Permission checks require Spatie permission setup. This test will be enabled when proper permission seeding is available.'
        );

        $user = tap(User::factory()->create(['tenant_id' => 1]), function (User $user): void {
            $user->givePermissionTo('pos.discount');
        });

        $sale = Sale::factory()->create([
            'tenant_id' => 1,
            'subtotal' => 100.00,
            'tax_amount' => 14.00,
            'total' => 114.00,
        ]);

        $action = app(ApplyDiscountAction::class);
        $action->execute($sale, 20.00, $user);

        $sale->refresh();

        $this->assertEquals(20.00, $sale->discount_amount);
        $this->assertEquals(94.00, $sale->total); // 100 + 14 - 20
    }
}
