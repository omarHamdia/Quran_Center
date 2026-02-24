<?php

namespace App\Filament\Teacher\Widgets;

use App\Models\Student;
use App\Models\Teacher;
use Carbon\Carbon;
use Filament\Support\Concerns\HasHeading;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WeeklyTopStudentsWidget extends Widget
{
    use HasHeading;

    protected static string $view = 'filament.teacher.widgets.weekly-top-students-widget';

    // ✅ حتى لا يظهر بجانب ويدجت أخرى
    protected int|string|array $columnSpan = 'full';

    protected function getHeading(): string
    {
        return 'أفضل الطلاب (آخر 7 أيام)';
    }

    public function getViewData(): array
    {
        [$start, $end] = $this->periodLast7Days();

        return [
            'periodLabel' => $this->periodLabel($start, $end),
            'students'    => $this->getRankedStudents($start, $end),
        ];
    }

    private function periodLast7Days(): array
    {
        $end = Carbon::now()->endOfDay();
        $start = Carbon::now()->subDays(6)->startOfDay(); // آخر 7 أيام (يشمل اليوم)
        return [$start, $end];
    }

    private function periodLabel(Carbon $start, Carbon $end): string
    {
        // مثال: 17 Feb 2026 → 23 Feb 2026
        return $start->translatedFormat('j M Y') . ' → ' . $end->translatedFormat('j M Y');
    }

    private function getTeacherId(): ?int
    {
        $userId = Auth::id();
        if (!$userId) return null;

        return Teacher::query()
            ->where('user_id', $userId)
            ->value('id');
    }

    /**
     * ✅ يرجع طلاب الحلقة مرتبين حسب:
     * 1) مجموع الآيات (الأكثر)
     * 2) مجموع الأخطاء (الأقل)
     * 3) عدد الجلسات (الأكثر)
     *
     * وإذا ما فيه سجلات ضمن الفترة → يرجع طلاب الحلقة (0) بدل ما تكون الودجت فاضية.
     */
    private function getRankedStudents(Carbon $start, Carbon $end): Collection
    {
        $teacherId = $this->getTeacherId();
        if (!$teacherId) {
            return collect();
        }

        // 1) طلاب الحلقة (حتى لو ما فيه تسميع)
        $baseStudents = Student::query()
            ->where('teacher_id', $teacherId)
            ->with(['user:id,name,phone'])
            ->get()
            ->keyBy('id');

        // إذا ما عنده طلاب أصلاً
        if ($baseStudents->isEmpty()) {
            return collect();
        }

        // 2) تجميع سجلات آخر 7 أيام
        $agg = DB::table('memorization_records')
            ->whereNull('deleted_at')
            ->where('teacher_id', $teacherId)
            ->whereDate('session_date', '>=', $start->toDateString())
            ->whereDate('session_date', '<=', $end->toDateString())
            ->selectRaw('
                student_id,
                COUNT(*) as sessions_count,
                COALESCE(SUM(mistakes_count), 0) as total_mistakes,
                COALESCE(SUM(
                    CASE
                        WHEN from_ayah IS NOT NULL AND to_ayah IS NOT NULL AND to_ayah >= from_ayah
                            THEN (to_ayah - from_ayah + 1)
                        ELSE 0
                    END
                ), 0) as total_ayahs
            ')
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id');

        // 3) دمج التجميع مع طلاب الحلقة (حتى من ليس له تسميع يظهر بقيم 0)
        $rows = $baseStudents->map(function ($student) use ($agg) {
            $a = $agg->get($student->id);

            return [
                'student_id'     => $student->id,
                'student_name'   => $student->user?->name ?? 'طالب',
                'phone'          => $student->user?->phone,
                'sessions_count' => (int) data_get($a, 'sessions_count', 0),
                'total_ayahs'    => (int) data_get($a, 'total_ayahs', 0),
                'total_mistakes' => (int) data_get($a, 'total_mistakes', 0),
            ];
        })->values();

        // 4) ترتيب + تحديد الرانك
        $sorted = $rows
            ->sortBy(fn ($r) => (int) $r['total_mistakes'])   // أخطاء أقل
            ->sortByDesc(fn ($r) => (int) $r['sessions_count']) // جلسات أكثر
            ->sortByDesc(fn ($r) => (int) $r['total_ayahs'])    // آيات أكثر (الأهم)
            ->values()
            ->map(function ($r, $i) {
                $r['rank'] = $i + 1;
                return $r;
            });

        return $sorted;
    }
}