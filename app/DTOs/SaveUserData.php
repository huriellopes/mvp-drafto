<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use Spatie\LaravelData\Data;

class SaveUserData extends Data
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $password,
        public string|RoleEnum $role,
        public string|UserStatusEnum $status = UserStatusEnum::ACTIVE,
        public bool $is_lifetime = false,
        public ?int $plan_id = null,
        public bool $send_welcome_email = true,
    ) {}
}
