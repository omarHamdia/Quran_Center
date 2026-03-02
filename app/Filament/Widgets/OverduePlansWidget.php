<?php

namespace App\Filament\Widgets;

use App\Services\AdminDashboardService;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class OverduePlansWidget extends Widget
{
    protected static ?string $heading = '⚠️ الخطط المتأخرة';

    protected static ?int $sort = 9;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $pollingInterval = '60s';

    protected static string $view = 'filament.widgets.overdue-plans-widget';

    public function getOverduePlansData(): Collection
    {
        $service = new AdminDashboardService();
        return $service->getOverduePlans();
    }
}
