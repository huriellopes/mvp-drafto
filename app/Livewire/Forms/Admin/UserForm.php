<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Admin;

use App\Actions\Users\StoreUserAction;
use App\Actions\Users\UpdateUserAction;
use App\DTOs\SaveUserData;
use App\DTOs\UpdateUserData;
use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Form;
use Throwable;

class UserForm extends Form
{
    public ?User $user = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $role = 'reader';

    public string $status = 'active';

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->user?->id)],
            'password' => [$this->user instanceof User ? 'nullable' : 'required', 'string', 'min:8'],
            'role' => ['required', Rule::enum(RoleEnum::class)],
            'status' => ['required', Rule::enum(UserStatusEnum::class)],
        ];
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role->value;
        $this->status = $user->status->value;
        $this->password = '';
    }

    /**
     * @throws Throwable
     */
    public function save(): void
    {
        $this->validate();

        if ($this->user instanceof User) {
            resolve(UpdateUserAction::class)
                ->exec(
                    user: $this->user,
                    data: UpdateUserData::from($this->except('user')),
                );

            return;
        }

        resolve(StoreUserAction::class)
            ->exec(
                data: SaveUserData::from([
                    ...$this->all(),
                    'send_welcome_email' => true,
                ]),
            );
    }
}
