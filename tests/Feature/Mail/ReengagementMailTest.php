<?php

declare(strict_types=1);

use App\Mail\ReengagementMail;
use App\Models\Post;
use App\Models\User;

it('invites writers to write again', function () {
    $writer = User::factory()->writer()->create();

    $mail = new ReengagementMail($writer, 30, 30);
    $mail->assertHasSubject('Que tal voltar a escrever no Drafto?');

    $html = $mail->render();

    expect($html)
        ->toContain('registrar seus')
        ->toContain(route('dashboard.posts.create'))
        ->toContain('Escrever no');
});

it('invites readers to read when there is new content', function () {
    $reader = User::factory()->create([
        'last_login_at' => now()->subDays(40),
    ]);

    // Conteúdo publicado depois da última visita do leitor.
    Post::factory()->published()->create(['published_at' => now()->subDays(2)]);

    $mail = new ReengagementMail($reader, 30, 30);
    $mail->assertHasSubject('Que tal voltar a ler no Drafto?');

    $html = $mail->render();

    expect($html)
        ->toContain('descobrir o que os escritores')
        ->toContain(route('posts.explore'))
        ->toContain('Explorar no');
});

it('invites readers to become writers when there is no new content', function () {
    $reader = User::factory()->create([
        'last_login_at' => now()->subDays(40),
    ]);

    // Nenhum post novo desde a última visita do leitor.

    $mail = new ReengagementMail($reader, 30, 30);
    $mail->assertHasSubject('Que tal compartilhar a sua história no Drafto?');

    $html = $mail->render();

    expect($html)
        ->toContain('vire <strong>escritor</strong>')
        ->toContain(route('dashboard.account'))
        ->toContain('Tornar-me escritor');
});
