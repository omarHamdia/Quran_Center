<?php

namespace App\Filament\Widgets;

use App\Services\AdminDashboardService;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class TeachersPerformanceWidget extends Widget
{
    protected static ?string $heading = '👨‍🏫 أداء المعلمين';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $pollingInterval = '60s';

    protected static string $view = 'filament.widgets.teachers-performance-widget';

    public function getTeachersData(): Collection
    {
        $service = new AdminDashboardService();
        return $service->getTeachersPerformance();
    }
}
