<?php

namespace Modules\Identity\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Actions\AuthenticateUserAction;
use Modules\Tenancy\Models\Tenant;
use Tests\TestCase;

class LoginSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'tenant_id' => Tenant::factory()->create()->id,
        ]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
            'remember' => true,
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_user_cannot_login_with_wrong_credentials(): void
    {
        $user = User::factory()->create([
            'tenant_id' => Tenant::factory()->create()->id,
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_repeated_failures(): void
    {
        $user = User::factory()->create([
            'tenant_id' => Tenant::factory()->create()->id,
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson(route('login.store'), [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->postJson(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertTooManyRequests();
    }

    public function test_authenticate_action_rejects_inactive_users(): void
    {
        $user = User::factory()->create([
            'tenant_id' => Tenant::factory()->create()->id,
            'is_active' => false,
        ]);

        $authenticated = app(AuthenticateUserAction::class)->execute($user->email, 'password', remember: true);

        $this->assertFalse($authenticated);
        $this->assertGuest();
    }
}
