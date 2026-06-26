<?php

declare(strict_types=1);

namespace Tests\Feature\Performance;

use App\Enums\ProfileVisibilityEnum;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

function makePublicWriterWithPosts(int $posts): string
{
    $writer = User::factory()->writer()->withProfile()->create();
    $writer->profile->update(['visibility' => ProfileVisibilityEnum::PUBLIC]);
    Post::factory()->published()->for($writer)->count($posts)->create();

    return $writer->profile->username;
}

it('does not run N+1 queries on the public profile (query count is bounded and scales flat)', function () {
    $fewUsername = makePublicWriterWithPosts(3);
    $manyUsername = makePublicWriterWithPosts(15); // página pagina em 12

    Cache::flush();

    $queries = [];
    DB::listen(function ($query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    // Perfil com poucos posts
    $this->get(route('profile.show', $fewUsername))->assertOk();
    $fewCount = count($queries);

    // Perfil com muitos posts
    $queries = [];
    $this->get(route('profile.show', $manyUsername))->assertOk();
    $manyCount = count($queries);

    // Sinal de N+1: o nº de queries cresceria com o nº de posts.
    // Com eager loading (author.profile, category) deve ficar ~constante.
    expect($manyCount - $fewCount)->toBeLessThanOrEqual(2)
        ->and($manyCount)->toBeLessThanOrEqual(30); // teto de sanidade
});
