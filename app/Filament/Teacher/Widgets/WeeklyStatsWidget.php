<?php

namespace App\Filament\Teacher\Widgets;

use App\Services\TeacherDashboardService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WeeklyStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    // تحديث كل 30 ثانية
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $teacherId = TeacherDashboardService::getTeacherId();

        if (!$teacherId) {
            return [
                Stat::make('تنبيه', 'لا يوجد حساب معلم مرتبط')
                    ->color('danger'),
            ];
        }

        $service = new TeacherDashboardService($teacherId);
        $stats = $service->getWeeklyStats();

        return [
            Stat::make('📗 إجمالي الحفظ', $stats['total_memorized'] . ' آية')
                ->description("من أصل {$stats['total_target']} آية مستهدفة")
                ->descriptionIcon('heroicon-m-book-open')
                ->color('success')
                ->chart($this->getMiniChart($teacherId, 'hifz')),

            Stat::make('📘 إجمالي المراجعة', $stats['total_revision'] . ' آية')
                ->description("{$stats['total_sessions']} جلسة هذا الأسبوع")
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('info'),

            Stat::make('📊 الآيات المتبقية', $stats['remaining'] . ' آية')
                ->description($stats['remaining'] > 0 ? 'لم تُنجز بعد' : '✅ تم إنجاز الهدف!')
                ->descriptionIcon($stats['remaining'] > 0 ? 'heroicon-m-clock' : 'heroicon-m-check-circle')
                ->color($stats['remaining'] > 0 ? 'warning' : 'success'),

            Stat::make('🎯 الهدف المقترح لليوم', $stats['suggested_today'] . ' آية')
                ->description('(المتبقي ÷ الأيام المتبقية)')
                ->descriptionIcon('heroicon-m-light-bulb')
                ->color('primary'),
        ];
    }

    /**
     * رسم بياني مصغّر لآخر 7 أيام (يظهر داخل البطاقة)
     */
    private function getMiniChart(int $teacherId, string $type): array
    {
        $service = new TeacherDashboardService($teacherId);
        $chartData = $service->getWeeklyChartData();

        return $chartData[$type] ?? [];
    }
}
