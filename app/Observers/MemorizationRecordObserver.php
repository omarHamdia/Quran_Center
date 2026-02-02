<?php

namespace App\Observers;

use App\Models\MemorizationRecord;
use App\Models\MemorizationPlan;
use App\Services\MemorizationPlanProgressService;

class MemorizationRecordObserver
{
    private MemorizationPlanProgressService $progressService;
    private static bool $recalculating = false;

    public function __construct(MemorizationPlanProgressService $progressService)
    {
        $this->progressService = $progressService;
    }

    public function created(MemorizationRecord $record): void
    {
        $this->recalculatePlanProgress($record);
    }

    public function updated(MemorizationRecord $record): void
    {
        $importantFields = [
            'memorization_plan_id', 'session_type', 'surah_id',
            'from_ayah', 'to_ayah', 'from_page', 'to_page', 'status',
        ];

        foreach ($importantFields as $field) {
            if ($record->isDirty($field)) {
                // إعادة حساب الخطة القديمة إذا تغيرت
                $oldPlanId = $record->getOriginal('memorization_plan_id');
                if ($oldPlanId && $oldPlanId !== $record->memorization_plan_id) {
                    $oldPlan = MemorizationPlan::find($oldPlanId);
                    if ($oldPlan) {
                        $this->progressService->recalculate($oldPlan);
                    }
                }

                $this->recalculatePlanProgress($record);
                break;
            }
        }
    }

    public function deleted(MemorizationRecord $record): void
    {
        $this->recalculatePlanProgress($record);
    }

    public function restored(MemorizationRecord $record): void
    {
        $this->recalculatePlanProgress($record);
    }

    private function recalculatePlanProgress(MemorizationRecord $record): void
    {
        if (self::$recalculating || !$record->memorization_plan_id) {
            return;
        }

        $plan = MemorizationPlan::find($record->memorization_plan_id);
        if (!$plan) return;

        try {
            self::$recalculating = true;
            $this->progressService->recalculate($plan);
        } finally {
            self::$recalculating = false;
        }
    }
}