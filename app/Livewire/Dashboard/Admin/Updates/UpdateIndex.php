<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Admin\Updates;

use App\Actions\Engagement\SendProductUpdateAction;
use App\Models\PlatformUpdate;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Masmerise\Toaster\Toaster;
use Mews\Purifier\Facades\Purifier;

#[Layout('layouts.app', ['heading' => 'Novidades', 'subheading' => 'Comunique melhorias e novidades aos usuários por e-mail'])]
#[Title('Novidades')]
class UpdateIndex extends Component
{
    #[Validate('required|min:3|max:160')]
    public string $title = '';

    #[Validate('required|min:10')]
    public string $content = '';

    #[Validate('required|in:all,writers,readers')]
    public string $audience = 'all';

    public ?int $editingId = null;

    public ?int $updateIdToSend = null;

    public ?int $updateIdToDelete = null;

    /**
     * Carrega um rascunho para edição. Comunicados já enviados não podem ser editados.
     */
    public function edit(int $id): void
    {
        $update = PlatformUpdate::find($id);

        if (!$update) {
            return;
        }

        if ($update->isSent()) {
            Toaster::warning('Este comunicado já foi enviado e não pode ser editado.');

            return;
        }

        $this->editingId = $update->id;
        $this->title = $update->title;
        $this->content = $update->content;
        $this->audience = $update->audience->value;
    }

    public function cancelEdit(): void
    {
        $this->reset('title', 'content', 'audience', 'editingId');
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            // Mesma sanitização dos posts (perfil post_content).
            'content' => Purifier::clean($this->content, 'post_content'),
            'audience' => $this->audience,
        ];

        if ($this->editingId) {
            $update = PlatformUpdate::find($this->editingId);

            // Trava de segurança: não permite editar algo que já foi enviado.
            if ($update && !$update->isSent()) {
                $update->update($data);
                Toaster::success('Comunicado atualizado.');
            }
        } else {
            PlatformUpdate::create($data + ['created_by' => auth()->id()]);
            Toaster::success('Comunicado salvo como rascunho. Revise e envie quando quiser.');
        }

        $this->reset('title', 'content', 'audience', 'editingId');
    }

    public function confirmSend(int $id): void
    {
        $this->updateIdToSend = $id;
        $this->dispatch('open-modal', name: 'confirm-send-update');
    }

    /**
     * Comunicado atualmente em confirmação de envio (para montar a mensagem do modal).
     */
    #[Computed]
    public function sendingUpdate(): ?PlatformUpdate
    {
        return $this->updateIdToSend
            ? PlatformUpdate::find($this->updateIdToSend)
            : null;
    }

    public function send(): void
    {
        $update = PlatformUpdate::find($this->updateIdToSend);

        if (!$update) {
            return;
        }

        $count = resolve(SendProductUpdateAction::class)->exec($update);

        Toaster::success("Comunicado enviado para {$count} usuário(s).");

        $this->updateIdToSend = null;
        $this->dispatch('close-modal', name: 'confirm-send-update');
    }

    public function confirmDelete(int $id): void
    {
        $this->updateIdToDelete = $id;
        $this->dispatch('open-modal', name: 'confirm-delete-update');
    }

    public function delete(): void
    {
        if (!$this->updateIdToDelete) {
            return;
        }

        PlatformUpdate::whereKey($this->updateIdToDelete)->delete();
        Toaster::success('Comunicado removido.');

        $this->updateIdToDelete = null;
        $this->dispatch('close-modal', name: 'confirm-delete-update');
    }

    #[Computed]
    public function updates()
    {
        return PlatformUpdate::with('author')->latest()->limit(20)->get();
    }

    public function render(): View
    {
        return view('livewire.dashboard.admin.updates.update-index');
    }
}
