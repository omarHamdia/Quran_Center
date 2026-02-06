<?php

namespace App\Observers;

use App\Models\MemorizationRecord;
use App\Models\MemorizationPlan;
use App\Services\MemorizationPlanProgressService;

class MemorizationRecordObserver
{
    private MemorizationPlanProgressService $progressService;
    private static bool $isProcessing = false;

    public function __construct(MemorizationPlanProgressService $progressService)
    {
        $this->progressService = $progressService;
    }

    /**
     * بعد إنشاء سجل جديد
     */
    public function created(MemorizationRecord $record): void
    {
        $this->updateAllStudentPlans($record);
    }

    /**
     * بعد تحديث سجل
     */
    public function updated(MemorizationRecord $record): void
    {
        $this->updateAllStudentPlans($record);
    }

    /**
     * بعد حذف سجل
     */
    public function deleted(MemorizationRecord $record): void
    {
        $this->updateAllStudentPlans($record);
    }

    /**
     * بعد استعادة سجل
     */
    public function restored(MemorizationRecord $record): void
    {
        $this->updateAllStudentPlans($record);
    }

    /**
     * تحديث جميع خطط الطالب النشطة
     */
    private function updateAllStudentPlans(MemorizationRecord $record): void
    {
        // منع التكرار
        if (self::$isProcessing) {
            return;
        }

        // فقط جلسات الحفظ الجديد المكتملة تؤثر على التقدم
        if ($record->session_type !== 'hifz' || $record->status !== 'completed') {
            return;
        }

        try {
            self::$isProcessing = true;

            // جلب جميع خطط الطالب النشطة
            $plans = MemorizationPlan::where('student_id', $record->student_id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->get();

            foreach ($plans as $plan) {
                // التحقق من أن التسميع ضمن نطاق الخطة
                if ($this->isRecordWithinPlan($record, $plan)) {
                    $this->progressService->recalculate($plan);
                }
            }

        } finally {
            self::$isProcessing = false;
        }
    }

    /**
     * التحقق من أن التسميع ضمن نطاق الخطة
     */
    private function isRecordWithinPlan(MemorizationRecord $record, MemorizationPlan $plan): bool
    {
        $fromSurah = $record->surah_id;
        $toSurah = $record->to_surah_id ?? $record->surah_id;
        $fromAyah = $record->from_ayah;
        $toAyah = $record->to_ayah;

        // التحقق البسيط: هل هناك تقاطع بين نطاق التسميع ونطاق الخطة؟
        $planFromSurah = $plan->from_surah_id;
        $planToSurah = $plan->to_surah_id;

        // إذا كانت السورة خارج نطاق الخطة تماماً
        if ($toSurah < $planFromSurah || $fromSurah > $planToSurah) {
            return false;
        }

        return true;
    }
}