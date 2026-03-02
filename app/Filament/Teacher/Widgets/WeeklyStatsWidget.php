<?php

namespace App\Filament\Teacher\Widgets;

use App\Services\TeacherDashboardService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WeeklyStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $teacherId = TeacherDashboardService::getTeacherId();

        if (!$teacherId) {
            return [];
        }

        $service = new TeacherDashboardService($teacherId);
        $stats = $service->getWeeklyStats();

        return [
            Stat::make('جلسات الأسبوع', $stats['total_sessions'])
                ->description('إجمالي الجلسات المكتملة')
                ->descriptionIcon('heroicon-m-calendar')
                ->icon('heroicon-o-calendar')
                ->color('success'),

            Stat::make('آيات حفظ', $stats['total_memorized'])
                ->description('حفظ جديد هذا الأسبوع')
                ->descriptionIcon('heroicon-m-book-open')
                ->icon('heroicon-o-book-open')
                ->color('info'),

            Stat::make('آيات مراجعة', $stats['total_revision'])
                ->description('مراجعة هذا الأسبوع')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->icon('heroicon-o-arrow-path')
                ->color('warning'),

            Stat::make('طلاب نشطون', $stats['active_students'])
                ->description('طلاب لديهم تسميع')
                ->descriptionIcon('heroicon-m-user-group')
                ->icon('heroicon-o-user-group')
                ->color('primary'),

            Stat::make('الهدف المتبقي', $stats['remaining'])
                ->description("من {$stats['total_target']} آية")
                ->descriptionIcon('heroicon-m-flag')
                ->icon('heroicon-o-flag')
                ->color($stats['remaining'] > 0 ? 'danger' : 'success'),

            Stat::make('المقترح اليوم', $stats['suggested_today'] . ' آية')
                ->description('لتحقيق الهدف الأسبوعي')
                ->descriptionIcon('heroicon-m-light-bulb')
                ->icon('heroicon-o-light-bulb')
                ->color('info'),
        ];
    }
}