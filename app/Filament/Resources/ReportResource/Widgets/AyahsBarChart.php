<?php

namespace App\Filament\Resources\ReportResource\Widgets;

use App\Models\MemorizationRecord;
use Filament\Widgets\ChartWidget;

class AyahsBarChart extends ChartWidget
{
    protected static ?string $heading = 'الآيات المحفوظة';
    protected static ?string $description = 'آخر 7 أيام';
    
    protected int | string | array $columnSpan = 1;
    protected static ?string $maxHeight = '250px';

    public ?int $teacherId = null;

    protected function getData(): array
    {
        $data = [];
        $labels = [];
        $colors = [
            'rgba(251, 191, 36, 0.8)',   // yellow
            'rgba(96, 165, 250, 0.8)',    // blue
            'rgba(52, 211, 153, 0.8)',    // green
            'rgba(251, 113, 133, 0.8)',   // pink
            'rgba(251, 191, 36, 0.8)',    // yellow
            'rgba(96, 165, 250, 0.8)',    // blue
            'rgba(52, 211, 153, 0.8)',    // green
        ];
        
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $labels[] = $day->translatedFormat('D');
            
            $query = MemorizationRecord::whereDate('session_date', $day)
                ->where('session_type', 'hifz');
            
            if ($this->teacherId) {
                $query->where('teacher_id', $this->teacherId);
            }
            
            $data[] = $query->sum('ayahs_count');
        }

        return [
            'datasets' => [
                [
                    'label' => 'الآيات',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderRadius' => 8,
                    'borderSkipped' => false,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
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
                    'grid' => [
                        'display' => false,
                    ],
                ],
                'y' => [
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