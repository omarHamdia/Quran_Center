<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\MemorizationPlan;
use App\Models\MemorizationRecord;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    protected Carbon $today;

    public function __construct()
    {
        $this->today = now();
    }

    // ═══════════════════════════════════════════════════
    // إحصائيات المركز العامة
    // ═══════════════════════════════════════════════════

    public function getCenterStats(): array
    {
        $weekStart = $this->today->copy()->startOfWeek(Carbon::SATURDAY);
        $weekEnd   = $weekStart->copy()->addDays(6)->endOfDay();
        $monthStart = $this->today->copy()->startOfMonth();

        $totalActiveStudents = Student::where('status', 'active')->count();
        $totalTeachers       = Teacher::count();
        $activePlans         = MemorizationPlan::whereIn('status', ['pending', 'in_progress'])->count();

        $weeklySessions = MemorizationRecord::whereBetween('session_date', [$weekStart, $weekEnd])
            ->where('status', 'completed')
            ->count();

        $monthlyAyahs = MemorizationRecord::whereBetween('session_date', [$monthStart, $this->today])
            ->where('status', 'completed')
            ->sum('ayahs_count');

        $newStudentsThisMonth = Student::where('created_at', '>=', $monthStart)->count();

        return [
            'total_active_students'  => $totalActiveStudents,
            'total_teachers'         => $totalTeachers,
            'active_plans'           => $activePlans,
            'weekly_sessions'        => $weeklySessions,
            'monthly_ayahs'          => (int) $monthlyAyahs,
            'new_students_this_month' => $newStudentsThisMonth,
        ];
    }

    // ═══════════════════════════════════════════════════
    // بيانات الرسم البياني الشهري
    // ═══════════════════════════════════════════════════

    public function getMonthlyActivityData(): array
    {
        $start = $this->today->copy()->subDays(29)->startOfDay();

        $records = MemorizationRecord::where('status', 'completed')
            ->whereBetween('session_date', [$start, $this->today])
            ->select(DB::raw('DATE(session_date) as day'), DB::raw('COUNT(*) as cnt'))
            ->groupBy('day')
            ->pluck('cnt', 'day');

        $labels   = [];
        $sessions = [];

        for ($i = 29; $i >= 0; $i--) {
            $date     = $this->today->copy()->subDays($i)->toDateString();
            $labels[] = Carbon::parse($date)->format('d/m');
            $sessions[] = (int) ($records[$date] ?? 0);
        }

        return ['labels' => $labels, 'sessions' => $sessions];
    }

    // ═══════════════════════════════════════════════════
    // بيانات الآيات الأسبوعية (8 أسابيع)
    // ═══════════════════════════════════════════════════

    public function getWeeklyAyahsData(): array
    {
        $labels = [];
        $ayahs  = [];

        for ($i = 7; $i >= 0; $i--) {
            $weekStart = $this->today->copy()->subWeeks($i)->startOfWeek(Carbon::SATURDAY);
            $weekEnd   = $weekStart->copy()->addDays(6)->endOfDay();

            $total = MemorizationRecord::where('status', 'completed')
                ->whereBetween('session_date', [$weekStart, $weekEnd])
                ->sum('ayahs_count');

            $labels[] = 'أسبوع ' . $weekStart->format('d/m');
            $ayahs[]  = (int) $total;
        }

        return ['labels' => $labels, 'ayahs' => $ayahs];
    }

    // ═══════════════════════════════════════════════════
    // أداء المعلمين
    // ═══════════════════════════════════════════════════

    public function getTeachersPerformance(): Collection
    {
        $weekStart = $this->today->copy()->startOfWeek(Carbon::SATURDAY);
        $weekEnd   = $weekStart->copy()->addDays(6)->endOfDay();

        $evaluationScores = [
            'excellent'    => 5,
            'very_good'    => 4,
            'good'         => 3,
            'acceptable'   => 2,
            'needs_review' => 1,
        ];

        return Teacher::with('user')
            ->get()
            ->map(function (Teacher $teacher) use ($weekStart, $weekEnd, $evaluationScores) {
                $weeklyRecords = MemorizationRecord::where('teacher_id', $teacher->id)
                    ->whereBetween('session_date', [$weekStart, $weekEnd])
                    ->where('status', 'completed')
                    ->select('evaluation', 'ayahs_count')
                    ->get();

                $weeklySessions = $weeklyRecords->count();
                $weeklyAyahs    = $weeklyRecords->sum('ayahs_count');

                $evaluatedRecords = $weeklyRecords->filter(fn ($r) => isset($evaluationScores[$r->evaluation]));
                $avgScore = $evaluatedRecords->isNotEmpty()
                    ? round($evaluatedRecords->avg(fn ($r) => $evaluationScores[$r->evaluation] ?? 0), 1)
                    : 0;

                return [
                    'id'              => $teacher->id,
                    'name'            => $teacher->user->name,
                    'students_count'  => $teacher->students()->where('status', 'active')->count(),
                    'weekly_sessions' => $weeklySessions,
                    'weekly_ayahs'    => $weeklyAyahs,
                    'avg_score'       => $avgScore,
                ];
            });
    }

    // ═══════════════════════════════════════════════════
    // أفضل الطلاب هذا الشهر
    // ═══════════════════════════════════════════════════

    public function getTopStudents(): Collection
    {
        $monthStart = $this->today->copy()->startOfMonth();

        $evaluationLabels = [
            'excellent'    => 'ممتاز',
            'very_good'    => 'جيد جداً',
            'good'         => 'جيد',
            'acceptable'   => 'مقبول',
            'needs_review' => 'يحتاج مراجعة',
        ];

        $evaluationScores = [
            'excellent'    => 5,
            'very_good'    => 4,
            'good'         => 3,
            'acceptable'   => 2,
            'needs_review' => 1,
        ];

        $records = MemorizationRecord::with(['student.user', 'student.teacher.user'])
            ->where('status', 'completed')
            ->whereBetween('session_date', [$monthStart, $this->today])
            ->select('student_id', DB::raw('SUM(ayahs_count) as monthly_ayahs'))
            ->groupBy('student_id')
            ->orderByDesc('monthly_ayahs')
            ->limit(10)
            ->get();

        // Fetch evaluations separately to avoid GROUP_CONCAT length limits
        $studentIds = $records->pluck('student_id')->toArray();
        $evaluationsByStudent = MemorizationRecord::where('status', 'completed')
            ->whereBetween('session_date', [$monthStart, $this->today])
            ->whereIn('student_id', $studentIds)
            ->whereNotNull('evaluation')
            ->select('student_id', 'evaluation')
            ->get()
            ->groupBy('student_id');

        return $records->map(function ($record, $index) use ($evaluationLabels, $evaluationScores, $evaluationsByStudent) {
            $student = $record->student;

            $evaluations = $evaluationsByStudent->get($record->student_id, collect())
                ->filter(fn ($r) => isset($evaluationScores[$r->evaluation]));

            $avgScore = $evaluations->isNotEmpty()
                ? $evaluations->avg(fn ($r) => $evaluationScores[$r->evaluation] ?? 0)
                : 0;

            $avgLabel = $this->scoreToLabel((float) $avgScore, $evaluationLabels, $evaluationScores);

            return [
                'rank'            => $index + 1,
                'student_id'      => $record->student_id,
                'student_name'    => $student?->user?->name ?? '-',
                'teacher_name'    => $student?->teacher?->user?->name ?? '-',
                'monthly_ayahs'   => (int) $record->monthly_ayahs,
                'avg_evaluation'  => $avgLabel,
            ];
        });
    }

    private function scoreToLabel(float $score, array $labels, array $scores): string
    {
        if ($score <= 0) return '-';
        $closest = collect($scores)->map(fn ($v, $k) => ['key' => $k, 'diff' => abs($v - $score)])
            ->sortBy('diff')->first();
        return $labels[$closest['key']] ?? '-';
    }

    // ═══════════════════════════════════════════════════
    // الطلاب غير النشطين (7+ أيام)
    // ═══════════════════════════════════════════════════

    public function getInactiveStudents(): Collection
    {
        $threshold = $this->today->copy()->subDays(7);

        $students = Student::with(['user', 'teacher.user'])
            ->where('status', 'active')
            ->get();

        $lastSessions = MemorizationRecord::where('status', 'completed')
            ->whereIn('student_id', $students->pluck('id'))
            ->select('student_id', DB::raw('MAX(session_date) as last_date'))
            ->groupBy('student_id')
            ->pluck('last_date', 'student_id');

        return $students
            ->filter(function (Student $student) use ($threshold, $lastSessions) {
                $lastDate = $lastSessions[$student->id] ?? null;
                return $lastDate === null || Carbon::parse($lastDate)->lt($threshold);
            })
            ->map(function (Student $student) use ($lastSessions) {
                $lastDate = $lastSessions[$student->id] ?? null;

                return [
                    'student_id'       => $student->id,
                    'student_name'     => $student->user?->name ?? '-',
                    'teacher_name'     => $student->teacher?->user?->name ?? '-',
                    'teacher_id'       => $student->teacher_id,
                    'last_session_date' => $lastDate ? Carbon::parse($lastDate)->format('Y/m/d') : 'لا يوجد',
                    'days_inactive'    => $lastDate
                        ? (int) Carbon::parse($lastDate)->diffInDays($this->today)
                        : null,
                ];
            })
            ->sortByDesc('days_inactive')
            ->values();
    }

    // ═══════════════════════════════════════════════════
    // توزيع الطلاب على المعلمين
    // ═══════════════════════════════════════════════════

    public function getTeacherStudentsDistribution(): Collection
    {
        return Teacher::with('user')
            ->get()
            ->map(function (Teacher $teacher) {
                $studentsCount = $teacher->students()->where('status', 'active')->count();
                $maxStudents   = $teacher->max_students;
                $percentage    = ($maxStudents > 0)
                    ? min(100, round(($studentsCount / $maxStudents) * 100))
                    : null;

                return [
                    'name'           => $teacher->user->name,
                    'students_count' => $studentsCount,
                    'max_students'   => $teacher->max_students,
                    'percentage'     => $percentage,
                ];
            });
    }

    // ═══════════════════════════════════════════════════
    // توزيع الطلاب حسب المستوى
    // ═══════════════════════════════════════════════════

    public function getStudentsLevelDistribution(): array
    {
        $levels = ['beginner', 'elementary', 'intermediate', 'advanced', 'memorizer'];
        $counts = Student::where('status', 'active')
            ->select('current_level', DB::raw('COUNT(*) as cnt'))
            ->groupBy('current_level')
            ->pluck('cnt', 'current_level');

        $result = [];
        foreach ($levels as $level) {
            $result[$level] = (int) ($counts[$level] ?? 0);
        }

        return $result;
    }

    // ═══════════════════════════════════════════════════
    // ملخص الحضور الأسبوعي
    // ═══════════════════════════════════════════════════

    public function getWeeklyAttendanceSummary(): array
    {
        $arabicDays = [
            'Saturday'  => 'السبت',
            'Sunday'    => 'الأحد',
            'Monday'    => 'الاثنين',
            'Tuesday'   => 'الثلاثاء',
            'Wednesday' => 'الأربعاء',
            'Thursday'  => 'الخميس',
            'Friday'    => 'الجمعة',
        ];

        $result = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = $this->today->copy()->subDays($i)->toDateString();
            $dayName = Carbon::parse($date)->format('l');

            $daySummary = Attendance::where('date', $date)
                ->select('status', DB::raw('COUNT(*) as cnt'))
                ->groupBy('status')
                ->pluck('cnt', 'status');

            $present  = (int) ($daySummary['present'] ?? 0);
            $absent   = (int) ($daySummary['absent'] ?? 0);
            $late     = (int) ($daySummary['late'] ?? 0);
            $excused  = (int) ($daySummary['excused'] ?? 0);
            $total    = $present + $absent + $late + $excused;
            $rate     = $total > 0 ? round((($present + $late) / $total) * 100) : 0;

            $result[] = [
                'date'            => Carbon::parse($date)->format('d/m'),
                'day_name'        => $arabicDays[$dayName] ?? $dayName,
                'present'         => $present,
                'absent'          => $absent,
                'late'            => $late,
                'excused'         => $excused,
                'attendance_rate' => $rate,
            ];
        }

        return $result;
    }

    // ═══════════════════════════════════════════════════
    // الخطط المتأخرة
    // ═══════════════════════════════════════════════════

    public function getOverduePlans(): Collection
    {
        return MemorizationPlan::with(['student.user', 'teacher.user'])
            ->where('end_date', '<', $this->today->toDateString())
            ->whereNotIn('status', ['completed'])
            ->orderBy('end_date')
            ->get()
            ->map(function (MemorizationPlan $plan) {
                return [
                    'student_name'        => $plan->student?->user?->name ?? '-',
                    'teacher_name'        => $plan->teacher?->user?->name ?? '-',
                    'plan_title'          => $plan->title ?? 'خطة حفظ',
                    'progress_percentage' => (float) ($plan->progress_percentage ?? 0),
                    'end_date'            => Carbon::parse($plan->end_date)->format('Y/m/d'),
                    'days_overdue'        => (int) Carbon::parse($plan->end_date)->diffInDays($this->today),
                ];
            });
    }

    // ═══════════════════════════════════════════════════
    // آخر سجلات التحفيظ
    // ═══════════════════════════════════════════════════

    public function getRecentRecords(): Collection
    {
        $evaluationLabels = [
            'excellent'    => 'ممتاز',
            'very_good'    => 'جيد جداً',
            'good'         => 'جيد',
            'acceptable'   => 'مقبول',
            'needs_review' => 'يحتاج مراجعة',
        ];

        $sessionTypeLabels = [
            'hifz'     => 'حفظ جديد',
            'revision' => 'مراجعة',
            'test'     => 'اختبار',
        ];

        return MemorizationRecord::with(['student.user', 'teacher.user', 'surah'])
            ->where('status', 'completed')
            ->orderByDesc('session_date')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function (MemorizationRecord $record) use ($evaluationLabels, $sessionTypeLabels) {
                return [
                    'student_name' => $record->student?->user?->name ?? '-',
                    'teacher_name' => $record->teacher?->user?->name ?? '-',
                    'surah_name'   => $record->surah?->name_arabic ?? $record->surah?->name ?? '-',
                    'ayahs_count'  => (int) $record->ayahs_count,
                    'evaluation'   => $evaluationLabels[$record->evaluation] ?? ($record->evaluation ?? '-'),
                    'session_date' => Carbon::parse($record->session_date)->format('Y/m/d'),
                    'session_type' => $sessionTypeLabels[$record->session_type] ?? ($record->session_type ?? '-'),
                ];
            });
    }
}
