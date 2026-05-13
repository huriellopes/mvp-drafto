<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TrixAttachmentController extends Controller
{
    /**
     * Handles file uploads from the Trix editor.
     * Stores the file and returns the public URL.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'image', 'max:5120'],
        ]);

        $file = $request->file('file');

        // Salvando no disco público
        $path = $file->storeAs(
            'trix-attachments',
            Str::uuid() . '.' . $file->getClientOriginalExtension(),
            'public',
        );

        // Retornando a URL absoluta para evitar problemas de caminho relativo
        return response()->json([
            'url' => asset('storage/' . $path),
        ]);
    }
}
