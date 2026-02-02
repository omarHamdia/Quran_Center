<?php

namespace App\Services;

use App\Models\MemorizationPlan;
use App\Models\MemorizationRecord;
use App\Models\QuranAyah;
use App\Models\Surah;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MemorizationPlanProgressService
{
    /**
     * إعادة حساب تقدم خطة الحفظ
     */
    public function recalculate(MemorizationPlan $plan): void
    {
        DB::transaction(function () use ($plan) {
            // 1. حساب إجمالي آيات الخطة
            $totalAyahs = $this->calculatePlanTotalAyahs($plan);

            // 2. جمع الآيات المحفوظة (الفريدة فقط)
            $completedAyahs = $this->calculateCompletedUniqueAyahs($plan);

            // 3. حساب النسبة المئوية
            $progressPercent = $totalAyahs > 0
                ? round(($completedAyahs / $totalAyahs) * 100, 2)
                : 0;

            // 4. تحديد حالة الخطة
            $planStatus = $this->determinePlanStatus($completedAyahs, $totalAyahs, $plan);

            // 5. تحديث الخطة (بدون تفعيل Observer)
            $plan->updateQuietly([
                'total_ayahs' => $totalAyahs,
                'completed_ayahs' => $completedAyahs,
                'progress_percentage' => min($progressPercent, 100),
                'plan_status' => $planStatus,
                'status' => $this->mapPlanStatusToStatus($planStatus),
            ]);
        });
    }

    /**
     * حساب إجمالي آيات الخطة من quran_ayahs
     */
    public function calculatePlanTotalAyahs(MemorizationPlan $plan): int
    {
        $query = QuranAyah::query();

        // إذا كانت نفس السورة
        if ($plan->from_surah_id === $plan->to_surah_id) {
            return $query->where('surah_id', $plan->from_surah_id)
                ->whereBetween('ayah_number', [$plan->from_ayah, $plan->to_ayah])
                ->count();
        }

        // سور متعددة
        return $query->where(function ($q) use ($plan) {
            // السورة الأولى: من الآية المحددة إلى نهاية السورة
            $q->where(function ($sub) use ($plan) {
                $sub->where('surah_id', $plan->from_surah_id)
                    ->where('ayah_number', '>=', $plan->from_ayah);
            })
            // السور الوسطى: كاملة
            ->orWhere(function ($sub) use ($plan) {
                $sub->where('surah_id', '>', $plan->from_surah_id)
                    ->where('surah_id', '<', $plan->to_surah_id);
            })
            // السورة الأخيرة: من البداية إلى الآية المحددة
            ->orWhere(function ($sub) use ($plan) {
                $sub->where('surah_id', $plan->to_surah_id)
                    ->where('ayah_number', '<=', $plan->to_ayah);
            });
        })->count();
    }

    /**
     * حساب الآيات المحفوظة الفريدة (بدون تكرار)
     */
    private function calculateCompletedUniqueAyahs(MemorizationPlan $plan): int
    {
        // جلب جميع سجلات الحفظ الجديد فقط
        $records = MemorizationRecord::where('memorization_plan_id', $plan->id)
            ->where('session_type', 'hifz')
            ->where('status', 'completed')
            ->get(['surah_id', 'from_ayah', 'to_ayah', 'from_page', 'to_page']);

        if ($records->isEmpty()) {
            return 0;
        }

        // جمع كل الآيات المحفوظة (كـ set لمنع التكرار)
        $memorizedAyahs = collect();

        foreach ($records as $record) {
            $ayahs = $this->getAyahsFromRecord($record);
            $memorizedAyahs = $memorizedAyahs->merge($ayahs);
        }

        return $memorizedAyahs->unique()->count();
    }

    /**
     * استخراج الآيات من سجل الحفظ باستخدام quran_ayahs
     */
    private function getAyahsFromRecord(MemorizationRecord $record): Collection
    {
        return QuranAyah::where('surah_id', $record->surah_id)
            ->whereBetween('ayah_number', [$record->from_ayah, $record->to_ayah])
            ->get()
            ->map(fn ($ayah) => "{$ayah->surah_id}:{$ayah->ayah_number}");
    }

    /**
     * تحديد حالة الخطة
     */
    private function determinePlanStatus(int $completed, int $total, MemorizationPlan $plan): string
    {
        if ($plan->status === 'cancelled' || $plan->plan_status === 'cancelled') {
            return 'cancelled';
        }

        if ($completed === 0) {
            return 'not_started';
        }

        if ($completed >= $total) {
            return 'completed';
        }

        return 'in_progress';
    }

    /**
     * تحويل حالة الخطة إلى حالة الـ status
     */
    private function mapPlanStatusToStatus(string $planStatus): string
    {
        return match ($planStatus) {
            'not_started' => 'pending',
            'in_progress' => 'in_progress',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
            default => 'pending',
        };
    }

    /**
     * حساب التقدم المتوقع بعد إضافة سجل جديد
     */
    public function calculateExpectedProgress(
        MemorizationPlan $plan,
        int $surahId,
        int $fromAyah,
        int $toAyah
    ): array {
        $currentCompleted = $plan->completed_ayahs ?? 0;
        $totalAyahs = $plan->total_ayahs ?: $this->calculatePlanTotalAyahs($plan);

        // الآيات الجديدة من quran_ayahs
        $newAyahs = QuranAyah::where('surah_id', $surahId)
            ->whereBetween('ayah_number', [$fromAyah, $toAyah])
            ->get()
            ->map(fn ($a) => "{$a->surah_id}:{$a->ayah_number}");

        // الآيات المحفوظة سابقاً
        $existingAyahs = $this->getExistingMemorizedAyahs($plan);

        // الآيات الجديدة الفريدة
        $uniqueNewAyahs = $newAyahs->diff($existingAyahs)->count();

        $expectedCompleted = $currentCompleted + $uniqueNewAyahs;
        $expectedProgress = $totalAyahs > 0
            ? round(($expectedCompleted / $totalAyahs) * 100, 2)
            : 0;

        return [
            'current_completed' => $currentCompleted,
            'new_ayahs' => $newAyahs->count(),
            'unique_new_ayahs' => $uniqueNewAyahs,
            'expected_completed' => $expectedCompleted,
            'total_ayahs' => $totalAyahs,
            'expected_progress' => min($expectedProgress, 100),
        ];
    }

    /**
     * جل�� الآيات المحفوظة سابقاً
     */
    private function getExistingMemorizedAyahs(MemorizationPlan $plan): Collection
    {
        $records = MemorizationRecord::where('memorization_plan_id', $plan->id)
            ->where('session_type', 'hifz')
            ->where('status', 'completed')
            ->get();

        $allAyahs = collect();

        foreach ($records as $record) {
            $allAyahs = $allAyahs->merge($this->getAyahsFromRecord($record));
        }

        return $allAyahs->unique();
    }

    /**
     * الحصول على عدد آيات سورة معينة
     */
    public static function getSurahAyahCount(int $surahId): int
    {
        return Cache::remember("surah_{$surahId}_ayah_count", 3600, function () use ($surahId) {
            // أولاً: من جدول surahs
            $surah = Surah::find($surahId);
            if ($surah) {
                $count = $surah->ayah_count ?? $surah->verses_count ?? $surah->ayahs ?? null;
                if ($count) return (int) $count;
            }

            // ثانياً: من جدول quran_ayahs
            return QuranAyah::where('surah_id', $surahId)->count();
        });
    }

    /**
     * الحصول على نطاق صفحا�� سورة معينة
     */
    public static function getSurahPageRange(int $surahId): array
    {
        return Cache::remember("surah_{$surahId}_pages", 3600, function () use ($surahId) {
            $result = QuranAyah::where('surah_id', $surahId)
                ->selectRaw('MIN(page_number) as min_page, MAX(page_number) as max_page')
                ->first();

            return [
                'min' => $result->min_page ?? 1,
                'max' => $result->max_page ?? 604,
            ];
        });
    }

    /**
     * الحصول على صفحة آية معينة
     */
    public static function getAyahPage(int $surahId, int $ayahNumber): ?int
    {
        return QuranAyah::where('surah_id', $surahId)
            ->where('ayah_number', $ayahNumber)
            ->value('page_number');
    }
}