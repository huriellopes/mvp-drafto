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
            $model->generateUniqueSlug();
        });

        static::updating(function ($model) {
            $sourceField = $model->getSlugSourceField();

            if ($model->isDirty($sourceField) && !$model->isDirty('slug')) {
                $model->generateUniqueSlug();
            }
        });
    }

    /**
     * Define the source field for slug generation.
     * Can be overridden in the model.
     */
    protected function getSlugSourceField(): string
    {
        return property_exists($this, 'name') || isset($this->name) ? 'name' : 'title';
    }

    /**
     * Generates a unique slug for the model.
     */
    protected function generateUniqueSlug(): void
    {
        $sourceField = $this->getSlugSourceField();
        $baseSlug = Str::slug($this->$sourceField);
        $slug = $baseSlug;
        $counter = 1;

        while (static::where('slug', $slug)
            ->where($this->getKeyName(), '!=', $this->getKey())
            ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $this->slug = $slug;
    }
}
