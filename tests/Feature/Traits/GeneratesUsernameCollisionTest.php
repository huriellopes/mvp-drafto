<?php

declare(strict_types=1);

namespace Tests\Feature\Traits;

use App\Models\Profile;
use App\Models\User;
use App\Traits\GeneratesUsername;
use Illuminate\Support\Str;

it('regenerates the username on collision and terminates', function () {
    // Sequência determinística para Str::random: 1º sorteio colide, 2º é livre.
    $sequence = ['AAAA', 'BBBB'];
    $i = 0;
    Str::createRandomStringsUsing(function () use (&$i, $sequence): string {
        return $sequence[$i++] ?? 'ZZZZ';
    });

    // Ocupa o primeiro candidato ('john-aaaa') para forçar o loop.
    $owner = User::factory()->create();
    Profile::factory()->create(['user_id' => $owner->id, 'username' => 'john-aaaa']);

    $generator = new class()
    {
        use GeneratesUsername;

        public function make(string $name): string
        {
            return $this->generateUniqueUsername($name);
        }
    };

    $result = $generator->make('John');

    Str::createRandomStringsNormally();

    // Pulou o colidido 'john-aaaa' e parou no segundo candidato livre.
    expect($result)->toBe('john-bbbb');
});
