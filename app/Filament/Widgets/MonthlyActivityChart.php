<?php

namespace App\Filament\Widgets;

use App\Services\AdminDashboardService;
use Filament\Widgets\ChartWidget;

class MonthlyActivityChart extends ChartWidget
{
    protected static ?string $heading = '📈 نشاط الجلسات - آخر 30 يوماً';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $pollingInterval = '60s';

    protected static ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $service   = new AdminDashboardService();
        $chartData = $service->getMonthlyActivityData();

        return [
            'datasets' => [
                [
                    'label'                => 'عدد الجلسات',
                    'data'                 => $chartData['sessions'],
                    'backgroundColor'      => 'rgba(16, 185, 129, 0.15)',
                    'borderColor'          => '#10b981',
                    'borderWidth'          => 2,
                    'tension'              => 0.35,
                    'fill'                 => true,
                    'pointBackgroundColor' => '#10b981',
                    'pointBorderColor'     => '#fff',
                    'pointBorderWidth'     => 2,
                    'pointRadius'          => 3,
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
                    'display'  => true,
                    'position' => 'top',
                    'rtl'      => true,
                    'labels'   => [
                        'usePointStyle' => true,
                        'pointStyle'    => 'circle',
                        'padding'       => 20,
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'grid'  => ['display' => false],
                    'ticks' => ['color' => '#94a3b8'],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'ticks'       => ['precision' => 0, 'color' => '#94a3b8'],
                    'grid'        => ['color' => 'rgba(148, 163, 184, 0.15)'],
                ],
            ],
        ];
    }
}
