<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Billing;

use App\Models\Plan;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class PlansIndex extends Component
{
    public function checkout(string $slug)
    {
        $plan = Plan::query()
            ->where('slug', $slug)
            ->firstOrFail();

        if (empty($plan->stripe_id)) {
            Toaster::error('Este plano ainda não foi configurado no Stripe. Entre em contato com o suporte.');

            return;
        }

        if ($plan->price <= 0) {
            return; // Já é o plano free
        }

        // Sênior: Iniciando o Checkout do Stripe
        return auth()->user()
            ->newSubscription($slug, $plan->stripe_id)
            ->checkout([
                'success_url' => route('dashboard.index', ['checkout' => 'success']),
                'cancel_url' => route('dashboard.billing.plans'),
            ]);
    }

    public function render(): View
    {
        return view('livewire.dashboard.billing.plans-index', [
            'plans' => Plan::query()
                ->where('is_active', true)
                ->orderBy('price')->get(),
        ]);
    }
}
