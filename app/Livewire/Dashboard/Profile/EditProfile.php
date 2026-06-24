<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Profile;

use App\Actions\Profile\UpdateProfileMediaAction;
use App\Livewire\Forms\Dashboard\ProfileForm;
use App\Services\IbgeService;
use Illuminate\Support\Facades\Storage;
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

    public bool $isCoverCropped = false;

    public ?array $coverCropData = null;

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
    public function profile()
    {
        return auth()->user()->fresh(['profile', 'profile.settings', 'profile.seo'])->profile;
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

    /**
     * Immediate definitive Avatar upload.
     */
    public function updatedFormAvatar(): void
    {
        $this->validateOnly('form.avatar');

        app(UpdateProfileMediaAction::class)->updateAvatar(auth()->user(), $this->form->avatar);

        $this->form->avatar = null;
        auth()->user()->refresh();
        $this->form->setUser(auth()->user());

        Toaster::success(__('dashboard.profile.edit.messages.avatar_success'));
    }

    /**
     * Opens cropper modal for Cover.
     */
    public function updatedFormCover(): void
    {
        $this->validateOnly('form.cover');
        $this->isCoverCropped = false;
        $this->dispatch('open-modal', name: 'cover-cropper-modal');
    }

    /**
     * Immediate definitive Cover upload after crop confirmation.
     */
    public function saveCrop(array $data): void
    {
        app(UpdateProfileMediaAction::class)->updateCover(auth()->user(), $this->form->cover, $data);

        $this->form->cover = null;
        $this->isCoverCropped = true;
        $this->coverCropData = null;

        $this->dispatch('close-modal', name: 'cover-cropper-modal');

        auth()->user()->refresh();
        $this->form->setUser(auth()->user());

        Toaster::success(__('dashboard.profile.edit.messages.cover_success'));
    }

    public function removeAvatar(): void
    {
        $profile = $this->profile;

        if ($profile && $profile->avatar_path) {
            $oldPath = $profile->avatar_path;
            $profile->update(['avatar_path' => null]);
            Storage::disk('public')->delete($oldPath);

            auth()->user()->refresh();
            $this->form->setUser(auth()->user());
            Toaster::success(__('dashboard.profile.edit.messages.avatar_removed'));
        }
    }

    public function removeCover(): void
    {
        $profile = $this->profile;

        if ($profile && $profile->cover_path) {
            $oldPath = $profile->cover_path;
            $profile->update(['cover_path' => null]);
            Storage::disk('public')->delete($oldPath);

            auth()->user()->refresh();
            $this->form->setUser(auth()->user());
            Toaster::success(__('dashboard.profile.edit.messages.cover_removed'));
        }
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

            auth()->user()->refresh()->load(['profile', 'profile.settings', 'profile.seo']);
            $this->form->setUser(auth()->user());

            Toaster::success(__('dashboard.profile.edit.messages.success'));
        } catch (ValidationException $e) {
            Toaster::error(__('dashboard.profile.edit.messages.validation_error'));

            throw $e;
        } catch (Throwable $e) {
            Toaster::error(__('dashboard.profile.edit.messages.error'));

            throw $e;
        }
    }

    public function render(): View
    {
        return view('livewire.dashboard.profile.edit-profile');
    }
}
