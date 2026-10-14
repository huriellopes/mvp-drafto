<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Events\Exports\ExportFailed;
use App\Events\Exports\ExportReady;
use App\Models\User;
use App\Support\TemporaryExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Throwable;

class GenerateRecoveryCodesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $format,
        public string $timestamp,
    ) {}

    public function handle(): void
    {
        $codes = (array) ($this->user->two_factor_recovery_codes ?? []);

        if ($codes === []) {
            return;
        }

        $path = $this->path();

        if ($this->format === 'txt') {
            $content = "DRAFTO - CÓDIGOS DE RECUPERAÇÃO\n";
            $content .= 'Data: ' . now()->format('d/m/Y H:i') . "\n";
            $content .= 'E-mail: ' . $this->user->email . "\n";
            $content .= str_repeat('-', 40) . "\n\n";
            $content .= implode("\n", $codes) . "\n\n";
            $content .= str_repeat('-', 40) . "\n";
            $content .= 'AVISO: Guarde estes códigos em um local seguro.';

            Storage::disk(TemporaryExport::DISK)->put($path, $content);
        } else {
            $manager = ImageManager::usingDriver(new Driver);
            $width = 400;
            $height = 600;

            $image = $manager->createImage($width, $height)->fill('ffffff');

            $fontPath = $this->getFontPath();

            // Título
            $image->text('DRAFTO', 200, 50, function ($font) use ($fontPath) {
                $font->filename($fontPath);
                $font->size(32);
                $font->color('18181b');
                $font->align('center');
            });

            $image->text('Códigos de Recuperação', 200, 90, function ($font) use ($fontPath) {
                $font->filename($fontPath);
                $font->size(18);
                $font->color('71717a');
                $font->align('center');
            });

            // Códigos
            $y = 160;

            foreach ($codes as $code) {
                $image->text($code, 200, $y, function ($font) use ($fontPath) {
                    $font->filename($fontPath);
                    $font->size(22);
                    $font->color('18181b');
                    $font->align('center');
                });
                $y += 45;
            }

            // Rodapé
            $image->text('E-mail: ' . $this->user->email, 200, 540, function ($font) use ($fontPath) {
                $font->filename($fontPath);
                $font->size(12);
                $font->color('a1a1aa');
                $font->align('center');
            });

            $image->text('Gerado em: ' . now()->format('d/m/Y H:i'), 200, 560, function ($font) use ($fontPath) {
                $font->filename($fontPath);
                $font->size(10);
                $font->color('a1a1aa');
                $font->align('center');
            });

            Storage::disk(TemporaryExport::DISK)->put($path, (string) $image->encodeUsingMediaType('image/png'));
        }

        // The file is already stored: a websocket outage must not fail the
        // job. The dashboard's fallback poll still picks the file up.
        rescue(fn () => event(new ExportReady($this->user->getKey(), $path)));
    }

    public function failed(?Throwable $exception): void
    {
        rescue(fn () => event(new ExportFailed($this->user->getKey(), $this->path())));
    }

    public function path(): string
    {
        $username = Str::slug($this->user->profile?->username ?? $this->user->name);

        return TemporaryExport::pathFor(
            $this->user->getKey(),
            "drafto-{$username}-{$this->timestamp}.{$this->format}",
        );
    }

    private function getFontPath(): string
    {
        $fonts = [
            '/usr/share/fonts/truetype/noto/NotoSansMono-Bold.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSansMono-Bold.ttf',
            '/usr/share/fonts/truetype/freefont/FreeMonoBold.ttf',
        ];

        foreach ($fonts as $font) {
            if (file_exists($font)) {
                return $font;
            }
        }

        return '';
    }
}
