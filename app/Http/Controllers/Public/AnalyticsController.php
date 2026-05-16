<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Actions\Public\UpdateSiteViewDurationAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AnalyticsController extends Controller
{
    public function updateDuration(Request $request, UpdateSiteViewDurationAction $action): JsonResponse
    {
        $request->validate([
            'url' => ['required', 'string'],
            'duration' => ['required', 'integer', 'min:0'],
        ]);

        $action->handle(
            sessionId: session()->getId(),
            url: $request->input('url'),
            duration: (int) $request->input('duration'),
        );

        return response()->json(['status' => 'success']);
    }
}
