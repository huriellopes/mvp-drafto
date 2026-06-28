<?php

declare(strict_types=1);

namespace Tests\Feature\Mail;

use App\Mail\ReengagementMail;
use App\Models\Post;
use App\Models\User;

it('uses the default early-stage subject for writers', function () {
    $writer = User::factory()->writer()->create();

    $mail = new ReengagementMail($writer, 0, 0);

    $mail->assertHasSubject('Sentimos sua falta no Drafto 👋');

    expect($mail->render())->toContain(route('dashboard.posts.create'));
});

it('uses the long-inactivity subject for writers', function () {
    $writer = User::factory()->writer()->create();

    $mail = new ReengagementMail($writer, 60, 60);

    $mail->assertHasSubject('Suas ideias têm espaço no Drafto');
});

it('uses the long-inactivity subject for readers with new content', function () {
    $reader = User::factory()->create(['last_login_at' => now()->subDays(70)]);
    Post::factory()->published()->create(['published_at' => now()->subDay()]);

    $mail = new ReengagementMail($reader, 60, 60);

    $mail->assertHasSubject('Tem muita leitura nova esperando você no Drafto');
    expect($mail->render())->toContain(route('posts.explore'));
});

it('uses the long-inactivity invite subject for readers without new content', function () {
    $reader = User::factory()->create(['last_login_at' => now()->subDays(70)]);

    $mail = new ReengagementMail($reader, 60, 60);

    $mail->assertHasSubject('As suas ideias merecem ser publicadas no Drafto');
    expect($mail->render())->toContain(route('dashboard.account'));
});
