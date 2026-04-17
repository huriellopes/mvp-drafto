<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Admin\Subscriptions;

use App\Models\User;
use Illuminate\View\View;
use Laravel\Cashier\Subscription;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

#[Layout('layouts.app', ['heading' => 'Gestão de Assinaturas', 'subheading' => 'Monitore o faturamento e status dos assinantes'])]
#[Title('Assinaturas')]
#[Lazy]
class SubscriptionIndex extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $status = '';

    #[Url(history: true)]
    public string $type = '';

    public ?int $selectedSubscriptionId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function confirmCancel(int $id): void
    {
        $this->selectedSubscriptionId = $id;
        $this->dispatch('open-modal', name: 'confirm-cancel-subscription');
    }

    public function cancelSubscription(): void
    {
        if (!$this->selectedSubscriptionId) {
            return;
        }

        $subscription = Subscription::findOrFail($this->selectedSubscriptionId);

        // Sênior: Cancelamento imediato via API do Stripe
        $subscription->cancelNow();

        Toaster::success('Assinatura cancelada com sucesso.');
        $this->selectedSubscriptionId = null;
    }

    public function resumeSubscription(int $id): void
    {
        $subscription = Subscription::findOrFail($id);

        if ($subscription->onGracePeriod()) {
            $subscription->resume();
            Toaster::success('Assinatura reativada com sucesso.');
        }
    }

    public function showDetails(int $id): void
    {
        $this->selectedSubscriptionId = $id;
        $this->dispatch('open-modal', name: 'subscription-details');
    }

    #[Computed]
    public function selectedSubscription(): ?Subscription
    {
        if (!$this->selectedSubscriptionId) {
            return null;
        }

        return Subscription::with(['user'])->find($this->selectedSubscriptionId);
    }

    #[Computed]
    public function subscriptions()
    {
        return Subscription::query()
            ->with(['user:id,name,email,stripe_id'])
            ->when($this->search, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->status, fn ($q) => $q->where('stripe_status', $this->status))
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->latest()
            ->paginate(15);
    }

    #[Computed]
    public function stats()
    {
        // Otimização: Uma única query para os status principais se possível,
        // mas o count separado é simples o suficiente.
        // Adicionando cache se necessário no futuro.
        return [
            'active' => Subscription::where('stripe_status', 'active')->count(),
            'trialing' => Subscription::where('stripe_status', 'trialing')->count(),
            'cancelled' => Subscription::whereNotNull('ends_at')->count(),
            'total_users' => User::count(),
        ];
    }

    public function render(): View
    {
        return view('livewire.dashboard.admin.subscriptions.subscription-index');
    }
}
