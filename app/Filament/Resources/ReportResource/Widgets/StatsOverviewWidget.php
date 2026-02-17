<?php

namespace App\Filament\Resources\ReportResource\Widgets;

use App\Models\MemorizationRecord;
use App\Models\Student;
use App\Models\Teacher;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    public ?int $teacherId = null;

    protected function getStats(): array
    {
        $weekStart = now()->startOfWeek();
        $lastWeekStart = now()->subWeek()->startOfWeek();
        $lastWeekEnd = now()->subWeek()->endOfWeek();

        // الجلسات
        $currentSessions = $this->getSessionsQuery()
            ->where('session_date', '>=', $weekStart)
            ->count();
        
        $lastWeekSessions = $this->getSessionsQuery()
            ->whereBetween('session_date', [$lastWeekStart, $lastWeekEnd])
            ->count();
        
        $sessionsChange = $lastWeekSessions > 0 
            ? round((($currentSessions - $lastWeekSessions) / $lastWeekSessions) * 100) 
            : 0;

        // الآيات
        $currentAyahs = $this->getSessionsQuery()
            ->where('session_date', '>=', $weekStart)
            ->where('session_type', 'hifz')
            ->sum('ayahs_count');
        
        $lastWeekAyahs = $this->getSessionsQuery()
            ->whereBetween('session_date', [$lastWeekStart, $lastWeekEnd])
            ->where('session_type', 'hifz')
            ->sum('ayahs_count');
        
        $ayahsChange = $lastWeekAyahs > 0 
            ? round((($currentAyahs - $lastWeekAyahs) / $lastWeekAyahs) * 100) 
            : 0;

        // الحضور
        $totalStudents = $this->teacherId 
            ? Student::where('teacher_id', $this->teacherId)->count()
            : Student::count();
        
        $studentsWithRecords = $this->getSessionsQuery()
            ->where('session_date', '>=', $weekStart)
            ->distinct('student_id')
            ->count('student_id');
        
        $attendanceRate = $totalStudents > 0 
            ? round(($studentsWithRecords / $totalStudents) * 100) 
            : 0;

        // Sparkline data
        $sessionsSparkline = $this->getWeeklySparklineData('count');
        $ayahsSparkline = $this->getWeeklySparklineData('ayahs');

        return [
            Stat::make('جلسات الأسبوع', $currentSessions)
                ->description($sessionsChange >= 0 ? "{$sessionsChange}% زيادة" : abs($sessionsChange) . "% انخفاض")
                ->descriptionIcon($sessionsChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($sessionsChange >= 0 ? 'success' : 'danger')
                ->chart($sessionsSparkline),

            Stat::make('الآيات المحفوظة', $currentAyahs)
                ->description($ayahsChange >= 0 ? "{$ayahsChange}% زيادة" : abs($ayahsChange) . "% انخفاض")
                ->descriptionIcon($ayahsChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($ayahsChange >= 0 ? 'success' : 'danger')
                ->chart($ayahsSparkline),

            Stat::make('نسبة الحضور', "{$attendanceRate}%")
                ->description("{$studentsWithRecords} من {$totalStudents} طالب")
                ->descriptionIcon('heroicon-m-user-group')
                ->color($attendanceRate >= 70 ? 'success' : ($attendanceRate >= 50 ? 'warning' : 'danger'))
                ->chart($this->getAttendanceSparkline()),
        ];
    }

    private function getSessionsQuery()
    {
        $query = MemorizationRecord::query();
        
        if ($this->teacherId) {
            $query->where('teacher_id', $this->teacherId);
        }
        
        return $query;
    }

    private function getWeeklySparklineData(string $type): array
    {
        $data = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $query = $this->getSessionsQuery()->whereDate('session_date', $day);
            
            if ($type === 'count') {
                $data[] = $query->count();
            } else {
                $data[] = $query->where('session_type', 'hifz')->sum('ayahs_count');
            }
        }
        
        return $data;
    }

    private function getAttendanceSparkline(): array
    {
        $data = [];
        $totalStudents = $this->teacherId 
            ? Student::where('teacher_id', $this->teacherId)->count()
            : Student::count();
        
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $present = $this->getSessionsQuery()
                ->whereDate('session_date', $day)
                ->distinct('student_id')
                ->count('student_id');
            
            $data[] = $totalStudents > 0 ? round(($present / $totalStudents) * 100) : 0;
        }
        
        return $data;
    }
}