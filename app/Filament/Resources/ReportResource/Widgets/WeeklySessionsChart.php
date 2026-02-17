<?php

namespace App\Filament\Resources\ReportResource\Widgets;

use App\Models\Teacher;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class WeeklySessionsChart extends ChartWidget
{
    protected static ?string $heading = 'جلسات الأسبوع';
    protected static ?string $description = 'عدد جلسات التسميع اليومية';
    
    protected int | string | array $columnSpan = 1;
    protected static ?string $maxHeight = '200px';

    public ?int $teacherId = null;

    protected function getData(): array
    {
        $data = [];
        $labels = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $labels[] = $day->translatedFormat('D');
            
            $query = \App\Models\MemorizationRecord::whereDate('session_date', $day);
            
            if ($this->teacherId) {
                $query->where('teacher_id', $this->teacherId);
            }
            
            $data[] = $query->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'الجلسات',
                    'data' => $data,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 3,
                    'pointBackgroundColor' => '#10b981',
                ],
            ],
            'labels' => $labels,
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
                    'display' => false,
                ],
            ],
            'scales' => [
                'x' => [
                    'display' => true,
                    'grid' => [
                        'display' => false,
                    ],
                ],
                'y' => [
                    'display' => true,
                    'beginAtZero' => true,
                    'grid' => [
                        'color' => 'rgba(156, 163, 175, 0.2)',
                    ],
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}