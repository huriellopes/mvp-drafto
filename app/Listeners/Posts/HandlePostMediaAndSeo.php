<?php

declare(strict_types=1);

namespace App\Listeners\Posts;

use App\Events\Posts\PostSaved;
use App\Jobs\ProcessPostMediaAndSeoJob;
use Illuminate\Contracts\Queue\ShouldQueue;

final class HandlePostMediaAndSeo implements ShouldQueue
{
    /**
     * Sênior: O listener é enfileirado por padrão para não travar a requisição.
     */
    public function handle(PostSaved $event): void
    {
        dispatch(new ProcessPostMediaAndSeoJob($event->post, $event->seoData, $event->oldImagePath));
    }
}
