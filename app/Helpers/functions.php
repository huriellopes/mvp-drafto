<?php

declare(strict_types=1);

if (!function_exists('format_display_name')) {
    /**
     * Retorna o primeiro e último nome de uma string.
     */
    function format_display_name(?string $name): string
    {
        if (empty($name)) {
            return 'Usuário';
        }

        $parts = explode(' ', mb_trim($name));
        $firstName = $parts[0];
        $lastName = count($parts) > 1 ? end($parts) : '';

        return mb_trim($firstName . ' ' . $lastName);
    }
}
