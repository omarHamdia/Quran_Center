<?php

namespace App\Services;

use App\Models\MemorizationPlan;
use App\Models\MemorizationRecord;
use App\Models\QuranAyah;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class MemorizationPlanProgressService
{
    /**
     * إعادة حساب تقدم خطة الحفظ
     */
    public function recalculate(MemorizationPlan $plan): void
    {
        DB::transaction(function () use ($plan) {
            $totalAyahs = $this->calculatePlanTotalAyahs($plan);
            $completedAyahs = $this->calculateCompletedUniqueAyahs($plan);
            $completedPages = $this->calculateCompletedPages($plan);
            $totalPages = $this->calculatePlanTotalPages($plan);

            $progressPercent = $totalAyahs > 0
                ? round(($completedAyahs / $totalAyahs) * 100, 2)
                : 0;

            $planStatus = $this->determinePlanStatus($completedAyahs, $totalAyahs);

            $plan->updateQuietly([
                'total_ayahs' => $totalAyahs,
                'completed_ayahs' => $completedAyahs,
                'total_pages' => $totalPages,
                'completed_pages' => $completedPages,
                'progress_percentage' => min($progressPercent, 100),
                'plan_status' => $planStatus,
                'status' => $this->mapPlanStatusToStatus($planStatus),
            ]);
        });
    }

    /**
     * حساب إجمالي آيات الخطة
     */
    public function calculatePlanTotalAyahs(MemorizationPlan $plan): int
    {
        if ($plan->from_surah_id === $plan->to_surah_id) {
            return QuranAyah::where('surah_id', $plan->from_surah_id)
                ->whereBetween('ayah_number', [$plan->from_ayah, $plan->to_ayah])
                ->count();
        }

        return QuranAyah::where(function ($q) use ($plan) {
            $q->where(function ($sub) use ($plan) {
                $sub->where('surah_id', $plan->from_surah_id)
                    ->where('ayah_number', '>=', $plan->from_ayah);
            })
            ->orWhere(function ($sub) use ($plan) {
                $sub->where('surah_id', '>', $plan->from_surah_id)
                    ->where('surah_id', '<', $plan->to_surah_id);
            })
            ->orWhere(function ($sub) use ($plan) {
                $sub->where('surah_id', $plan->to_surah_id)
                    ->where('ayah_number', '<=', $plan->to_ayah);
            });
        })->count();
    }

    /**
     * حساب إجمالي صفحات الخطة
     */
    public function calculatePlanTotalPages(MemorizationPlan $plan): int
    {
        if ($plan->from_page && $plan->to_page) {
            return $plan->to_page - $plan->from_page + 1;
        }

        $pages = $this->getPlanAyahs($plan)->pluck('page_number')->unique();
        return $pages->count();
    }

    /**
     * حساب الآيات المحفوظة الفريدة
     */
    private function calculateCompletedUniqueAyahs(MemorizationPlan $plan): int
    {
        $records = MemorizationRecord::where('student_id', $plan->student_id)
            ->where('session_type', 'hifz')
            ->where('status', 'completed')
            ->get();

        if ($records->isEmpty()) {
            return 0;
        }

        $planAyahs = $this->getPlanAyahsSet($plan);
        $memorizedAyahs = collect();

        foreach ($records as $record) {
            $recordAyahs = $this->getRecordAyahs($record);
            $intersection = $recordAyahs->intersect($planAyahs);
            $memorizedAyahs = $memorizedAyahs->merge($intersection);
        }

        return $memorizedAyahs->unique()->count();
    }

    /**
     * حساب الصفحات المكتملة
     */
    private function calculateCompletedPages(MemorizationPlan $plan): int
    {
        $records = MemorizationRecord::where('student_id', $plan->student_id)
            ->where('session_type', 'hifz')
            ->where('status', 'completed')
            ->get();

        if ($records->isEmpty()) {
            return 0;
        }

        $planAyahs = $this->getPlanAyahsSet($plan);
        $completedPages = collect();

        foreach ($records as $record) {
            $recordAyahs = $this->getRecordAyahsWithPages($record);
            foreach ($recordAyahs as $ayah) {
                if ($planAyahs->contains($ayah['key'])) {
                    $completedPages->push($ayah['page']);
                }
            }
        }

        return $completedPages->unique()->count();
    }

    /**
     * الحصول على آيات الخطة
     */
    private function getPlanAyahs(MemorizationPlan $plan): Collection
    {
        if ($plan->from_surah_id === $plan->to_surah_id) {
            return QuranAyah::where('surah_id', $plan->from_surah_id)
                ->whereBetween('ayah_number', [$plan->from_ayah, $plan->to_ayah])
                ->get();
        }

        return QuranAyah::where(function ($q) use ($plan) {
            $q->where(function ($sub) use ($plan) {
                $sub->where('surah_id', $plan->from_surah_id)
                    ->where('ayah_number', '>=', $plan->from_ayah);
            })
            ->orWhere(function ($sub) use ($plan) {
                $sub->where('surah_id', '>', $plan->from_surah_id)
                    ->where('surah_id', '<', $plan->to_surah_id);
            })
            ->orWhere(function ($sub) use ($plan) {
                $sub->where('surah_id', $plan->to_surah_id)
                    ->where('ayah_number', '<=', $plan->to_ayah);
            });
        })->get();
    }

    /**
     * الحصول على مجموعة آيات الخطة كـ keys
     */
    private function getPlanAyahsSet(MemorizationPlan $plan): Collection
    {
        return $this->getPlanAyahs($plan)->map(fn ($a) => "{$a->surah_id}:{$a->ayah_number}");
    }

    /**
     * الحصول على آيات سجل التسميع
     */
    private function getRecordAyahs(MemorizationRecord $record): Collection
    {
        $fromSurah = $record->surah_id;
        $toSurah = $record->to_surah_id ?? $record->surah_id;
        $fromAyah = $record->from_ayah;
        $toAyah = $record->to_ayah;

        if ($fromSurah === $toSurah) {
            return QuranAyah::where('surah_id', $fromSurah)
                ->whereBetween('ayah_number', [$fromAyah, $toAyah])
                ->get()
                ->map(fn ($a) => "{$a->surah_id}:{$a->ayah_number}");
        }

        return QuranAyah::where(function ($q) use ($fromSurah, $toSurah, $fromAyah, $toAyah) {
            $q->where(function ($sub) use ($fromSurah, $fromAyah) {
                $sub->where('surah_id', $fromSurah)
                    ->where('ayah_number', '>=', $fromAyah);
            })
            ->orWhere(function ($sub) use ($fromSurah, $toSurah) {
                $sub->where('surah_id', '>', $fromSurah)
                    ->where('surah_id', '<', $toSurah);
            })
            ->orWhere(function ($sub) use ($toSurah, $toAyah) {
                $sub->where('surah_id', $toSurah)
                    ->where('ayah_number', '<=', $toAyah);
            });
        })->get()->map(fn ($a) => "{$a->surah_id}:{$a->ayah_number}");
    }

    /**
     * الحصول على آيات مع الصفحات
     */
    private function getRecordAyahsWithPages(MemorizationRecord $record): Collection
    {
        $fromSurah = $record->surah_id;
        $toSurah = $record->to_surah_id ?? $record->surah_id;
        $fromAyah = $record->from_ayah;
        $toAyah = $record->to_ayah;

        if ($fromSurah === $toSurah) {
            return QuranAyah::where('surah_id', $fromSurah)
                ->whereBetween('ayah_number', [$fromAyah, $toAyah])
                ->get()
                ->map(fn ($a) => ['key' => "{$a->surah_id}:{$a->ayah_number}", 'page' => $a->page_number]);
        }

        return QuranAyah::where(function ($q) use ($fromSurah, $toSurah, $fromAyah, $toAyah) {
            $q->where(function ($sub) use ($fromSurah, $fromAyah) {
                $sub->where('surah_id', $fromSurah)
                    ->where('ayah_number', '>=', $fromAyah);
            })
            ->orWhere(function ($sub) use ($fromSurah, $toSurah) {
                $sub->where('surah_id', '>', $fromSurah)
                    ->where('surah_id', '<', $toSurah);
            })
            ->orWhere(function ($sub) use ($toSurah, $toAyah) {
                $sub->where('surah_id', $toSurah)
                    ->where('ayah_number', '<=', $toAyah);
            });
        })->get()->map(fn ($a) => ['key' => "{$a->surah_id}:{$a->ayah_number}", 'page' => $a->page_number]);
    }

    /**
     * حساب التقدم المتوقع (للعرض)
     */
    public function calculateExpectedProgress(
        MemorizationPlan $plan,
        int $surahId,
        int $fromAyah,
        int $toAyah,
        ?int $toSurahId = null
    ): array {
        $currentCompleted = $plan->completed_ayahs ?? 0;
        $totalAyahs = $plan->total_ayahs ?: $this->calculatePlanTotalAyahs($plan);

        $toSurahId = $toSurahId ?? $surahId;

        // الآيات الجديدة
        $newAyahs = $this->getAyahsBetween($surahId, $toSurahId, $fromAyah, $toAyah);

        // الآيات ضمن نطاق الخطة
        $planAyahs = $this->getPlanAyahsSet($plan);
        $newAyahsInPlan = $newAyahs->intersect($planAyahs);

        // الآيات المحفوظة سابقاً
        $existingAyahs = $this->getExistingMemorizedAyahs($plan);

        // الآيات الجديدة الفريدة
        $uniqueNewAyahs = $newAyahsInPlan->diff($existingAyahs)->count();

        $expectedCompleted = $currentCompleted + $uniqueNewAyahs;
        $expectedProgress = $totalAyahs > 0
            ? round(($expectedCompleted / $totalAyahs) * 100, 2)
            : 0;

        return [
            'current_completed' => $currentCompleted,
            'new_ayahs' => $newAyahs->count(),
            'new_ayahs_in_plan' => $newAyahsInPlan->count(),
            'unique_new_ayahs' => $uniqueNewAyahs,
            'expected_completed' => $expectedCompleted,
            'total_ayahs' => $totalAyahs,
            'expected_progress' => min($expectedProgress, 100),
        ];
    }

    /**
     * الحصول على الآيات بين سورتين
     */
    private function getAyahsBetween(int $fromSurah, int $toSurah, int $fromAyah, int $toAyah): Collection
    {
        if ($fromSurah === $toSurah) {
            return QuranAyah::where('surah_id', $fromSurah)
                ->whereBetween('ayah_number', [$fromAyah, $toAyah])
                ->get()
                ->map(fn ($a) => "{$a->surah_id}:{$a->ayah_number}");
        }

        return QuranAyah::where(function ($q) use ($fromSurah, $toSurah, $fromAyah, $toAyah) {
            $q->where(function ($sub) use ($fromSurah, $fromAyah) {
                $sub->where('surah_id', $fromSurah)
                    ->where('ayah_number', '>=', $fromAyah);
            })
            ->orWhere(function ($sub) use ($fromSurah, $toSurah) {
                $sub->where('surah_id', '>', $fromSurah)
                    ->where('surah_id', '<', $toSurah);
            })
            ->orWhere(function ($sub) use ($toSurah, $toAyah) {
                $sub->where('surah_id', $toSurah)
                    ->where('ayah_number', '<=', $toAyah);
            });
        })->get()->map(fn ($a) => "{$a->surah_id}:{$a->ayah_number}");
    }

    /**
     * الحصول على الآيات المحفوظة سابقاً
     */
    private function getExistingMemorizedAyahs(MemorizationPlan $plan): Collection
    {
        $records = MemorizationRecord::where('student_id', $plan->student_id)
            ->where('session_type', 'hifz')
            ->where('status', 'completed')
            ->get();

        $allAyahs = collect();
        foreach ($records as $record) {
            $allAyahs = $allAyahs->merge($this->getRecordAyahs($record));
        }

        return $allAyahs->unique();
    }

    private function determinePlanStatus(int $completed, int $total): string
    {
        if ($completed === 0) return 'not_started';
        if ($completed >= $total) return 'completed';
        return 'in_progress';
    }

    private function mapPlanStatusToStatus(string $planStatus): string
    {
        return match ($planStatus) {
            'not_started' => 'pending',
            'in_progress' => 'in_progress',
            'completed' => 'completed',
            default => 'pending',
        };
    }
}