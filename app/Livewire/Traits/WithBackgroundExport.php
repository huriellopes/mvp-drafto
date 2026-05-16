<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;

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

        return Storage::disk('local')
            ->exists($this->generatedPath);
    }

    /**
     * Clear the generated file path state.
     */
    public function clearGeneratedFile(): void
    {
        $this->generatedPath = null;
    }
}
