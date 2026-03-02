<?php

namespace App\Filament\Widgets;

use App\Services\AdminDashboardService;
use Filament\Widgets\ChartWidget;

class TeacherStudentsDistributionChart extends ChartWidget
{
    protected static ?string $heading = '👩‍🏫 توزيع الطلاب على المعلمين';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;

    protected static ?string $pollingInterval = '60s';

    protected static ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $service = new AdminDashboardService();
        $data    = $service->getTeacherStudentsDistribution();

        $colors = [
            '#10b981', '#0ea5e9', '#f59e0b', '#8b5cf6',
            '#ec4899', '#14b8a6', '#f97316', '#6366f1',
        ];

        return [
            'datasets' => [
                [
                    'label'           => 'عدد الطلاب',
                    'data'            => $data->pluck('students_count')->values()->toArray(),
                    'backgroundColor' => array_slice($colors, 0, $data->count()),
                    'borderWidth'     => 2,
                    'borderColor'     => '#fff',
                ],
            ],
            'labels' => $data->pluck('name')->values()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display'  => true,
                    'position' => 'bottom',
                    'rtl'      => true,
                ],
            ],
        ];
    }
}
