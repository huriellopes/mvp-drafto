<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Admin\Analytics;

use App\Actions\Admin\GetSiteAnalyticsAction;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
final class SiteAnalytics extends Component
{
    public int $days = 7;

    /**
     * @throws \Exception
     */
    #[Computed]
    public function analytics()
    {
        return app(GetSiteAnalyticsAction::Class)
            ->handle(
                $this->days
            );
    }

    public function render() : View
    {
        return view('livewire.dashboard.admin.analytics.site-analytics');
    }
}
