<?php

namespace Modules\Tenancy\Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Modules\Tenancy\Actions\RegisterTenantAction;
use Modules\Tenancy\DTOs\RegisterTenantData;
use Modules\Tenancy\Models\Tenant;
use Tests\TestCase;

class TenantRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_tenant_action_creates_tenant_admin_and_sends_verification_notification(): void
    {
        Notification::fake();

        $user = app(RegisterTenantAction::class)->execute(
            new RegisterTenantData(
                storeName: 'Royal Store',
                ownerName: 'Royal Owner',
                email: 'owner@example.com',
                password: 'password',
            ),
        );

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseCount('tenants', 1);
        $this->assertDatabaseCount('users', 1);

        $tenant = Tenant::query()->firstOrFail();

        $this->assertSame($tenant->id, $user->fresh()->tenant_id);
        $this->assertSame('Royal Store', $tenant->name);
        $this->assertSame('Royal Owner', $user->name);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_public_tenant_registration_flow_creates_tenant_admin_and_redirects_to_check_inbox(): void
    {
        Notification::fake();

        $response = $this->post(route('tenant.register.store'), [
            'store_name' => 'Downtown Market',
            'owner_name' => 'Mona Saleh',
            'email' => 'mona@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::query()->where('email', 'mona@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('tenant.register.check-inbox'));
        $this->assertDatabaseHas('tenants', ['name' => 'Downtown Market']);
        $this->assertGreaterThanOrEqual(13, now()->diffInDays($user->tenant->trial_ends_at));
        $this->assertLessThanOrEqual(14, now()->diffInDays($user->tenant->trial_ends_at));

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_tenant_registration_page_is_public(): void
    {
        $this->get(route('tenant.register'))->assertOk();
    }
}
