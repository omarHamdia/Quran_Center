<?php

namespace App\Filament\Teacher\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

// ✅ عدّل أسماء الـ Models إذا مختلفة عندك
use App\Models\Teacher;
use App\Models\MemorizationRecord;

class WeeklyActivityChart extends ChartWidget
{
    protected static ?string $heading = 'نشاط آخر 7 أيام';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $teacherId = Teacher::query()
            ->where('user_id', auth()->id())
            ->value('id');

        $days = collect(range(0, 6))
            ->map(fn ($i) => Carbon::today()->subDays(6 - $i));

        $labels = $days->map(fn ($d) => $d->translatedFormat('l'))->values()->all();

        $hifz = [];
        $revision = [];

        foreach ($days as $d) {
            $records = MemorizationRecord::query()
                ->where('teacher_id', $teacherId)
                ->whereDate('session_date', $d)
                ->get();

            $hifzAyahs = $records->where('session_type', 'hifz')->sum(function ($r) {
                $from = (int) ($r->from_ayah ?? 0);
                $to = (int) ($r->to_ayah ?? 0);
                return ($from > 0 && $to >= $from) ? (($to - $from) + 1) : 0;
            });

            $revAyahs = $records->where('session_type', 'revision')->sum(function ($r) {
                $from = (int) ($r->from_ayah ?? 0);
                $to = (int) ($r->to_ayah ?? 0);
                return ($from > 0 && $to >= $from) ? (($to - $from) + 1) : 0;
            });

            $hifz[] = (int) $hifzAyahs;
            $revision[] = (int) $revAyahs;
        }

        return [
            'datasets' => [
                [
                    'label' => 'حفظ جديد (آيات)',
                    'data' => $hifz,
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'مراجعة (آيات)',
                    'data' => $revision,
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.35,
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
                    'position' => 'top',
                    'labels' => [
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'grid' => ['display' => false],
                ],
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}