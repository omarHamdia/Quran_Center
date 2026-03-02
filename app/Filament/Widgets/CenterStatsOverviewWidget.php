<?php

namespace App\Filament\Widgets;

use App\Services\AdminDashboardService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CenterStatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $service = new AdminDashboardService();
        $stats   = $service->getCenterStats();

        return [
            Stat::make('الطلاب النشطون', $stats['total_active_students'])
                ->description('إجمالي الطلاب المسجلين والنشطين')
                ->descriptionIcon('heroicon-m-user-group')
                ->icon('heroicon-o-user-group')
                ->color('success'),

            Stat::make('المعلمون', $stats['total_teachers'])
                ->description('إجمالي المعلمين في المركز')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->icon('heroicon-o-academic-cap')
                ->color('primary'),

            Stat::make('الخطط النشطة', $stats['active_plans'])
                ->description('خطط قيد التنفيذ')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('info'),

            Stat::make('جلسات الأسبوع', $stats['weekly_sessions'])
                ->description('الجلسات المكتملة هذا الأسبوع')
                ->descriptionIcon('heroicon-m-calendar')
                ->icon('heroicon-o-calendar')
                ->color('warning'),

            Stat::make('آيات هذا الشهر', number_format($stats['monthly_ayahs']))
                ->description('مجموع الآيات المحفوظة والمراجعة')
                ->descriptionIcon('heroicon-m-book-open')
                ->icon('heroicon-o-book-open')
                ->color('success'),

            Stat::make('طلاب جدد', $stats['new_students_this_month'])
                ->description('انضموا هذا الشهر')
                ->descriptionIcon('heroicon-m-user-plus')
                ->icon('heroicon-o-user-plus')
                ->color('primary'),
        ];
    }
}
