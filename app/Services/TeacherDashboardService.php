<?php

namespace App\Services;

use App\Models\MemorizationPlan;
use App\Models\MemorizationRecord;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TeacherDashboardService
{
    protected int $teacherId;
    protected Carbon $weekStart;
    protected Carbon $weekEnd;
    protected Carbon $today;

    public function __construct(int $teacherId)
    {
        $this->teacherId = $teacherId;
        $this->today = now();
        // بداية الأسبوع = السبت (الأسبوع العربي)
        $this->weekStart = $this->today->copy()->startOfWeek(Carbon::SATURDAY);
        $this->weekEnd = $this->weekStart->copy()->addDays(6)->endOfDay();
    }

    /**
     * جلب Teacher ID من المستخدم الحالي
     */
    public static function getTeacherId(): ?int
    {
        return Teacher::where('user_id', auth()->id())->value('id');
    }

    // ═══════════════════════════════════════════════════
    // 1) إحصائيات الأسبوع
    // ═══════════════════════════════════════════════════

    /**
     * إحصائيات الحفظ والمراجعة لهذا الأسبوع
     * استعلام واحد محسّن بدلاً من استعلامات متعددة
     */
    public function getWeeklyStats(): array
    {
        $stats = MemorizationRecord::where('teacher_id', $this->teacherId)
            ->whereBetween('session_date', [$this->weekStart, $this->weekEnd])
            ->where('status', 'completed')
            ->select([
                DB::raw("SUM(CASE WHEN session_type = 'hifz' THEN ayahs_count ELSE 0 END) as total_memorized"),
                DB::raw("SUM(CASE WHEN session_type = 'revision' THEN ayahs_count ELSE 0 END) as total_revision"),
                DB::raw("COUNT(DISTINCT student_id) as active_students"),
                DB::raw("COUNT(*) as total_sessions"),
            ])
            ->first();

        // مجموع الآيات المستهدفة من الخطط الأسبوعية النشطة
        $totalTarget = $this->getWeeklyTarget();

        $totalMemorized = (int) ($stats->total_memorized ?? 0);
        $remaining = max(0, $totalTarget - $totalMemorized);

        return [
            'total_memorized' => $totalMemorized,
            'total_revision' => (int) ($stats->total_revision ?? 0),
            'remaining' => $remaining,
            'total_target' => $totalTarget,
            'active_students' => (int) ($stats->active_students ?? 0),
            'total_sessions' => (int) ($stats->total_sessions ?? 0),
            'suggested_today' => $this->calculateSuggestedToday($remaining),
        ];
    }

    /**
     * الحصول على مجموع الآيات المستهدفة أسبوعياً
     * من جميع الخطط النشطة لطلاب هذا المعلم
     */
    private function getWeeklyTarget(): int
    {
        return (int) MemorizationPlan::where('teacher_id', $this->teacherId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->where('plan_type', 'weekly')
            ->sum('total_ayahs');
    }

    // ═══════════════════════════════════════════════════
    // 2) الهدف اليومي المقترح
    // ═══════════════════════════════════════════════════

    /**
     * حساب الهدف المقترح لليوم
     * المعادلة: الآيات المتبقية ÷ الأيام المتبقية في الأسبوع
     */
    private function calculateSuggestedToday(int $remaining): int
    {
        // حساب الأيام المتبقية (بما فيها اليوم)
        $remainingDays = max(1, $this->weekEnd->diffInDays($this->today) + 1);

        return (int) ceil($remaining / $remainingDays);
    }

    // ═══════════════════════════════════════════════════
    // 3) الطلاب المتأخرون عن الخطة
    // ═══════════════════════════════════════════════════

    /**
     * الطلاب المتأخرون: التقدم الفعلي < التقدم المتوقع
     *
     * المنطق:
     * - الأيام المنقضية من الأسبوع = daysPassed
     * - التقدم المتوقع = (total_ayahs / 7) * daysPassed
     * - التقدم الفعلي = مجموع ayahs_count للحفظ الجديد هذا الأسبوع
     * - إذا الفعلي < المتوقع ← الطالب متأخر
     */
    public function getOverdueStudents(): Collection
    {
        $daysPassed = $this->weekStart->diffInDays($this->today) + 1;

        // جلب الخطط الأسبوعية النشطة مع الطلاب
        $activePlans = MemorizationPlan::where('teacher_id', $this->teacherId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->where('plan_type', 'weekly')
            ->with('student.user')
            ->get();

        if ($activePlans->isEmpty()) {
            return collect();
        }

        // جلب التقدم الفعلي لكل طالب هذا الأسبوع (استعلام واحد)
        $studentProgress = MemorizationRecord::where('teacher_id', $this->teacherId)
            ->whereBetween('session_date', [$this->weekStart, $this->weekEnd])
            ->where('session_type', 'hifz')
            ->where('status', 'completed')
            ->groupBy('student_id')
            ->select([
                'student_id',
                DB::raw('SUM(ayahs_count) as actual_memorized'),
            ])
            ->pluck('actual_memorized', 'student_id');

        $overdueStudents = collect();

        foreach ($activePlans as $plan) {
            $expectedProgress = (int) ceil(($plan->total_ayahs / 7) * $daysPassed);
            $actualProgress = (int) ($studentProgress[$plan->student_id] ?? 0);
            $deficit = $expectedProgress - $actualProgress;

            if ($deficit > 0 && $plan->student) {
                $overdueStudents->push([
                    'student_name' => $plan->student->user->name ?? 'غير معروف',
                    'student_id' => $plan->student_id,
                    'expected' => $expectedProgress,
                    'actual' => $actualProgress,
                    'deficit' => $deficit,
                    'plan_title' => $plan->title,
                    'total_target' => $plan->total_ayahs,
                ]);
            }
        }

        return $overdueStudents->sortByDesc('deficit')->values();
    }

    // ═══════════════════════════════════════════════════
    // 4) طلاب بدون نشاط (آخر 3 أيام)
    // ═══════════════════════════════════════���═══════════

    /**
     * الطلاب النشطون الذين لم يسجلوا أي جلسة خلال آخر 3 أيام
     */
    public function getInactiveStudents(): Collection
    {
        $threeDaysAgo = $this->today->copy()->subDays(3)->startOfDay();

        // الطلاب الذين لديهم نشاط خلال آخر 3 أيام
        $activeStudentIds = MemorizationRecord::where('teacher_id', $this->teacherId)
            ->where('session_date', '>=', $threeDaysAgo)
            ->distinct()
            ->pluck('student_id');

        // الطلاب النشطون بدون نشاط
        return Student::where('teacher_id', $this->teacherId)
            ->where('status', 'active')
            ->whereNotIn('id', $activeStudentIds)
            ->with('user')
            ->get()
            ->map(function ($student) {
                // آخر جلسة للطالب
                $lastSession = MemorizationRecord::where('student_id', $student->id)
                    ->where('teacher_id', $this->teacherId)
                    ->orderByDesc('session_date')
                    ->value('session_date');

                return [
                    'student_name' => $student->user->name ?? 'غير معروف',
                    'student_id' => $student->id,
                    'last_activity' => $lastSession
                        ? Carbon::parse($lastSession)->diffForHumans()
                        : 'لا يوجد نشاط مسبق',
                    'last_activity_date' => $lastSession
                        ? Carbon::parse($lastSession)->format('Y/m/d')
                        : null,
                ];
            });
    }

    // ═══════════════════════════════════════════════════
    // 5) بيانات الرسم البياني (آخر 7 أيام)
    // ═══════════════════════════════════════════════════

    /**
     * إحصائيات آخر 7 أيام للرسم البياني
     * استعلام واحد محسّن مع GROUP BY
     */
    public function getWeeklyChartData(): array
    {
        $sevenDaysAgo = $this->today->copy()->subDays(6)->startOfDay();

        $dailyStats = MemorizationRecord::where('teacher_id', $this->teacherId)
            ->where('session_date', '>=', $sevenDaysAgo)
            ->where('status', 'completed')
            ->groupBy('session_date', 'session_type')
            ->select([
                'session_date',
                'session_type',
                DB::raw('SUM(ayahs_count) as total_ayahs'),
                DB::raw('COUNT(*) as sessions_count'),
            ])
            ->orderBy('session_date')
            ->get();

        // تجهيز البيانات لـ 7 أيام
        $labels = [];
        $hifzData = [];
        $revisionData = [];

        $arabicDays = ['السبت', 'الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة'];

        for ($i = 6; $i >= 0; $i--) {
            $date = $this->today->copy()->subDays($i);
            $dateStr = $date->format('Y-m-d');

            // اسم اليوم بالعربية
            $dayOfWeek = $date->dayOfWeek; // 0=أحد ... 6=سبت
            $arabicIndex = match ($dayOfWeek) {
                Carbon::SATURDAY => 0,
                Carbon::SUNDAY => 1,
                Carbon::MONDAY => 2,
                Carbon::TUESDAY => 3,
                Carbon::WEDNESDAY => 4,
                Carbon::THURSDAY => 5,
                Carbon::FRIDAY => 6,
            };
            $labels[] = $arabicDays[$arabicIndex];

            // تجميع البيانات
            $dayRecords = $dailyStats->where('session_date', $dateStr);
            $hifzData[] = (int) $dayRecords->where('session_type', 'hifz')->sum('total_ayahs');
            $revisionData[] = (int) $dayRecords->where('session_type', 'revision')->sum('total_ayahs');
        }

        return [
            'labels' => $labels,
            'hifz' => $hifzData,
            'revision' => $revisionData,
        ];
    }
      public function getTodayRecords(): Collection
    {
        return MemorizationRecord::where('teacher_id', $this->teacherId)
            ->where('session_date', $this->today->toDateString())
            ->with(['student.user', 'surah'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($record) {
                $surahName = $record->surah?->name_arabic ?? $record->surah?->name ?? "سورة {$record->surah_id}";

                return [
                    'id' => $record->id,
                    'student_name' => $record->student?->user?->name ?? 'غير معروف',
                    'student_id' => $record->student_id,
                    'session_type' => $record->session_type,
                    'session_type_label' => match ($record->session_type) {
                        'hifz' => 'حفظ جديد',
                        'revision' => 'مراجعة',
                        'test' => 'اختبار',
                        default => $record->session_type,
                    },
                    'session_type_color' => match ($record->session_type) {
                        'hifz' => 'success',
                        'revision' => 'info',
                        'test' => 'warning',
                        default => 'gray',
                    },
                    'surah' => $surahName,
                    'ayah_range' => "{$record->from_ayah} - {$record->to_ayah}",
                    'ayahs_count' => $record->ayahs_count ?? 0,
                    'evaluation' => match ($record->evaluation) {
                        'excellent' => 'ممتاز',
                        'very_good' => 'جيد جداً',
                        'good' => 'جيد',
                        'acceptable' => 'مقبول',
                        'needs_review' => 'يحتاج مراجعة',
                        default => '-',
                    },
                    'evaluation_color' => match ($record->evaluation) {
                        'excellent' => 'success',
                        'very_good' => 'info',
                        'good' => 'primary',
                        'acceptable' => 'warning',
                        'needs_review' => 'danger',
                        default => 'gray',
                    },
                    'mistakes_count' => $record->mistakes_count ?? 0,
                    'status' => $record->status,
                ];
            });
    }

    /**
     * ملخص سريع لتسميع اليوم
     */
    public function getTodaySummary(): array
    {
        $records = MemorizationRecord::where('teacher_id', $this->teacherId)
            ->where('session_date', $this->today->toDateString())
            ->where('status', 'completed')
            ->select([
                DB::raw('COUNT(*) as total_sessions'),
                DB::raw("SUM(CASE WHEN session_type = 'hifz' THEN ayahs_count ELSE 0 END) as hifz_ayahs"),
                DB::raw("SUM(CASE WHEN session_type = 'revision' THEN ayahs_count ELSE 0 END) as revision_ayahs"),
                DB::raw('COUNT(DISTINCT student_id) as students_count'),
            ])
            ->first();

        return [
            'total_sessions' => (int) ($records->total_sessions ?? 0),
            'hifz_ayahs' => (int) ($records->hifz_ayahs ?? 0),
            'revision_ayahs' => (int) ($records->revision_ayahs ?? 0),
            'students_count' => (int) ($records->students_count ?? 0),
        ];
    }
}
