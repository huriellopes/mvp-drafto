<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    public static function findBySlug(string $slug): self
    {
        return static::where('slug', $slug)->firstOrFail();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Boot the trait to generate slugs automatically.
     */
    protected static function bootHasSlug(): void
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $sourceField = isset($model->name) ? 'name' : 'title';
                $model->slug = Str::slug($model->$sourceField);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty(isset($model->name) ? 'name' : 'title') && !$model->isDirty('slug')) {
                $sourceField = isset($model->name) ? 'name' : 'title';
                $model->slug = Str::slug($model->$sourceField);
            }
        });
    }
}
