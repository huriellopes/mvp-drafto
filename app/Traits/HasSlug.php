<?php

namespace App\Traits;

trait HasSlug
{
    public static function findBySlug(string $slug) : self
    {
        return static::where('slug', $slug)->firstOrFail();
    }

    public function getRouteKeyName() : string
    {
        return 'slug';
    }
}
