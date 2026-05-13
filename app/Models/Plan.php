<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

#[Fillable([
    'name',
    'slug',
    'stripe_id',
    'price',
    'features',
    'is_active',
])]
class Plan extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    /**
     * Cache estático para evitar múltiplas consultas aos mesmos planos no mesmo request.
     */
    protected static array $requestCache = [];

    public static function findFromCache(int|string $id): ?Plan
    {
        return self::$requestCache[$id] ?? self::find($id);
    }

    protected static function booted(): void
    {
        static::retrieved(function (Plan $plan) {
            self::$requestCache[$plan->id] = $plan;
        });
    }

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
