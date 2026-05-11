<?php

namespace Modules\Billing\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Billing\Actions\CreatePaymentIntentAction;
use Modules\Billing\Actions\HandlePaymentWebhookAction;
use Modules\Billing\Actions\RenewSubscriptionAction;
use Modules\Billing\Models\Plan;
use Modules\Billing\Models\Subscription;
use Modules\Shared\Enums\SubscriptionStatus;
use Modules\Tenancy\Models\Tenant;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SubscriptionFlowTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_a_payment_intent(): void
    {
        $plan = Plan::factory()->create(['price_monthly' => 49.00]);

        $action = app(CreatePaymentIntentAction::class);
        $intent = $action->execute($plan, 1);

        $this->assertArrayHasKey('client_secret', $intent);
        $this->assertArrayHasKey('amount', $intent);
        $this->assertSame(49.00, (float) $intent['amount']);
    }

    #[Test]
    public function it_renews_a_subscription(): void
    {
        $tenant = Tenant::factory()->create();
        $plan = Plan::factory()->create();

        $subscription = Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::Active,
            'ends_at' => now()->subDay(),
        ]);

        $action = app(RenewSubscriptionAction::class);
        $renewed = $action->execute($subscription);

        $this->assertSame(SubscriptionStatus::Active, $renewed->status);
        $this->assertTrue(now()->lessThan($renewed->ends_at));
    }

    #[Test]
    public function it_handles_successful_payment_webhook(): void
    {
        $tenant = Tenant::factory()->create();
        $plan = Plan::factory()->create();

        $subscription = Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::PastDue,
        ]);

        $action = app(HandlePaymentWebhookAction::class);
        $result = $action->execute(
            ['status' => 'succeeded'],
            $subscription,
        );

        $this->assertSame(SubscriptionStatus::Active, $result->status);
    }

    #[Test]
    public function it_handles_failed_payment_webhook(): void
    {
        $tenant = Tenant::factory()->create();
        $plan = Plan::factory()->create();

        $subscription = Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::Active,
        ]);

        $action = app(HandlePaymentWebhookAction::class);
        $result = $action->execute(
            ['status' => 'failed'],
            $subscription,
        );

        $this->assertSame(SubscriptionStatus::PastDue, $result->status);
    }

    #[Test]
    public function it_creates_subscription_via_checkout_route(): void
    {
        $tenant = Tenant::factory()->create(['trial_ends_at' => now()->addDays(14)]);
        $plan = Plan::factory()->create();

        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->actingAs($user);

        $response = $this->post(route('billing.checkout', $plan));

        $response->assertRedirect(route('billing.index'));

        $this->assertDatabaseHas('subscriptions', [
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
        ]);
    }

    #[Test]
    public function pricing_page_is_public(): void
    {
        Plan::factory()->create(['name' => 'Basic', 'price_monthly' => 19.00]);
        Plan::factory()->create(['name' => 'Pro', 'price_monthly' => 99.00]);

        $response = $this->get(route('billing.pricing'));

        $response->assertOk();
    }

    #[Test]
    public function subscription_index_shows_current_subscription(): void
    {
        $tenant = Tenant::factory()->create();
        $plan = Plan::factory()->create(['name' => 'Advanced']);

        Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::Active,
        ]);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->actingAs($user);

        $response = $this->get(route('billing.index'));
        $response->assertOk();
    }

    #[Test]
    public function webhook_endpoint_updates_subscription(): void
    {
        $tenant = Tenant::factory()->create();
        $plan = Plan::factory()->create();

        $subscription = Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::PastDue,
        ]);

        $response = $this->postJson('/api/billing/webhook', [
            'subscription_id' => $subscription->id,
            'status' => 'succeeded',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'status' => SubscriptionStatus::Active,
        ]);
    }
}
