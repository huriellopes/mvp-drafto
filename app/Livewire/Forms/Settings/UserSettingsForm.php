<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Settings;

use App\Actions\Settings\UpdateUserSettingsAction;
use App\DTOs\UpdateUserSettingsData;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Form;

class UserSettingsForm extends Form
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public bool $wants_reengagement_emails = true;

    public bool $wants_product_updates = true;

    public function setUser(User $user): void
    {
        $this->name = $user->name;
        $this->email = $user->email;
        $this->wants_reengagement_emails = (bool) $user->wants_reengagement_emails;
        $this->wants_product_updates = (bool) $user->wants_product_updates;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore(auth()->id()),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'wants_reengagement_emails' => ['boolean'],
            'wants_product_updates' => ['boolean'],
        ];
    }

    public function update(): void
    {
        $this->validate();

        resolve(UpdateUserSettingsAction::class)->exec(
            user: auth()->user(),
            data: UpdateUserSettingsData::from($this->all()),
        );

        $this->reset(['password', 'password_confirmation']);
    }
}
