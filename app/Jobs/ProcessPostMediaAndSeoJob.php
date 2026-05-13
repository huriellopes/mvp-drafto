<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Post;
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

final class ProcessPostMediaAndSeoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Sênior: O número de vezes que o job pode ser tentado.
     */
    public int $tries = 3;

    /**
     * Sênior: O número de segundos a aguardar antes de tentar novamente.
     */
    public array $backoff = [30, 60, 120];

    public function __construct(
        public Post $post,
        public array $seoData = [],
        public ?string $oldImagePath = null,
    ) {}

    public function handle(): void
    {
        // Garante que o post ainda exista
        if (!$this->post->exists) {
            return;
        }

        // 1. Processamento de Imagem (Conversão para WebP)
        if ($this->post->cover_image_path && !$this->isWebp($this->post->cover_image_path)) {
            $this->optimizeImage();
        }

        // 2. Cleanup de Imagem (I/O de Disco/S3)
        if ($this->oldImagePath && $this->post->cover_image_path !== $this->oldImagePath) {
            try {
                Storage::disk('public')->delete($this->oldImagePath);
            } catch (Exception $e) {
                // Sênior: Logamos mas não falhamos o job por erro de cleanup (evita loop infinito se arquivo sumiu)
                Log::warning('Erro ao deletar imagem antiga: ' . $this->oldImagePath);
            }
        }

        // 3. Atualização de SEO (Escrita em tabela auxiliar)
        if (!empty($this->seoData['title']) || !empty($this->seoData['description'])) {
            $this->post->seo()->updateOrCreate(
                ['model_id' => $this->post->id, 'model_type' => $this->post->getMorphClass()],
                [
                    'title' => $this->seoData['title'] ?? null,
                    'description' => $this->seoData['description'] ?? null,
                ],
            );
        }
    }

    /**
     * Tratamento de falha definitiva.
     */
    public function failed(Throwable $exception): void
    {
        Log::error("Job ProcessPostMediaAndSeoJob falhou definitivamente para o Post #{$this->post->id}: " . $exception->getMessage());
    }

    private function isWebp(string $path): bool
    {
        return str_ends_with(mb_strtolower($path), '.webp');
    }

    private function optimizeImage(): void
    {
        try {
            /** @var string $coverPath */
            $coverPath = $this->post->cover_image_path;
            $path = Storage::disk('public')->path($coverPath);

            if (!file_exists($path)) {
                return;
            }

            $imageManager = new ImageManager(new Driver());
            $image = $imageManager->read($path);

            $newPath = 'posts/' . pathinfo($coverPath, PATHINFO_FILENAME) . '.webp';

            Storage::disk('public')->put($newPath, $image->toWebp(80)->toString());

            // Deleta o original se for diferente (ex: .jpg)
            if ($newPath !== $coverPath) {
                Storage::disk('public')->delete($coverPath);
            }

            $this->post->updateQuietly(['cover_image_path' => $newPath]);
        } catch (Exception $e) {
            Log::error('Erro ao otimizar imagem para WebP: ' . $e->getMessage());
        }
    }
}
