<?php

namespace App\Filament\Teacher\Widgets;

use App\Services\TeacherDashboardService;
use Filament\Widgets\ChartWidget;

class WeeklyActivityChart extends ChartWidget
{
    protected static ?string $heading = '📈 نشاط آخر 7 أيام';

    protected static ?int $sort = 2;

    protected static ?string $maxHeight = '280px';

    protected static ?string $pollingInterval = '60s';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $teacherId = TeacherDashboardService::getTeacherId();

        if (!$teacherId) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $service = new TeacherDashboardService($teacherId);
        $chartData = $service->getWeeklyChartData();

        return [
            'datasets' => [
                [
                    'label' => 'حفظ جديد (آيات)',
                    'data' => $chartData['hifz'],
                    'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 2,
                    'tension' => 0.3,
                    'fill' => true,
                ],
                [
                    'label' => 'مراجعة (آيات)',
                    'data' => $chartData['revision'],
                    'backgroundColor' => 'rgba(14, 165, 233, 0.2)',
                    'borderColor' => 'rgb(14, 165, 233)',
                    'borderWidth' => 2,
                    'tension' => 0.3,
                    'fill' => true,
                ],
            ],
            'labels' => $chartData['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                    'rtl' => true,
                    'labels' => [
                        'font' => ['family' => 'inherit'],
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
