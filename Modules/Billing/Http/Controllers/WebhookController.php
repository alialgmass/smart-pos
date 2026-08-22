<?php

namespace Modules\Billing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Billing\Actions\HandlePaymentWebhookAction;
use Modules\Billing\Models\Subscription;

class WebhookController extends Controller
{
    public function __construct(
        private readonly HandlePaymentWebhookAction $handleWebhook,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'subscription_id' => 'required|integer|exists:subscriptions,id',
            'status' => 'required|string|in:pending,succeeded,completed,failed,cancelled',
        ]);

        $subscription = Subscription::query()->findOrFail($payload['subscription_id']);

        $this->handleWebhook->execute($payload, $subscription);

        return response()->json(['message' => 'Webhook handled.']);
    }
}
