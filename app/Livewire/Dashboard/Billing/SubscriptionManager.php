<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Billing;

use App\Models\Plan;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

#[Layout('layouts.app', ['heading' => 'Minha Assinatura', 'subheading' => 'Gerencie seu plano, faturas e formas de pagamento'])]
#[Title('Minha Assinatura')]
class SubscriptionManager extends Component
{
    public ?Plan $proPlan = null;

    /**
     * Get the user's invoices.
     */
    public function getInvoicesProperty(): Collection
    {
        try {
            return auth()->user()->hasStripeId()
                ? auth()->user()->invoices()
                : collect();
        } catch (Exception $e) {
            return collect();
        }
    }

    /**
     * Download a specific invoice.
     */
    public function downloadInvoice(string $invoiceId): mixed
    {
        try {
            Toaster::info('Iniciando o download da fatura...');

            return auth()->user()->downloadInvoice($invoiceId, [
                'vendor' => config('app.name'),
                'product' => 'Assinatura Drafto',
            ]);
        } catch (Exception $e) {
            Toaster::error('Não foi possível baixar a fatura. Tente novamente mais tarde.');

            return null;
        }
    }

    public function mount()
    {
        $user = auth()->user();

        // Sênior: Se for Admin ou Vitalício, buscamos os benefícios do plano PRO para exibir
        if ($user->isAdmin() || $user->is_lifetime) {
            $this->proPlan = Plan::where('slug', 'pro')->first();
        }
    }

    public function render(): View
    {
        $user = auth()->user();

        return view('livewire.dashboard.billing.subscription-manager', [
            'user' => $user,
            'subscription' => $user->subscription(),
            'proPlan' => $this->proPlan,
        ]);
    }
}
