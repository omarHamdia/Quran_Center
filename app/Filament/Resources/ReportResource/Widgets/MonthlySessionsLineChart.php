<?php

namespace App\Filament\Resources\ReportResource\Widgets;

use App\Models\MemorizationRecord;
use Filament\Widgets\ChartWidget;

class MonthlySessionsLineChart extends ChartWidget
{
    protected static ?string $heading = 'الجلسات الشهرية';
    protected static ?string $description = 'آخر 6 أشهر';
    
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '300px';

    public ?int $teacherId = null;

    protected function getData(): array
    {
        $sessionsData = [];
        $ayahsData = [];
        $labels = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[] = $month->translatedFormat('M Y');
            
            $query = MemorizationRecord::whereYear('session_date', $month->year)
                ->whereMonth('session_date', $month->month);
            
            if ($this->teacherId) {
                $query->where('teacher_id', $this->teacherId);
            }
            
            $records = $query->get();
            $sessionsData[] = $records->count();
            $ayahsData[] = $records->where('session_type', 'hifz')->sum('ayahs_count');
        }

        return [
            'datasets' => [
                [
                    'label' => 'الجلسات',
                    'data' => $sessionsData,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                    'pointRadius' => 4,
                    'pointBackgroundColor' => '#3b82f6',
                    'pointBorderColor' => '#fff',
                    'pointBorderWidth' => 2,
                ],
                [
                    'label' => 'الآيات',
                    'data' => $ayahsData,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'transparent',
                    'fill' => false,
                    'tension' => 0.3,
                    'pointRadius' => 4,
                    'pointBackgroundColor' => '#10b981',
                    'pointBorderColor' => '#fff',
                    'pointBorderWidth' => 2,
                    'yAxisID' => 'y1',
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
                    'display' => true,
                    'position' => 'bottom',
                    'rtl' => true,
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
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'الجلسات',
                    ],
                    'grid' => [
                        'color' => 'rgba(156, 163, 175, 0.2)',
                    ],
                ],
                'y1' => [
                    'beginAtZero' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'الآيات',
                    ],
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}