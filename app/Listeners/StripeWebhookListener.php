<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\Plan;
use App\Models\User;
use Laravel\Cashier\Events\WebhookHandled;

class StripeWebhookListener
{
    public function handle(WebhookHandled $event): void
    {
        $payload = $event->payload;
        $type = $payload['type'];

        if (in_array($type, ['customer.subscription.created', 'customer.subscription.updated', 'customer.subscription.deleted'], true)) {
            $stripeId = $payload['data']['object']['customer'];
            $user = User::where('stripe_id', $stripeId)->first();

            if (!$user) {
                return;
            }

            $this->updateUserPlanId($user);
        }
    }

    private function updateUserPlanId(User $user): void
    {
        $planSlug = 'free';

        if ($user->subscribed('pro')) {
            $planSlug = 'pro';
        } elseif ($user->subscribed('plus')) {
            $planSlug = 'plus';
        }

        $plan = Plan::where('slug', $planSlug)->first();

        if ($plan && $user->plan_id !== $plan->id) {
            $user->update(['plan_id' => $plan->id]);
        }
    }
}
