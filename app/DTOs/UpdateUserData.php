<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use Spatie\LaravelData\Data;

class UpdateUserData extends Data
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $password = null,
        public string|RoleEnum|null $role = null,
        public string|UserStatusEnum|null $status = null,
    ) {}
}
