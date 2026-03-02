<?php

namespace App\Filament\Widgets;

use App\Services\AdminDashboardService;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class RecentRecordsWidget extends Widget
{
    protected static ?string $heading = '📝 آخر سجلات التحفيظ';

    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $pollingInterval = '60s';

    protected static string $view = 'filament.widgets.recent-records-widget';

    public function getRecentRecordsData(): Collection
    {
        $service = new AdminDashboardService();
        return $service->getRecentRecords();
    }
}
