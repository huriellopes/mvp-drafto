<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Admin\System;

use App\Actions\Admin\ReadLogEntriesAction;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

#[Layout('layouts.app', [
    'heading' => 'Logs do Sistema',
    'subheading' => 'Erros, jobs e diagnóstico da aplicação',
])]
#[Title('Logs do Sistema')]
class LogViewerIndex extends Component
{
    private const LEVELS = [
        'errors' => ['emergency', 'alert', 'critical', 'error'],
        'debug' => ['debug', 'info', 'notice', 'warning'],
    ];

    /** Aba ativa: errors | jobs | debug */
    #[Url]
    public string $tab = 'errors';

    /** Arquivo de log selecionado (abas errors/debug). */
    #[Url]
    public ?string $file = null;

    public function selectTab(string $tab): void
    {
        $this->tab = in_array($tab, ['errors', 'jobs', 'debug'], true) ? $tab : 'errors';
    }

    public function retryJob(string $uuid): void
    {
        Artisan::call('queue:retry', ['id' => [$uuid]]);

        Toaster::success(__('dashboard.logs.job_retried'));
    }

    public function forgetJob(string $uuid): void
    {
        Artisan::call('queue:forget', ['id' => $uuid]);

        Toaster::success(__('dashboard.logs.job_forgotten'));
    }

    public function render(): View
    {
        $action = app(ReadLogEntriesAction::class);

        $entries = collect();
        $failedJobs = collect();

        if ($this->tab === 'jobs') {
            $failedJobs = $action->failedJobs();
        } else {
            $levels = self::LEVELS[$this->tab] ?? self::LEVELS['errors'];
            $entries = $action->exec($this->file, $levels);
        }

        return view('livewire.dashboard.admin.system.log-viewer-index', [
            'entries' => $entries,
            'failedJobs' => $failedJobs,
            'files' => $action->files(),
        ]);
    }
}
