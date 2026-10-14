<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\TemporaryExport;
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
        $path = (string) $request->query('path', '');

        // Security: only the owner may download (temp/exports/{userId}/...).
        // Previously knowing the (predictable) file name was enough to fetch
        // another user's export, including 2FA recovery codes.
        abort_unless(TemporaryExport::isOwnedBy($path, (int) $request->user()->getKey()), 403);

        $disk = Storage::disk(TemporaryExport::DISK);

        abort_unless($disk->exists($path), 404);

        return response()->download($disk->path($path))->deleteFileAfterSend(true);
    }
}
