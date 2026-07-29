<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

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

        $username = Str::slug($this->user->profile?->username ?? $this->user->name);
        $filename = "drafto-{$username}-{$this->timestamp}";
        $path = "temp/{$filename}.{$this->format}";

        if ($this->format === 'txt') {
            $content = "DRAFTO - CÓDIGOS DE RECUPERAÇÃO\n";
            $content .= 'Data: ' . now()->format('d/m/Y H:i') . "\n";
            $content .= 'E-mail: ' . $this->user->email . "\n";
            $content .= str_repeat('-', 40) . "\n\n";
            $content .= implode("\n", $codes) . "\n\n";
            $content .= str_repeat('-', 40) . "\n";
            $content .= 'AVISO: Guarde estes códigos em um local seguro.';

            Storage::disk('local')->put($path, $content);
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

            Storage::disk('local')->put($path, (string) $image->encodeUsingMediaType('image/png'));
        }

        // Emite um evento para o Livewire avisando que o arquivo está pronto
        // Como o Job roda em outro processo, usaremos uma abordagem de polling ou notificação
        // Para este MVP, vamos considerar que o arquivo será acessível via URL temporária
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
