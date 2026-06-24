<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Lê o cookie de consentimento (LGPD) definido pelo banner de cookies.
 *
 * O cookie `drafto_consent` é gravado pelo JavaScript do banner (logo, não é
 * criptografado pelo Laravel — ver bootstrap/app.php) e contém um JSON com as
 * categorias autorizadas pelo usuário.
 */
final class CookieConsent
{
    public const COOKIE = 'drafto_consent';

    /**
     * Verifica se o usuário consentiu com uma categoria (ex.: 'analytics', 'marketing').
     */
    public static function allows(Request $request, string $category): bool
    {
        $raw = $request->cookie(self::COOKIE);

        if (!is_string($raw) || $raw === '') {
            return false;
        }

        $consent = json_decode(rawurldecode($raw), true);

        return is_array($consent) && ($consent[$category] ?? false) === true;
    }
}
