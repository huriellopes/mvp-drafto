<?php

declare(strict_types=1);

namespace App\Traits;

trait EnumOptions
{
    abstract public function label(): string;

    public function color(): string
    {
        return 'gray';
    }

    /**
     * @return list<array{
     *     value: string|int,
     *     label: string,
     *     color: string
     * }>
     */
    public static function options(): array
    {
        return array_map(
            static fn ($case): array => [
                'value' => $case->value,
                'label' => $case->label(),
                'color' => $case->color(),
            ],
            static::cases(),
        );
    }

    /**
     * @return list<string|int>
     */
    public static function values(): array
    {
        return array_column(static::options(), 'value');
    }

    /**
     * @return array<string|int, string>
     */
    public static function labels(): array
    {
        $labels = [];

        foreach (static::cases() as $case) {
            $labels[$case->value] = $case->label();
        }

        return $labels;
    }
}
