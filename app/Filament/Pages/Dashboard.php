<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getColumns(): int|string|array
    {
        return 2;
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\CenterStatsOverviewWidget::class,
            \App\Filament\Widgets\MonthlyActivityChart::class,
            \App\Filament\Widgets\TeachersPerformanceWidget::class,
            \App\Filament\Widgets\TopStudentsWidget::class,
            \App\Filament\Widgets\InactiveStudentsAlertWidget::class,
            \App\Filament\Widgets\TeacherStudentsDistributionChart::class,
            \App\Filament\Widgets\StudentsLevelDistributionChart::class,
            \App\Filament\Widgets\WeeklyAttendanceSummaryWidget::class,
            \App\Filament\Widgets\OverduePlansWidget::class,
            \App\Filament\Widgets\RecentRecordsWidget::class,
        ];
    }
}
