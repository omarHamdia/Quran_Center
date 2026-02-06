<?php

namespace App\Console\Commands;

use App\Models\MemorizationPlan;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class DeleteExpiredPlans extends Command
{
    protected $signature = 'plans:delete-expired';
    protected $description = 'حذف الخطط التي انتهى وقتها';

    public function handle(): int
    {
        $today = Carbon::today();

        // الخطط المنتهية (end_date < اليوم)
        $expiredPlans = MemorizationPlan::where('end_date', '<', $today)
            ->whereIn('status', ['pending', 'in_progress'])
            ->get();

        $count = $expiredPlans->count();

        if ($count === 0) {
            $this->info('لا توجد خطط منتهية للحذف.');
            return self::SUCCESS;
        }

        foreach ($expiredPlans as $plan) {
            $this->line("حذف الخطة: {$plan->title} (الطالب ID: {$plan->student_id})");
            
            // فك ارتباط سجلات التسميع من هذه الخطة (لا تحذفها)
            $plan->memorizationRecords()->update(['memorization_plan_id' => null]);
            
            // حذف الخطة
            $plan->delete();
        }

        $this->info("✅ تم حذف {$count} خطة منتهية.");

        return self::SUCCESS;
    }
}