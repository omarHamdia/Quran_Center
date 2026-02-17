<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use App\Models\MemorizationPlan;
use Filament\Resources\Pages\ViewRecord;

class ViewReport extends ViewRecord
{
    protected static string $resource = ReportResource::class;
    protected static string $view = 'filament.resources.report-resource.pages.view-report';

    public function getTitle(): string
    {
        return "تقرير حلقة: {$this->record->user->name}";
    }

    public function getStudents()
    {
        return $this->record->students()
            ->with(['user', 'memorizationPlans', 'memorizationRecords'])
            ->get()
            ->map(function ($student) {
                $plans = $student->memorizationPlans;
                $records = $student->memorizationRecords;
                
                $avgProgress = $plans->count() > 0 ? ($plans->avg('progress_percentage') ?? 0) : 0;
                $totalMistakes = $records->sum('mistakes_count') ?? 0;
                $avgMistakes = $records->count() > 0 ? round($records->avg('mistakes_count'), 1) : 0;
                
                $excellentCount = $records->whereIn('evaluation', ['excellent', 'very_good'])->count();
                $excellentRate = $records->count() > 0 ? round(($excellentCount / $records->count()) * 100, 1) : 0;

                $weekStart = now()->startOfWeek();
                $weekRecords = $records->where('session_date', '>=', $weekStart);
                $completedSessions = $weekRecords->where('status', 'completed')->count();
                $attendanceRate = $weekRecords->count() > 0 ? round(($completedSessions / $weekRecords->count()) * 100, 1) : 0;

                // حساب الصفحات الكلية المسمّعة
                $totalPages = $records->where('session_type', 'hifz')->sum(function ($r) {
                    return ($r->from_page && $r->to_page) ? ($r->to_page - $r->from_page + 1) : 0;
                });

                // حساب الأجزاء: كل 20 صفحة = 1 جزء
                $memorizedJuz = floor($totalPages / 20);

                $student->avg_progress = round($avgProgress, 1);
                $student->avg_mistakes = $avgMistakes;
                $student->total_mistakes = $totalMistakes;
                $student->excellent_rate = $excellentRate;
                $student->attendance_rate = min($attendanceRate, 100);
                $student->total_pages = $totalPages;
                $student->memorized_juz = $memorizedJuz;
                $student->sessions_count = $records->count();
                
                return $student;
            })
            // ✅ الترتيب: الصفحات أولاً (تنازلي)، ثم الأخطاء (تصاعدي)
            ->sortBy([
                ['total_pages', 'desc'],
                ['total_mistakes', 'asc'],
            ])
            ->values();
    }

    public function getTodayRecords()
    {
        $today = now()->toDateString();
        
        return $this->record->memorizationRecords()
            ->with(['student.user'])
            ->whereDate('session_date', $today)
            ->get()
            ->map(function ($record) {
                $pages = ($record->from_page && $record->to_page) ? ($record->to_page - $record->from_page + 1) : 0;
                $record->pages_count = $pages;
                return $record;
            })
            // ✅ ترتيب تسميع اليوم: الصفحات أولاً، ثم الأخطاء الأقل
            ->sortBy([
                ['pages_count', 'desc'],
                ['mistakes_count', 'asc'],
            ])
            ->values();
    }

    public function getWeeklyStats(): array
    {
        $weekStart = now()->startOfWeek();
        $lastWeekStart = now()->subWeek()->startOfWeek();
        $lastWeekEnd = now()->subWeek()->endOfWeek();
        
        $records = $this->record->memorizationRecords()->where('session_date', '>=', $weekStart)->get();
        $lastWeekRecords = $this->record->memorizationRecords()->whereBetween('session_date', [$lastWeekStart, $lastWeekEnd])->get();

        $totalStudents = $this->record->students()->count();
        $studentsWithRecords = $records->pluck('student_id')->unique()->count();

        $currentPages = $records->where('session_type', 'hifz')->sum(function ($r) {
            return ($r->from_page && $r->to_page) ? ($r->to_page - $r->from_page + 1) : 0;
        });

        $lastWeekPages = $lastWeekRecords->where('session_type', 'hifz')->sum(function ($r) {
            return ($r->from_page && $r->to_page) ? ($r->to_page - $r->from_page + 1) : 0;
        });

        $pagesChange = $lastWeekPages > 0 ? round((($currentPages - $lastWeekPages) / $lastWeekPages) * 100) : ($currentPages > 0 ? 100 : 0);
        $sessionsChange = $lastWeekRecords->count() > 0 ? round((($records->count() - $lastWeekRecords->count()) / $lastWeekRecords->count()) * 100) : ($records->count() > 0 ? 100 : 0);

        return [
            'total_sessions' => $records->count(),
            'sessions_change' => $sessionsChange,
            'total_pages' => $currentPages,
            'pages_change' => $pagesChange,
            'students_with_records' => $studentsWithRecords,
            'total_students' => $totalStudents,
            'attendance_rate' => $totalStudents > 0 ? round(($studentsWithRecords / $totalStudents) * 100, 1) : 0,
            'avg_mistakes' => $records->count() > 0 ? round($records->avg('mistakes_count'), 1) : 0,
            'excellent_sessions' => $records->whereIn('evaluation', ['excellent', 'very_good'])->count(),
        ];
    }

    public function getDailyPagesData(): array
    {
        $data = [];
        $labels = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $labels[] = $day->translatedFormat('D');
            
            $pages = $this->record->memorizationRecords()
                ->whereDate('session_date', $day)
                ->where('session_type', 'hifz')
                ->get()
                ->sum(fn($r) => ($r->from_page && $r->to_page) ? ($r->to_page - $r->from_page + 1) : 0);
            
            $data[] = $pages;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    public function getMonthlyData(): array
    {
        $pagesData = [];
        $sessionsData = [];
        $labels = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[] = $month->translatedFormat('M');
            
            $records = $this->record->memorizationRecords()
                ->whereYear('session_date', $month->year)
                ->whereMonth('session_date', $month->month)
                ->get();
            
            $sessionsData[] = $records->count();
            $pagesData[] = $records->where('session_type', 'hifz')->sum(fn($r) => ($r->from_page && $r->to_page) ? ($r->to_page - $r->from_page + 1) : 0);
        }

        return ['labels' => $labels, 'pages' => $pagesData, 'sessions' => $sessionsData];
    }

    public function getEvaluationData(): array
    {
        $records = $this->record->memorizationRecords()->where('session_date', '>=', now()->subDays(30))->get();

        return [
            'excellent' => $records->where('evaluation', 'excellent')->count(),
            'very_good' => $records->where('evaluation', 'very_good')->count(),
            'good' => $records->where('evaluation', 'good')->count(),
            'acceptable' => $records->where('evaluation', 'acceptable')->count(),
            'needs_review' => $records->where('evaluation', 'needs_review')->count(),
        ];
    }

    public function getWeeklyCompletions(): array
    {
        $plans = MemorizationPlan::whereHas('student', fn($q) => $q->where('teacher_id', $this->record->id))
            ->whereIn('status', ['pending', 'in_progress', 'completed'])
            ->get();

        $completed = $plans->where('progress_percentage', '>=', 100)->count();
        $inProgress = $plans->whereBetween('progress_percentage', [1, 99])->count();
        $notStarted = $plans->where('progress_percentage', '<=', 0)->count();

        return [
            'total' => $plans->count(),
            'completed' => $completed,
            'in_progress' => $inProgress,
            'not_started' => $notStarted,
            'completion_rate' => $plans->count() > 0 ? round(($completed / $plans->count()) * 100, 1) : 0,
        ];
    }

    public function getTopStudents()
    {
        return $this->getStudents()->take(5);
    }

    public function getSurahName($surahId): string
    {
        $names = [
            1 => 'الفاتحة', 2 => 'البقرة', 3 => 'آل عمران', 4 => 'النساء', 5 => 'المائدة',
            6 => 'الأنعام', 7 => 'الأعراف', 8 => 'الأنفال', 9 => 'التوبة', 10 => 'يونس',
            11 => 'هود', 12 => 'يوسف', 13 => 'الرعد', 14 => 'إبراهيم', 15 => 'الحجر',
            16 => 'النحل', 17 => 'الإسراء', 18 => 'الكهف', 19 => 'مريم', 20 => 'طه',
            21 => 'الأنبياء', 22 => 'الحج', 23 => 'المؤمنون', 24 => 'النور', 25 => 'الفرقان',
            26 => 'الشعراء', 27 => 'النمل', 28 => 'القصص', 29 => 'العنكبوت', 30 => 'الروم',
            31 => 'لقمان', 32 => 'السجدة', 33 => 'الأحزاب', 34 => 'سبأ', 35 => 'فاطر',
            36 => 'يس', 37 => 'الصافات', 38 => 'ص', 39 => 'الزمر', 40 => 'غافر',
            41 => 'فصلت', 42 => 'الشورى', 43 => 'الزخرف', 44 => 'الدخان', 45 => 'الجاثية',
            46 => 'الأحقاف', 47 => 'محمد', 48 => 'الفتح', 49 => 'الحجرات', 50 => 'ق',
            51 => 'الذاريات', 52 => 'الطور', 53 => 'النجم', 54 => 'القمر', 55 => 'الرحمن',
            56 => 'الواقعة', 57 => 'الحديد', 58 => 'المجادلة', 59 => 'الحشر', 60 => 'الممتحنة',
            61 => 'الصف', 62 => 'الجمعة', 63 => 'المنافقون', 64 => 'التغابن', 65 => 'الطلاق',
            66 => 'التحريم', 67 => 'الملك', 68 => 'القلم', 69 => 'الحاقة', 70 => 'المعارج',
            71 => 'نوح', 72 => 'الجن', 73 => 'المزمل', 74 => 'المدثر', 75 => 'القيامة',
            76 => 'الإنسان', 77 => 'المرسلات', 78 => 'النبأ', 79 => 'النازعات', 80 => 'عبس',
            81 => 'التكوير', 82 => 'الانفطار', 83 => 'المطففين', 84 => 'الانشقاق', 85 => 'البروج',
            86 => 'الطارق', 87 => 'الأعلى', 88 => 'الغاشية', 89 => 'الفجر', 90 => 'البلد',
            91 => 'الشمس', 92 => 'الليل', 93 => 'الضحى', 94 => 'الشرح', 95 => 'التين',
            96 => 'العلق', 97 => 'القدر', 98 => 'البينة', 99 => 'الزلزلة', 100 => 'العاديات',
            101 => 'القارعة', 102 => 'التكاثر', 103 => 'العصر', 104 => 'الهمزة', 105 => 'الفيل',
            106 => 'قريش', 107 => 'الماعون', 108 => 'الكوثر', 109 => 'الكافرون', 110 => 'النصر',
            111 => 'المسد', 112 => 'الإخلاص', 113 => 'الفلق', 114 => 'الناس',
        ];
        return $names[$surahId] ?? "سورة {$surahId}";
    }
}