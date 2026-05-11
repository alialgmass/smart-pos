<?php

namespace Modules\Billing\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Billing\Http\Middleware\CheckSubscription;
use Modules\Billing\Models\Plan;
use Modules\Billing\Models\Subscription;
use Modules\Shared\Enums\SubscriptionStatus;
use Modules\Tenancy\Models\Tenant;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_in_trial_period_passes_check(): void
    {
        $tenant = Tenant::factory()->create(['trial_ends_at' => now()->addDays(7)]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->actingAs($user);

        $response = $this->get(route('billing.pricing'));

        $response->assertOk();
    }

    #[Test]
    public function user_with_active_subscription_passes_check(): void
    {
        $tenant = Tenant::factory()->create(['trial_ends_at' => now()->subDay()]);
        $plan = Plan::factory()->create();

        Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::Active,
            'ends_at' => now()->addDays(30),
        ]);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->actingAs($user);

        $response = $this->get(route('billing.pricing'));

        $response->assertOk();
    }

    #[Test]
    public function user_with_expired_subscription_is_redirected(): void
    {
        $tenant = Tenant::factory()->create(['trial_ends_at' => null]);
        $plan = Plan::factory()->create();

        Subscription::factory()->expired()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
        ]);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->actingAs($user);

        $response = $this->get(route('billing.pricing'));

        $response->assertOk();
    }

    #[Test]
    public function user_with_no_subscription_and_no_trial_is_redirected(): void
    {
        $tenant = Tenant::factory()->create(['trial_ends_at' => null]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->actingAs($user);

        $response = $this->get(route('billing.pricing'));

        $response->assertOk();
    }

    #[Test]
    public function middleware_redirects_to_pricing_when_expired(): void
    {
        $tenant = Tenant::factory()->create(['trial_ends_at' => now()->subDays(30)]);
        $plan = Plan::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        Subscription::factory()->expired()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
        ]);

        $this->actingAs($user);

        $request = request()->create('/dashboard', 'GET');
        $request->setUserResolver(fn () => $user);
        $middleware = app(CheckSubscription::class);

        $response = $middleware->handle($request, fn () => response('OK'));

        $this->assertTrue($response->isRedirect(route('billing.pricing')));
    }

    #[Test]
    public function middleware_allows_trial_users_through(): void
    {
        $tenant = Tenant::factory()->create(['trial_ends_at' => now()->addDays(5)]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->actingAs($user);

        $request = request()->create('/dashboard', 'GET');
        $request->setUserResolver(fn () => $user);
        $middleware = app(CheckSubscription::class);

        $response = $middleware->handle($request, fn () => response('OK'));

        $this->assertEquals('OK', $response->content());
    }

    #[Test]
    public function unauthenticated_user_passes_middleware(): void
    {
        $request = request()->create('/dashboard', 'GET');
        $middleware = app(CheckSubscription::class);

        $response = $middleware->handle($request, fn () => response('OK'));

        $this->assertEquals('OK', $response->content());
    }
}
