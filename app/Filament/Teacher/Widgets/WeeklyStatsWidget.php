<?php

namespace App\Filament\Teacher\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

// ✅ عدّل أسماء الـ Models إذا مختلفة عندك
use App\Models\Teacher;
use App\Models\MemorizationRecord;
use Illuminate\Support\Carbon;

class WeeklyStatsWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $teacherId = Teacher::query()
            ->where('user_id', auth()->id())
            ->value('id');

        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek   = Carbon::now()->endOfWeek();

        $records = MemorizationRecord::query()
            ->where('teacher_id', $teacherId)
            ->whereBetween('session_date', [$startOfWeek, $endOfWeek])
            ->get();

        $totalSessions = $records->count();

        $totalAyahs = $records->sum(function ($r) {
            $from = (int) ($r->from_ayah ?? 0);
            $to = (int) ($r->to_ayah ?? 0);
            return ($from > 0 && $to >= $from) ? (($to - $from) + 1) : 0;
        });

        $studentsWithRecords = $records->pluck('student_id')->unique()->count();

        $avgMistakes = $records->avg('mistakes_count') ?? 0;

        return [
            Stat::make('جلسات هذا الأسبوع', $totalSessions)
                ->description('إجمالي الجلسات')
                ->icon('heroicon-o-calendar'),

            Stat::make('آيات هذا الأسبوع', (int) $totalAyahs)
                ->description('مجموع الآيات')
                ->icon('heroicon-o-book-open'),

            Stat::make('طلاب شاركوا', $studentsWithRecords)
                ->description('طلاب لديهم تسميع')
                ->icon('heroicon-o-user-group'),

            Stat::make('متوسط الأخطاء', round($avgMistakes, 1))
                ->description('متوسط لكل جلسة')
                ->icon('heroicon-o-exclamation-triangle'),
        ];
    }
}