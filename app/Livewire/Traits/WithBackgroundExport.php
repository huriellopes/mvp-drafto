<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Jobs\ExportDataJob;
use App\Support\TemporaryExport;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Masmerise\Toaster\Toaster;

/**
 * Background file generation for dashboard components.
 *
 * Completion is pushed through Reverb (ExportReady / ExportFailed on the
 * user's private channel) instead of polling every second. While Echo is
 * disconnected the view falls back to a slow poll (see `realtimeFallback`
 * in resources/js/app.js).
 */
trait WithBackgroundExport
{
    public ?string $generatedPath = null;

    /**
     * Check if the generated file exists on the disk.
     */
    #[Computed]
    public function isFileReady(): bool
    {
        if (!$this->generatedPath) {
            return false;
        }

        return Storage::disk(TemporaryExport::DISK)
            ->exists($this->generatedPath);
    }

    /**
     * Clear the generated file path state.
     */
    public function clearGeneratedFile(): void
    {
        $this->generatedPath = null;
    }

    /**
     * Re-render so `isFileReady` picks up the finished file.
     *
     * @param  array{path?: string}  $payload
     */
    public function onExportReady(array $payload = []): void {}

    /**
     * @param  array{path?: string}  $payload
     */
    public function onExportFailed(array $payload = []): void
    {
        if (($payload['path'] ?? null) !== $this->generatedPath) {
            return;
        }

        $this->clearGeneratedFile();

        Toaster::error('Não foi possível gerar o arquivo. Tente novamente.');
    }

    /**
     * @return array<string, string>
     */
    protected function getListeners(): array
    {
        $userId = auth()->id();

        if ($userId === null) {
            return [];
        }

        $channel = "echo-private:App.Models.User.{$userId}";

        return [
            "{$channel},.export.ready" => 'onExportReady',
            "{$channel},.export.failed" => 'onExportFailed',
        ];
    }

    /**
     * @param  class-string  $exportClass
     * @param  array<string, mixed>  $params
     */
    protected function queueExport(string $exportClass, array $params, string $fileName): void
    {
        $userId = (int) auth()->id();
        $this->generatedPath = TemporaryExport::pathFor($userId, $fileName);

        dispatch(new ExportDataJob($exportClass, $params, $fileName, $userId));
    }
}
