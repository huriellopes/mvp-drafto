<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\Plan;
use App\Models\User;
use App\Notifications\Billing\SubscriptionSuccessNotification;
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

        if ($plan) {
            $oldPlanId = $user->plan_id;
            $isPaidPlan = in_array($planSlug, ['pro', 'plus'], true);

            $updateData = ['plan_id' => $plan->id];

            // Sênior: Se o usuário assinou um plano pago (Pro ou Plus), resetamos o trial
            if ($isPaidPlan && $user->trial_ends_at !== null) {
                $updateData['trial_ends_at'] = null;
            }

            if ($user->plan_id !== $plan->id || array_key_exists('trial_ends_at', $updateData)) {
                $user->update($updateData);

                // Sênior: Se o usuário subiu de nível (não era pro/plus e agora é), enviamos a notificação
                if ($isPaidPlan && ($oldPlanId === null || $oldPlanId !== $plan->id)) {
                    $user->notify(new SubscriptionSuccessNotification($plan->name));
                }
            }
        }
    }
}
