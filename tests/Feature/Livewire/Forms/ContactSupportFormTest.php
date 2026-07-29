<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Forms;

use App\DTOs\SupportContactData;
use App\Livewire\Forms\Dashboard\SupportForm;
use Livewire\Component;
use Livewire\Livewire;

/**
 * The Dashboard\SupportForm (public contact form) has no host component in the
 * codebase, so we exercise it through a tiny anonymous Livewire host.
 */
function contactSupportHost(): Component
{
    return new class extends Component
    {
        public SupportForm $form;

        public function submit(): void
        {
            $this->form->validate();
        }

        public function render()
        {
            return <<<'HTML'
            <div></div>
            HTML;
        }
    };
}

it('passes validation with valid contact data', function () {
    Livewire::test(contactSupportHost()::class)
        ->set('form.name', 'Maria Silva')
        ->set('form.email', 'maria@example.com')
        ->set('form.subject', 'Dúvida sobre planos')
        ->set('form.message', 'Gostaria de saber mais sobre os planos disponíveis.')
        ->call('submit')
        ->assertHasNoErrors();
});

it('fails validation when fields are missing or invalid', function () {
    Livewire::test(contactSupportHost()::class)
        ->set('form.name', 'ab')
        ->set('form.email', 'not-an-email')
        ->set('form.subject', 'no')
        ->set('form.message', 'short')
        ->call('submit')
        ->assertHasErrors([
            'form.name',
            'form.email',
            'form.subject',
            'form.message',
        ]);
});

it('builds a SupportContactData DTO from the form state', function () {
    $form = new SupportForm(contactSupportHost(), 'form');
    $form->name = 'João';
    $form->email = 'joao@example.com';
    $form->subject = 'Assunto importante';
    $form->message = 'Mensagem com mais de dez caracteres.';

    $dto = $form->toDTO();

    expect($dto)->toBeInstanceOf(SupportContactData::class)
        ->and($dto->name)->toBe('João')
        ->and($dto->email)->toBe('joao@example.com')
        ->and($dto->subject)->toBe('Assunto importante')
        ->and($dto->message)->toBe('Mensagem com mais de dez caracteres.');
});
