<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Http\Middleware\AllowIframeMiddleware;
use Illuminate\Http\Request;

it('removes x-frame-options and sets a permissive csp header', function () {
    $middleware = new AllowIframeMiddleware;

    $response = $middleware->handle(Request::create('/badge/@joe'), function () {
        $resp = response('ok');
        $resp->headers->set('X-Frame-Options', 'DENY');

        return $resp;
    });

    expect($response->headers->get('X-Frame-Options'))->toBeNull()
        ->and($response->headers->get('Content-Security-Policy'))->toBe('frame-ancestors *');
});
