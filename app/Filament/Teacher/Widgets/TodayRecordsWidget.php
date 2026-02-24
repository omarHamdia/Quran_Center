<?php

namespace App\Filament\Teacher\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

use App\Models\Teacher;
use App\Models\MemorizationRecord;

class TodayRecordsWidget extends Widget
{
    protected static string $view = 'filament.teacher.widgets.today-records-widget';
    protected int|string|array $columnSpan = 'full';

    // ✅ نجبرها تصير بعرض الصفحة (خصوصاً إذا الـ dashboard عمودين)
    // protected int|string|array $columnSpan = [
    //     'default' => 1,
    //     'md' => 2,
    //     'lg' => 2,
    //     'xl' => 2,
    // ];

    public function getTodayData(): Collection
    {
        $teacherId = $this->resolveTeacherId();
        if (! $teacherId) {
            return collect();
        }

        $today = Carbon::today();

        $records = MemorizationRecord::query()
            ->with([
                'student.user:id,name,phone',
                'surah', // ✅ بدون تحديد أعمدة لتجنب مشكلة name
            ])
            ->where('teacher_id', $teacherId)
            ->whereDate('session_date', $today)
            ->latest('created_at')
            ->get();

        return $records->map(function ($r) {
            $from = (int) ($r->from_ayah ?? 0);
            $to   = (int) ($r->to_ayah ?? 0);

            $ayahsCount = 0;
            if ($from > 0 && $to > 0 && $to >= $from) {
                $ayahsCount = ($to - $from) + 1;
            }

            // ✅ اسم السورة (fallback)
            $surahName = '-';
            if ($r->surah) {
                foreach (['name', 'title', 'surah_name', 'name_ar', 'arabic_name'] as $col) {
                    if (! empty($r->surah->{$col})) {
                        $surahName = $r->surah->{$col};
                        break;
                    }
                }
            }

            return [
                // ✅ IDs نحتاجهم للأزرار
                'record_id'     => (int) ($r->id ?? 0),
                'student_id'    => (int) ($r->student_id ?? 0),

                'student_name'  => $r->student?->user?->name ?? '-',
                'student_phone' => $r->student?->user?->phone ?? '-',

                'surah_name'    => $surahName,
                'from_ayah'     => $r->from_ayah,
                'to_ayah'       => $r->to_ayah,
                'ayahs_count'   => $ayahsCount,

                'pages_count'   => (int) ($r->pages_count ?? 0),
                'session_type'  => $r->session_type,     // hifz | revision | test
                'evaluation'    => $r->evaluation,       // excellent | very_good | good | acceptable | needs_review
                'mistakes'      => (int) ($r->mistakes_count ?? 0),
            ];
        });
    }

    public function getTodaySummary(): array
    {
        $data = $this->getTodayData();

        return [
            'total_sessions' => (int) $data->count(),
            'hifz_ayahs'     => (int) $data->where('session_type', 'hifz')->sum('ayahs_count'),
            'rev_ayahs'      => (int) $data->where('session_type', 'revision')->sum('ayahs_count'),
            'students_count' => (int) $data->pluck('student_id')->filter()->unique()->count(),
        ];
    }

    private function resolveTeacherId(): ?int
    {
        $userId = auth()->id();
        if (! $userId) return null;

        return Teacher::query()
            ->where('user_id', $userId)
            ->value('id');
    }
}