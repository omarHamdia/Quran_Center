<?php

namespace App\Filament\Resources\ReportResource\Widgets;

use App\Models\MemorizationRecord;
use Filament\Widgets\ChartWidget;

class EvaluationDoughnutChart extends ChartWidget
{
    protected static ?string $heading = 'توزيع التقييمات';
    protected static ?string $description = 'آخر 30 يوم';
    
    protected int | string | array $columnSpan = 1;
    protected static ?string $maxHeight = '300px';

    public ?int $teacherId = null;

    protected function getData(): array
    {
        $query = MemorizationRecord::where('session_date', '>=', now()->subDays(30));
        
        if ($this->teacherId) {
            $query->where('teacher_id', $this->teacherId);
        }
        
        $records = $query->get();

        return [
            'datasets' => [
                [
                    'data' => [
                        $records->where('evaluation', 'excellent')->count(),
                        $records->where('evaluation', 'very_good')->count(),
                        $records->where('evaluation', 'good')->count(),
                        $records->where('evaluation', 'acceptable')->count(),
                        $records->where('evaluation', 'needs_review')->count(),
                    ],
                    'backgroundColor' => [
                        '#10b981', // green - ممتاز
                        '#3b82f6', // blue - جيد جداً
                        '#8b5cf6', // purple - جيد
                        '#f59e0b', // amber - مقبول
                        '#ef4444', // red - يحتاج مراجعة
                    ],
                    'borderWidth' => 0,
                    'cutout' => '60%',
                ],
            ],
            'labels' => ['ممتاز', 'جيد جداً', 'جيد', 'مقبول', 'يحتاج مراجعة'],
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
                    'display' => true,
                    'position' => 'bottom',
                    'rtl' => true,
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 20,
                    ],
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}