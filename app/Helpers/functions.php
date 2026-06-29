<?php

declare(strict_types=1);
use App\Support\PlatformVersion;

if (!function_exists('format_display_name')) {
    /**
     * Retorna o primeiro e último nome de uma string de forma limpa.
     */
    function format_display_name(?string $name): string
    {
        if (in_array($name, [null, '', '0'], true)) {
            return 'Usuário';
        }

        $parts = array_filter(explode(' ', mb_trim($name)));

        if (count($parts) === 0) {
            return 'Usuário';
        }

        if (count($parts) === 1) {
            return $parts[0];
        }

        return $parts[0] . ' ' . end($parts);
    }
}

if (!function_exists('format_currency')) {
    /**
     * Formata um valor para Real (BRL).
     */
    function format_currency(int|float $value): string
    {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }
}

if (!function_exists('get_initials')) {
    /**
     * Retorna as iniciais de um nome.
     */
    function get_initials(?string $name): string
    {
        if (in_array($name, [null, '', '0'], true)) {
            return 'DR';
        }

        $words = array_filter(explode(' ', mb_trim($name)));
        $initials = '';

        foreach ($words as $word) {
            $initials .= mb_substr($word, 0, 1);

            if (mb_strlen($initials) >= 2) {
                break;
            }
        }

        return mb_strtoupper($initials);
    }
}

if (!function_exists('platform_version')) {
    /**
     * Versão atual da plataforma (ex.: v1.0.0), para exibição/logs.
     */
    function platform_version(): string
    {
        return PlatformVersion::current();
    }
}
