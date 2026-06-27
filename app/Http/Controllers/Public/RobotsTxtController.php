<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

/**
 * Gera o robots.txt a partir da plataforma (fonte da verdade) reunindo:
 * preâmbulo de reserva de direitos, content signals, regras do site e a lista
 * de crawlers de IA bloqueados. O Cloudflare deve apenas espelhar este origin
 * (passthrough), sem o Managed robots.txt sobrescrevendo a resposta.
 *
 * @see config/robots.php
 */
final class RobotsTxtController extends Controller
{
    public function __invoke(): Response
    {
        $lines = [
            ...$this->preamble(),
            ...$this->primaryGroup(),
            ...$this->aiBotGroups(),
            'Sitemap: ' . url('/sitemap.xml'),
        ];

        return response(implode("\n", $lines) . "\n")
            ->header('Content-Type', 'text/plain; charset=utf-8');
    }

    /**
     * Texto de reserva de direitos (TDM opt-out) que acompanha os content signals.
     *
     * @return list<string>
     */
    private function preamble(): array
    {
        if (!config('robots.include_preamble')) {
            return [];
        }

        return [
            '# As a condition of accessing this website, you agree to abide by the following',
            '# content signals:',
            '#',
            '# (a)  If a Content-Signal = yes, you may collect content for the corresponding use.',
            '# (b)  If a Content-Signal = no, you may not collect content for the corresponding use.',
            '# (c)  If the website operator does not include a Content-Signal for a corresponding',
            '#      use, the website operator neither grants nor restricts permission.',
            '#',
            '# search:   building a search index and providing search results.',
            '# ai-input: inputting content into one or more AI models (e.g., RAG, grounding).',
            '# ai-train: training or fine-tuning AI models.',
            '#',
            '# ANY RESTRICTIONS EXPRESSED VIA CONTENT SIGNALS ARE EXPRESS RESERVATIONS OF RIGHTS',
            '# UNDER ARTICLE 4 OF THE EUROPEAN UNION DIRECTIVE 2019/790 ON COPYRIGHT AND RELATED',
            '# RIGHTS IN THE DIGITAL SINGLE MARKET.',
            '',
        ];
    }

    /**
     * Grupo "User-agent: *": content signal, liberação geral e áreas privadas.
     *
     * @return list<string>
     */
    private function primaryGroup(): array
    {
        $lines = ['User-agent: *'];

        $signal = config('robots.content_signal');

        if (filled($signal)) {
            $lines[] = 'Content-Signal: ' . $signal;
        }

        $lines[] = 'Allow: /';

        foreach ((array) config('robots.disallow', []) as $path) {
            $lines[] = 'Disallow: ' . $path;
        }

        $lines[] = '';

        return $lines;
    }

    /**
     * Um grupo "Disallow: /" por crawler de IA bloqueado.
     *
     * @return list<string>
     */
    private function aiBotGroups(): array
    {
        $lines = [];

        foreach ((array) config('robots.blocked_ai_bots', []) as $bot) {
            $lines[] = 'User-agent: ' . $bot;
            $lines[] = 'Disallow: /';
            $lines[] = '';
        }

        return $lines;
    }
}
