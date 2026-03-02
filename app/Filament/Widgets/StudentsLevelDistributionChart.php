<?php

namespace App\Filament\Widgets;

use App\Services\AdminDashboardService;
use Filament\Widgets\ChartWidget;

class StudentsLevelDistributionChart extends ChartWidget
{
    protected static ?string $heading = '📊 توزيع الطلاب حسب المستوى';

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 1;

    protected static ?string $pollingInterval = '60s';

    protected static ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $service = new AdminDashboardService();
        $data    = $service->getStudentsLevelDistribution();

        $levelLabels = [
            'beginner'     => 'مبتدئ',
            'elementary'   => 'أساسي',
            'intermediate' => 'متوسط',
            'advanced'     => 'متقدم',
            'memorizer'    => 'حافظ',
        ];

        $colors = ['#94a3b8', '#0ea5e9', '#f59e0b', '#10b981', '#8b5cf6'];

        return [
            'datasets' => [
                [
                    'label'           => 'عدد الطلاب',
                    'data'            => array_values($data),
                    'backgroundColor' => $colors,
                    'borderWidth'     => 2,
                    'borderColor'     => '#fff',
                ],
            ],
            'labels' => array_map(fn ($k) => $levelLabels[$k] ?? $k, array_keys($data)),
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
