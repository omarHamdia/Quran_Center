<?php

namespace App\Filament\Widgets;

use App\Services\AdminDashboardService;
use Filament\Widgets\Widget;

class WeeklyAttendanceSummaryWidget extends Widget
{
    protected static ?string $heading = '📅 ملخص الحضور الأسبوعي';

    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $pollingInterval = '60s';

    protected static string $view = 'filament.widgets.weekly-attendance-summary-widget';

    public function getAttendanceData(): array
    {
        $service = new AdminDashboardService();
        return $service->getWeeklyAttendanceSummary();
    }
}
