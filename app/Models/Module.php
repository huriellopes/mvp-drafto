<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ModuleEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use OwenIt\Auditing\Contracts\Auditable;

#[Fillable([
    'name',
    'slug',
    'description',
    'icon',
    'is_enabled',
    'settings',
])]
class Module extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    public static function isEnabled(ModuleEnum|string $slug): bool
    {
        $value = $slug instanceof ModuleEnum ? $slug->value : $slug;

        return (bool) Cache::remember("module_status_{$value}_v3", now()->addDay(), function () use ($value) {
            return self::where('slug', $value)->where('is_enabled', true)->exists();
        });
    }
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    protected static function booted(): void
    {
        // Limpa o cache sempre que o módulo for alterado
        $clearCache = function ($module) {
            $slug = $module->slug instanceof ModuleEnum ? $module->slug->value : $module->slug;
            Cache::forget("module_status_{$slug}");
            Cache::forget("module_status_{$slug}_v2");
            Cache::forget("module_data_{$slug}");
            Cache::forget("module_data_{$slug}_v2");
            Cache::forget("module_data_{$slug}_v3");
        };

        static::created($clearCache);
        static::updated($clearCache);
        static::deleted($clearCache);
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
