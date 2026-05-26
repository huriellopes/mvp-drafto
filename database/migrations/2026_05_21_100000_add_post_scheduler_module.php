<?php

declare(strict_types=1);

use App\Enums\ModuleEnum;
use App\Models\Module;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $config = collect(config('modules'))->firstWhere('slug', ModuleEnum::POST_SCHEDULER);

        if ($config) {
            Module::query()->updateOrCreate(
                ['slug' => $config['slug']],
                [
                    'name' => $config['name'],
                    'description' => $config['description'],
                    'icon' => $config['icon'],
                    'is_enabled' => true,
                    'settings' => $config['settings'],
                ]
            );
        }
    }

    public function down(): void
    {
        Module::query()->where('slug', ModuleEnum::POST_SCHEDULER)->delete();
    }
};
