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

    public function setUser(User $user): void
    {
        $this->name = $user->name;
        $this->email = $user->email;
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
        ];
    }

    public function update(): void
    {
        $this->validate();

        app(UpdateUserSettingsAction::class)->exec(
            user: auth()->user(),
            data: UpdateUserSettingsData::from($this->all()),
        );

        $this->reset(['password', 'password_confirmation']);
    }
}
