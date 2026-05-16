<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class TemporaryFileDownloadController extends Controller
{
    /**
     * Handle the download and cleanup of temporary files.
     */
    public function __invoke(Request $request): BinaryFileResponse
    {
        $path = $request->query('path');

        if (!$path || !Storage::disk('local')->exists($path)) {
            abort(404);
        }

        // Segurança: Apenas arquivos dentro da pasta temp/
        if (!str_starts_with($path, 'temp/')) {
            abort(403);
        }

        $fullPath = Storage::disk('local')->path($path);

        return response()->download($fullPath)->deleteFileAfterSend(true);
    }
}
