<?php

namespace App\Filament\Pages;

use App\Models\MemorizationPlan;
use App\Models\MemorizationRecord;
use App\Models\Student;
use App\Models\Surah;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

class StudentReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $title = 'تقرير الطالب';
    protected static ?string $navigationLabel = 'تقرير الطالب';
    protected static bool $shouldRegisterNavigation = false; // لا يظهر في القائمة

    protected static string $view = 'filament.pages.student-report';

    public ?Student $student = null;
    public array $studentInfo = [];
    public array $planSummary = [];
    public array $recentRecords = [];
    public array $monthSummary = [];

    public function mount(): void
    {
        $studentId = request()->query('student');

        if (!$studentId) {
            abort(404, 'لم يتم تحديد الطالب');
        }

        $this->student = Student::with([
            'user',
            'teacher.user',
            'memorizationPlans' => fn ($q) => $q->whereIn('status', ['pending', 'in_progress'])->latest(),
            'memorizationRecords' => fn ($q) => $q->with(['surah', 'toSurah'])->latest('session_date')->limit(10),
        ])->find($studentId);

        if (!$this->student) {
            abort(404, 'الطالب غير موجود');
        }

        $this->loadStudentInfo();
        $this->loadPlanSummary();
        $this->loadRecentRecords();
        $this->loadMonthSummary();
    }

    public function getTitle(): string
    {
        return "تقرير الطالب: {$this->student->user->name}";
    }

    private function loadStudentInfo(): void
    {
        $this->studentInfo = [
            'name' => $this->student->user->name,
            'phone' => $this->student->user->phone,
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
            'date_range' => $plan->start_date?->format('Y/m/d') . ' - ' . $plan->end_date?->format('Y/m/d'),
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
        $this->recentRecords = $this->student->memorizationRecords->map(function ($record) {
            $fromSurah = $record->surah?->name_arabic ?? '-';
            $toSurah = $record->toSurah?->name_arabic ?? $fromSurah;

            return [
                'date' => $record->session_date?->format('Y/m/d'),
                'session_type' => $this->getSessionTypeLabel($record->session_type),
                'surah' => $record->surah_id == $record->to_surah_id
                    ? $fromSurah
                    : "{$fromSurah} → {$toSurah}",
                'ayah_range' => "{$record->from_ayah} - {$record->to_ayah}",
                'page_range' => $record->from_page && $record->to_page
                    ? "{$record->from_page} - {$record->to_page}"
                    : '-',
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
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        $records = MemorizationRecord::where('student_id', $this->student->id)
            ->where('session_date', '>=', $thirtyDaysAgo)
            ->where('status', 'completed')
            ->get();

        $sessionDates = $records->pluck('session_date')->map(fn ($d) => $d->format('Y-m-d'))->unique();
        $allDays = collect();
        for ($i = 0; $i < 30; $i++) {
            $allDays->push(Carbon::now()->subDays($i)->format('Y-m-d'));
        }
        $gapDays = $allDays->diff($sessionDates)->count();

        // حساب متوسط التقييم
        $evaluationScores = [
            'excellent' => 5,
            'very_good' => 4,
            'good' => 3,
            'acceptable' => 2,
            'needs_review' => 1,
        ];

        $avgScore = $records->avg(fn ($r) => $evaluationScores[$r->evaluation] ?? 0);
        $avgEvaluation = match (true) {
            $avgScore >= 4.5 => 'ممتاز',
            $avgScore >= 3.5 => 'جيد جداً',
            $avgScore >= 2.5 => 'جيد',
            $avgScore >= 1.5 => 'مقبول',
            $avgScore > 0 => 'يحتاج مراجعة',
            default => '-',
        };

        $this->monthSummary = [
            'sessions_count' => $records->count(),
            'total_ayahs' => $records->sum('ayahs_count'),
            'hifz_sessions' => $records->where('session_type', 'hifz')->count(),
            'revision_sessions' => $records->where('session_type', 'revision')->count(),
            'test_sessions' => $records->where('session_type', 'test')->count(),
            'gap_days' => $gapDays,
            'average_evaluation' => $avgEvaluation,
            'total_mistakes' => $records->sum('mistakes_count'),
        ];
    }

    // Helper methods
    private function getLevelLabel(string $level): string
    {
        return match ($level) {
            'beginner' => 'مبتدئ',
            'elementary' => 'أساسي',
            'intermediate' => 'متوسط',
            'advanced' => 'متقدم',
            'memorizer' => 'حافظ',
            default => '-',
        };
    }

    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'active' => 'نشط',
            'inactive' => 'غير نشط',
            'graduated' => 'متخرج',
            'withdrawn' => 'منسحب',
            default => '-',
        };
    }

    private function getPlanTypeLabel(?string $type): string
    {
        return match ($type) {
            'weekly' => 'أسبوعية',
            'monthly' => 'شهرية',
            'yearly' => 'سنوية',
            default => '-',
        };
    }

    private function getPlanStatusLabel(?string $status): string
    {
        return match ($status) {
            'pending' => 'قيد الانتظار',
            'in_progress' => 'جاري التنفيذ',
            'completed' => 'مكتملة',
            'cancelled' => 'ملغاة',
            default => '-',
        };
    }

    private function getSessionTypeLabel(?string $type): string
    {
        return match ($type) {
            'hifz' => 'حفظ جديد',
            'new_memorization' => 'حفظ جديد',
            'revision' => 'مراجعة',
            'test' => 'اختبار',
            default => '-',
        };
    }

    private function getEvaluationLabel(?string $evaluation): string
    {
        return match ($evaluation) {
            'excellent' => 'ممتاز',
            'very_good' => 'جيد جداً',
            'good' => 'جيد',
            'acceptable' => 'مقبول',
            'needs_review' => 'يحتاج مراجعة',
            default => '-',
        };
    }

    private function getEvaluationColor(?string $evaluation): string
    {
        return match ($evaluation) {
            'excellent' => 'success',
            'very_good' => 'info',
            'good' => 'primary',
            'acceptable' => 'warning',
            'needs_review' => 'danger',
            default => 'gray',
        };
    }
}