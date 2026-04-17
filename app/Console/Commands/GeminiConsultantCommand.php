<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:gemini-consultant-command')]
#[Description('Command description')]
class GeminiConsultantCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $question = $this->argument('question');
        $context = file_get_contents(base_path('gemini.md'));

        // Aqui você faria a chamada para a SDK do Gemini ou via cURL
        $fullPrompt = "{$context}\n\nTask: {$question}";
    }
}
