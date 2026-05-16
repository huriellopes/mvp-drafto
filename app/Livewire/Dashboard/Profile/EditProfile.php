<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Profile;

use App\Livewire\Forms\Dashboard\ProfileForm;
use App\Services\IbgeService;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;
use Throwable;

#[Layout('layouts.app', ['heading' => 'Meu Perfil', 'subheading' => 'Personalize sua identidade na plataforma'])]
#[Title('Editar Perfil')]
class EditProfile extends Component
{
    use WithFileUploads;

    public ProfileForm $form;

    public ?string $selectedUf = '';

    public array $ufs = [];

    public function mount(IbgeService $ibge): void
    {
        $this->form->setUser(auth()->user());

        $this->ufs = $ibge->getUfs();

        if ($this->form->location && str_contains($this->form->location, ',')) {
            $parts = explode(',', $this->form->location);
            $this->selectedUf = mb_trim(end($parts));
        }
    }

    #[Computed]
    public function municipios(): array
    {
        if (!$this->selectedUf) {
            return [];
        }

        return app(IbgeService::class)->getMunicipios($this->selectedUf);
    }

    public function updatedSelectedUf(): void
    {
        $this->form->location = '';
    }

    public function save(): void
    {
        try {
            if ($this->selectedUf && $this->form->location) {
                if (!str_contains($this->form->location, ',')) {
                    $this->form->location = "{$this->form->location}, {$this->selectedUf}";
                }
            }

            $this->form->update();
            Toaster::success('Perfil atualizado com sucesso!');
        } catch (ValidationException $e) {
            Toaster::error('Erro de validação. Verifique os campos preenchidos.');

            throw $e;
        } catch (Throwable $e) {
            Toaster::error('Ocorreu um erro ao atualizar o perfil.');

            throw $e;
        }
    }

    public function render(): View
    {
        return view('livewire.dashboard.profile.edit-profile');
    }
}
