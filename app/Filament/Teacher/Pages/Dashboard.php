<?php

namespace App\Filament\Teacher\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    /**
     * ✅ عمود واحد = كل ويدجت تأخذ عرض الصفحة الكامل
     */
    public function getColumns(): int|string|array
    {
        return 1;
    }

    /**
     * ✅ التحكم بترتيب الويدجت يدوياً
     */
    public function getWidgets(): array
    {
        return [
            \App\Filament\Teacher\Widgets\WeeklyStatsWidget::class,
            \App\Filament\Teacher\Widgets\WeeklyActivityChart::class,
            \App\Filament\Teacher\Widgets\TodayRecordsWidget::class,
            \App\Filament\Teacher\Widgets\WeeklyTopStudentsWidget::class,
            \App\Filament\Teacher\Widgets\PlanProgressWidget::class,
            \App\Filament\Teacher\Widgets\OverdueStudentsWidget::class,
            \App\Filament\Teacher\Widgets\InactiveStudentsWidget::class,
        ];
    }
}