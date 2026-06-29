<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Modules\ResolveShortLinkAction;
use App\Models\ShortLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ShortLinkController extends Controller
{
    /**
     * Sênior: Redireciona links curtos para o destino original com tracking de cliques.
     */
    public function __invoke(Request $request, string $code, ResolveShortLinkAction $resolveAction): RedirectResponse
    {
        $url = $resolveAction->exec($code);

        abort_unless($url !== null, 404);

        // Incremento assíncrono de cliques (via query direta para performance)
        ShortLink::where('code', $code)->increment('clicks');

        // Redirecionamento 302 para garantir que o tracking funcione e SEO seja preservado
        return redirect()->to($url, 302);
    }
}
