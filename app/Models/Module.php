<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ModuleEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable([
    'name',
    'slug',
    'description',
    'icon',
    'is_enabled',
    'settings',
])]
class Module extends Model
{
    // Helper Sênior: Verifica se um módulo está ativo com Cache para performance
    public static function isEnabled(ModuleEnum $slug): bool
    {
        return Cache::remember("module_status_{$slug->value}", now()->addMinutes(30), function () use ($slug) {
            return self::where('slug', $slug)->where('is_enabled', true)->exists();
        });
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    protected static function booted(): void
    {
        // Limpa o cache sempre que o módulo for atualizado
        static::updated(function ($module) {
            Cache::forget("module_status_{$module->slug->value}");
        });
    }

    protected function casts(): array
    {
        return [
            'slug' => ModuleEnum::class,
            'is_enabled' => 'boolean',
            'settings' => 'array',
        ];
    }
}
