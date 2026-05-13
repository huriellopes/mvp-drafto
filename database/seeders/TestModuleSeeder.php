<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ModuleEnum;
use App\Models\Module;
use Illuminate\Database\Seeder;

class TestModuleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ModuleEnum::cases() as $module) {
            Module::updateOrCreate(
                ['slug' => $module->value],
                [
                    'name' => $module->label(),
                    'is_enabled' => true,
                    'settings' => [],
                ],
            );
        }
    }
}
