<?php

namespace App\Filament\Teacher\Pages;

use App\Models\MemorizationPlan;
use App\Models\MemorizationRecord;
use App\Models\Student;
use App\Models\Surah;
use App\Models\Teacher;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class TeacherStudentReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $title = 'تقرير الطالب';
    protected static ?string $navigationLabel = 'تقرير الطالب';
    protected static ?string $navigationGroup = 'التقارير';
    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'student-report';

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.teacher.pages.teacher-student-report';

    public ?Student $student = null;
    public array $studentInfo = [];
    public array $planSummary = [];
    public array $recentRecords = [];
    public array $monthSummary = [];
    public array $weeklyStats = [];

    public static function canAccess(): bool
    {
        return auth()->check()
            && ((auth()->user()->role->value ?? auth()->user()->role) === 'teacher');
    }

    public function mount(): void
    {
        $studentId = request()->query('student');

        if (!$studentId) {
            abort(404, 'لم يتم تحديد الطالب');
        }

        $teacherId = Teacher::where('user_id', auth()->id())->value('id');

        $this->student = Student::with([
            'user',
            'teacher.user',
            'memorizationPlans' => fn ($q) => $q->whereIn('status', ['pending', 'in_progress'])->latest(),
        ])->where('teacher_id', $teacherId)
          ->find($studentId);

        if (!$this->student) {
            abort(403, 'هذا الطالب ليس من طلابك');
        }

        $this->loadStudentInfo();
        $this->loadPlanSummary();
        $this->loadRecentRecords();
        $this->loadMonthSummary();
        $this->loadWeeklyStats();
    }

    public function getTitle(): string
    {
        return "تقرير الطالب: {$this->student->user->name}";
    }

    // ═══════════════════════════════════
    // تنزيل PDF لتسميع اليوم
    // ═══════════════════════════════════

    public function downloadTodayPdf()
    {
        $teacherId = Teacher::where('user_id', auth()->id())->value('id');
        $today = now()->toDateString();

        $records = MemorizationRecord::where('teacher_id', $teacherId)
            ->where('session_date', $today)
            ->with(['student.user', 'surah', 'toSurah'])
            ->orderBy('created_at')
            ->get();

        $teacherName = auth()->user()->name;

        $html = view('pdf.today-records', [
            'records' => $records,
            'teacherName' => $teacherName,
            'date' => now()->format('Y/m/d'),
            'dateHijri' => $today,
        ])->render();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'landscape');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, "تسميع-اليوم-{$today}.pdf");
    }

    // ═══════════════════════════════════
    // Data Loading Methods
    // ═══════════════════════════════════

    private function loadStudentInfo(): void
    {
        $this->studentInfo = [
            'name' => $this->student->user->name,
            'phone' => $this->student->user->phone ?? '-',
            'teacher' => $this->student->teacher?->user?->name ?? 'غير محدد',
            'enrollment_date' => $this->student->enrollment_date?->format('Y/m/d') ?? '-',
            'current_level' => $this->getLevelLabel($this->student->current_level),
            'status' => $this->getStatusLabel($this->student->status),
            'memorized_juz' => $this->student->memorized_juz ?? 0,
        ];
    }

    private function loadPlanSummary(): void
    {
        $plan = $this->student->memorizationPlans->first();

        if (!$plan) {
            $this->planSummary = ['exists' => false];
            return;
        }

        $fromSurah = Surah::find($plan->from_surah_id);
        $toSurah = Surah::find($plan->to_surah_id);

        $this->planSummary = [
            'exists' => true,
            'title' => $plan->title,
            'type' => $this->getPlanTypeLabel($plan->plan_type),
            'date_range' => ($plan->start_date?->format('Y/m/d') ?? '-') . ' ← ' . ($plan->end_date?->format('Y/m/d') ?? '-'),
            'from_surah' => $fromSurah?->name_arabic ?? '-',
            'to_surah' => $toSurah?->name_arabic ?? '-',
            'from_ayah' => $plan->from_ayah,
            'to_ayah' => $plan->to_ayah,
            'from_page' => $plan->from_page ?? '-',
            'to_page' => $plan->to_page ?? '-',
            'total_ayahs' => $plan->total_ayahs ?? 0,
            'completed_ayahs' => $plan->completed_ayahs ?? 0,
            'remaining_ayahs' => ($plan->total_ayahs ?? 0) - ($plan->completed_ayahs ?? 0),
            'progress_percentage' => $plan->progress_percentage ?? 0,
            'status' => $this->getPlanStatusLabel($plan->status),
        ];
    }

    private function loadRecentRecords(): void
    {
        $records = MemorizationRecord::where('student_id', $this->student->id)
            ->with(['surah', 'toSurah'])
            ->orderByDesc('session_date')
            ->limit(15)
            ->get();

        $this->recentRecords = $records->map(function ($record) {
            $fromSurah = $record->surah?->name_arabic ?? '-';
            $toSurah = $record->toSurah?->name_arabic ?? $fromSurah;

            return [
                'date' => $record->session_date?->format('Y/m/d'),
                'type_label' => $this->getSessionTypeLabel($record->session_type),
                'type_color' => match ($record->session_type) {
                    'hifz' => 'success', 'revision' => 'info', 'test' => 'warning', default => 'gray',
                },
                'surah' => $record->surah_id == $record->to_surah_id ? $fromSurah : "{$fromSurah} → {$toSurah}",
                'ayah_range' => "{$record->from_ayah} - {$record->to_ayah}",
                'ayahs_count' => $record->ayahs_count ?? 0,
                'evaluation' => $this->getEvaluationLabel($record->evaluation),
                'evaluation_color' => $this->getEvaluationColor($record->evaluation),
                'mistakes_count' => $record->mistakes_count ?? 0,
                'notes' => $record->teacher_notes ?? '-',
            ];
        })->toArray();
    }

    private function loadMonthSummary(): void
    {
        $records = MemorizationRecord::where('student_id', $this->student->id)
            ->where('session_date', '>=', Carbon::now()->subDays(30))
            ->where('status', 'completed')
            ->get();

        $this->monthSummary = [
            'sessions_count' => $records->count(),
            'total_ayahs' => $records->sum('ayahs_count'),
            'hifz_ayahs' => $records->where('session_type', 'hifz')->sum('ayahs_count'),
            'revision_ayahs' => $records->where('session_type', 'revision')->sum('ayahs_count'),
            'hifz_sessions' => $records->where('session_type', 'hifz')->count(),
            'revision_sessions' => $records->where('session_type', 'revision')->count(),
            'test_sessions' => $records->where('session_type', 'test')->count(),
            'total_mistakes' => $records->sum('mistakes_count'),
            'avg_mistakes' => $records->count() > 0 ? round($records->avg('mistakes_count'), 1) : 0,
        ];
    }

    private function loadWeeklyStats(): void
    {
        $weekStart = now()->startOfWeek(Carbon::SATURDAY);

        $records = MemorizationRecord::where('student_id', $this->student->id)
            ->where('session_date', '>=', $weekStart)
            ->where('status', 'completed')
            ->get();

        $this->weeklyStats = [
            'total_sessions' => $records->count(),
            'hifz_ayahs' => $records->where('session_type', 'hifz')->sum('ayahs_count'),
            'revision_ayahs' => $records->where('session_type', 'revision')->sum('ayahs_count'),
        ];
    }

    // ═══════════════════════════════
    // Helper Methods
    // ═══════════════════════════════

    private function getLevelLabel(string $level): string
    {
        return match ($level) {
            'beginner' => 'مبتدئ', 'elementary' => 'أساسي',
            'intermediate' => 'متوسط', 'advanced' => 'متقدم',
            'memorizer' => 'حافظ', default => '-',
        };
    }

    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'active' => 'نشط', 'inactive' => 'غير نشط',
            'graduated' => 'متخرج', 'withdrawn' => 'منسحب', default => '-',
        };
    }

    private function getPlanTypeLabel(?string $type): string
    {
        return match ($type) {
            'weekly' => 'أسبوعية', 'monthly' => 'شهرية',
            'yearly' => 'سنوية', default => '-',
        };
    }

    private function getPlanStatusLabel(?string $status): string
    {
        return match ($status) {
            'pending' => 'قيد الانتظار', 'in_progress' => 'جاري التنفيذ',
            'completed' => 'مكتملة', 'cancelled' => 'ملغاة', default => '-',
        };
    }

    private function getSessionTypeLabel(?string $type): string
    {
        return match ($type) {
            'hifz' => 'حفظ جديد', 'new_memorization' => 'حفظ جديد',
            'revision' => 'مراجعة', 'test' => 'اختبار', default => '-',
        };
    }

    private function getEvaluationLabel(?string $evaluation): string
    {
        return match ($evaluation) {
            'excellent' => 'ممتاز', 'very_good' => 'جيد جداً',
            'good' => 'جيد', 'acceptable' => 'مقبول',
            'needs_review' => 'يحتاج مراجعة', default => '-',
        };
    }

    private function getEvaluationColor(?string $evaluation): string
    {
        return match ($evaluation) {
            'excellent' => 'success', 'very_good' => 'info',
            'good' => 'primary', 'acceptable' => 'warning',
            'needs_review' => 'danger', default => 'gray',
        };
    }
}