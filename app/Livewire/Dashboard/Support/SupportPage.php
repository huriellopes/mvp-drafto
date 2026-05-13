<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Support;

use App\Actions\Support\SendSupportMessageAction;
use App\Enums\ModuleEnum;
use App\Livewire\Forms\Dashboard\SupportForm;
use App\Models\Module;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SupportPage extends Component
{
    public SupportForm $form;

    public bool $isFormEnabled = false;

    public string $supportEmail;

    public string $whatsappUrl;

    public function mount(): void
    {
        $this->supportEmail = config('support.email');

        $whatsappNumber = config('support.whatsapp.number');
        $whatsappMessage = urlencode(config('support.whatsapp.message'));

        $this->whatsappUrl = "https://wa.me/{$whatsappNumber}?text={$whatsappMessage}";

        // Sênior: Verifica configuração dinâmica do módulo
        $module = Module::where('slug', ModuleEnum::SUPPORT)->first();
        $this->isFormEnabled = (bool) ($module?->getSetting('enable_contact_form') ?? false);
    }

    public function submit(): void
    {
        if (! $this->isFormEnabled) {
            session()->flash('error', __('dashboard.support.form.error'));

            return;
        }

        $this->validate();

        // Sênior: Uso de Action para lógica de negócio e processamento em segundo plano
        app(SendSupportMessageAction::class)->exec($this->form->toDTO());

        $this->form->reset();
        
        session()->flash('success', __('dashboard.support.form.success'));
    }

    public function render(): View
    {
        return view('livewire.dashboard.support.support-page');
    }
}
