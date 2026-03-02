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
                    'backgroundColor' => 'rgba(16, 185, 129, 0.15)',
                    'borderColor' => '#10b981',
                    'borderWidth' => 2,
                    'tension' => 0.35,
                    'fill' => true,
                    'pointBackgroundColor' => '#10b981',
                    'pointBorderColor' => '#fff',
                    'pointBorderWidth' => 2,
                    'pointRadius' => 4,
                ],
                [
                    'label' => 'مراجعة (آيات)',
                    'data' => $chartData['revision'],
                    'backgroundColor' => 'rgba(14, 165, 233, 0.15)',
                    'borderColor' => '#0ea5e9',
                    'borderWidth' => 2,
                    'tension' => 0.35,
                    'fill' => true,
                    'pointBackgroundColor' => '#0ea5e9',
                    'pointBorderColor' => '#fff',
                    'pointBorderWidth' => 2,
                    'pointRadius' => 4,
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
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                        'padding' => 20,
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'grid' => ['display' => false],
                    'ticks' => ['color' => '#94a3b8'],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                        'color' => '#94a3b8',
                    ],
                    'grid' => ['color' => 'rgba(148, 163, 184, 0.15)'],
                ],
            ],
        ];
    }
}