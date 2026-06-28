<?php

declare(strict_types=1);

namespace Tests\Feature\Mail;

use App\Mail\ProductUpdateMail;
use App\Models\PlatformUpdate;
use App\Models\User;

it('builds the product update mail', function () {
    $user = User::factory()->withProfile()->create();
    $update = PlatformUpdate::create([
        'title' => 'Nova funcionalidade',
        'content' => 'Lançamos algo incrível para você.',
        'created_by' => $user->id,
    ]);

    $mail = new ProductUpdateMail($user, $update);

    $mail->assertHasSubject('Novidades no Drafto 🚀');

    $html = $mail->render();

    expect($html)
        ->toContain('Nova funcionalidade')
        ->toContain($mail->unsubscribeUrl)
        ->toContain(route('dashboard.index'));
});
