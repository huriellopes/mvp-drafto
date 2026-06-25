<?php

declare(strict_types=1);

namespace App\Mail;

use App\Enums\RoleEnum;
use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class ReengagementMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private const string MODE_WRITER = 'writer';

    private const string MODE_READER_READ = 'reader_read';

    private const string MODE_READER_INVITE_WRITER = 'reader_invite_writer';

    public string $unsubscribeUrl;

    private ?string $resolvedMode = null;

    public function __construct(
        public User $user,
        public int $stage = 0,
        public int $inactiveDays = 0,
    ) {
        $this->unsubscribeUrl = URL::signedRoute(
            'email.preferences.unsubscribe',
            ['user' => $this->user->id, 'type' => 'reengagement'],
        );
    }

    public function envelope(): Envelope
    {
        $subject = match ($this->mode()) {
            self::MODE_WRITER => match (true) {
                $this->stage >= 60 => 'Suas ideias têm espaço no Drafto',
                $this->stage >= 30 => 'Que tal voltar a escrever no Drafto?',
                default => 'Sentimos sua falta no Drafto 👋',
            },
            self::MODE_READER_READ => match (true) {
                $this->stage >= 60 => 'Tem muita leitura nova esperando você no Drafto',
                $this->stage >= 30 => 'Que tal voltar a ler no Drafto?',
                default => 'Sentimos sua falta no Drafto 👋',
            },
            self::MODE_READER_INVITE_WRITER => match (true) {
                $this->stage >= 60 => 'As suas ideias merecem ser publicadas no Drafto',
                $this->stage >= 30 => 'Que tal compartilhar a sua história no Drafto?',
                default => 'Sentimos sua falta no Drafto 👋',
            },
            default => 'Sentimos sua falta no Drafto 👋',
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $mode = $this->mode();

        [$ctaUrl, $ctaText] = match ($mode) {
            self::MODE_WRITER => [route('dashboard.posts.create'), 'Escrever no ' . config('app.name')],
            self::MODE_READER_READ => [route('posts.explore'), 'Explorar no ' . config('app.name')],
            default => [route('dashboard.account'), 'Tornar-me escritor'],
        };

        return new Content(
            view: 'emails.reengagement',
            with: [
                'user' => $this->user,
                'inactiveDays' => $this->inactiveDays,
                'mode' => $mode,
                'ctaUrl' => $ctaUrl,
                'ctaText' => $ctaText,
                'unsubscribeUrl' => $this->unsubscribeUrl,
            ],
        );
    }

    private function mode(): string
    {
        if ($this->resolvedMode !== null) {
            return $this->resolvedMode;
        }

        if ($this->user->hasRole(RoleEnum::WRITER)) {
            return $this->resolvedMode = self::MODE_WRITER;
        }

        return $this->resolvedMode = $this->readerHasNewContent()
            ? self::MODE_READER_READ
            : self::MODE_READER_INVITE_WRITER;
    }

    private function readerHasNewContent(): bool
    {
        $since = $this->user->last_login_at ?? now()->subDays(max($this->inactiveDays, 30));

        return Post::query()
            ->published()
            ->where('published_at', '>', $since)
            ->exists();
    }
}
