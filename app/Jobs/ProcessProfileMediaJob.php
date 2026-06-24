<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Profile;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Throwable;

final class ProcessProfileMediaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 60, 120];

    public function __construct(
        public Profile $profile,
        public ?string $oldAvatarPath = null,
        public ?string $oldCoverPath = null,
    ) {}

    public function handle(): void
    {
        if (!$this->profile->exists) {
            return;
        }

        // 1. Otimizar Avatar
        if ($this->profile->avatar_path && !$this->isWebp($this->profile->avatar_path)) {
            $this->profile->avatar_path = $this->optimizeImage($this->profile->avatar_path, 'avatars', 400, 400);
        }

        // 2. Otimizar Capa (Apenas se não for WebP já, ex: uploads diretos sem crop)
        if ($this->profile->cover_path && !$this->isWebp($this->profile->cover_path)) {
            $this->profile->cover_path = $this->optimizeImage($this->profile->cover_path, 'covers', 1200, 400);
        }

        if ($this->profile->isDirty(['avatar_path', 'cover_path'])) {
            $this->profile->saveQuietly();
        }

        // 3. Cleanup: Deleta as imagens antigas para economizar espaço
        $this->cleanup($this->oldAvatarPath, $this->profile->avatar_path);
        $this->cleanup($this->oldCoverPath, $this->profile->cover_path);
    }

    public function failed(Throwable $exception): void
    {
        Log::error("Job ProcessProfileMediaJob falhou para o Perfil #{$this->profile->id}: " . $exception->getMessage());
    }

    private function isWebp(string $path): bool
    {
        return str_ends_with(mb_strtolower($path), '.webp');
    }

    private function optimizeImage(string $currentPath, string $folder, int $width, int $height): string
    {
        try {
            $fullPath = Storage::disk('public')->path($currentPath);

            if (!file_exists($fullPath)) {
                return $currentPath;
            }

            $imageManager = ImageManager::usingDriver(new Driver());
            $image = $imageManager->decodePath($fullPath);

            // Redimensionamento inteligente (cover/fit)
            $image->cover($width, $height);

            $newPath = $folder . '/' . pathinfo($currentPath, PATHINFO_FILENAME) . '.webp';
            Storage::disk('public')->put($newPath, (string) $image->encodeUsingFileExtension('webp', 80));

            if ($newPath !== $currentPath) {
                Storage::disk('public')->delete($currentPath);
            }

            return $newPath;
        } catch (Exception $e) {
            Log::error("Erro ao otimizar imagem de perfil ({$folder}): " . $e->getMessage());

            return $currentPath;
        }
    }

    private function cleanup(?string $oldPath, ?string $currentPath): void
    {
        if (!$oldPath || $oldPath === $currentPath) {
            return;
        }

        try {
            $disk = Storage::disk('public');

            // 1. Deleta o arquivo original (o caminho que veio do banco)
            $disk->delete($oldPath);

            // 2. Sênior: Deleta versões processadas (WebP) caso o original fosse JPG/PNG
            $webpPath = pathinfo($oldPath, PATHINFO_DIRNAME) . '/' . pathinfo($oldPath, PATHINFO_FILENAME) . '.webp';

            if ($webpPath !== $oldPath && $webpPath !== $currentPath) {
                $disk->delete($webpPath);
            }
        } catch (Exception $e) {
            Log::warning("Erro ao deletar imagem antiga de perfil ({$oldPath}): " . $e->getMessage());
        }
    }
}
