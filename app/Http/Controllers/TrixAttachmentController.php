<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TrixAttachmentController extends Controller
{
    /**
     * Handles image and video uploads from the rich editor (Quill).
     * Stores the file on the public disk and returns its URL and type.
     */
    public function __invoke(Request $request): JsonResponse
    {
        return $this->store($request);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'mimetypes:image/jpeg,image/png,image/gif,image/webp,image/svg+xml,video/mp4,video/webm,video/ogg,video/quicktime',
                // 100MB: alinhado ao limite do PHP (post_max_size/upload_max_filesize).
                // Para vídeos maiores, o usuário deve usar um link do YouTube/Vimeo.
                'max:102400',
            ],
        ]);

        $file = $request->file('file');
        $isVideo = str_starts_with((string) $file->getMimeType(), 'video/');

        $path = $file->storeAs(
            'trix-attachments',
            Str::uuid() . '.' . $file->getClientOriginalExtension(),
            'public',
        );

        // URL absoluta evita problemas de caminho relativo no editor/renderização.
        return response()->json([
            'url' => asset('storage/' . $path),
            'type' => $isVideo ? 'video' : 'image',
        ]);
    }
}
