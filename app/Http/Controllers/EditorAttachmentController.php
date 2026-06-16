<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EditorAttachmentController extends Controller
{
    /**
     * Handles image and video uploads from the rich editor (Quill).
     * Stores the file on the configured disk and returns its URL and type.
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
                'mimetypes:' . implode(',', config('editor.allowed_mimetypes')),
                // Limite vindo de config/editor.php (alinhado ao PHP do servidor).
                'max:' . config('editor.max_upload_kb'),
            ],
        ]);

        $file = $request->file('file');
        $isVideo = str_starts_with((string) $file->getMimeType(), 'video/');

        $path = $file->storeAs(
            config('editor.upload_path'),
            Str::uuid() . '.' . $file->getClientOriginalExtension(),
            config('editor.upload_disk'),
        );

        // URL absoluta evita problemas de caminho relativo no editor/renderização.
        return response()->json([
            'url' => asset('storage/' . $path),
            'type' => $isVideo ? 'video' : 'image',
        ]);
    }
}
