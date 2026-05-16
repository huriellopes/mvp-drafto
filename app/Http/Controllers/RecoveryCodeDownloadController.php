<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RecoveryCodeDownloadController extends Controller
{
    /**
     * Handle the download and cleanup of recovery codes.
     */
    public function __invoke(Request $request): BinaryFileResponse
    {
        $path = $request->query('path');

        if (!$path || !Storage::disk('local')->exists($path)) {
            abort(404);
        }

        // Verifica se o arquivo pertence ao usuário (nome do arquivo contém o slug do usuário)
        $user = auth()->user();
        $username = Str::slug($user->profile?->username ?? $user->name);
        $filename = basename($path);

        if (!str_starts_with($filename, "drafto-{$username}-")) {
            abort(403);
        }

        $fullPath = Storage::disk('local')->path($path);

        return response()->download($fullPath)->deleteFileAfterSend(true);
    }
}
