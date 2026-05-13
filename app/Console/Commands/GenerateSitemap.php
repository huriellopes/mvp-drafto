<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Seo\GenerateSitemapAction;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('seo:generate-sitemap')]
#[Description('Generate the SEO sitemap for the application')]
class GenerateSitemap extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Generating sitemap...');

        app(GenerateSitemapAction::class)
            ->exec();

        $this->info('Sitemap generated successfully at public/sitemap.xml');
    }
}
