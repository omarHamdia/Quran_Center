<?php

namespace App\Console\Commands;

use App\Models\MemorizationPlan;
use App\Services\MemorizationPlanProgressService;
use Illuminate\Console\Command;

class RecalculateAllPlansProgress extends Command
{
    protected $signature = 'plans:recalculate-progress {--student= : معرف الطالب}';
    protected $description = 'إعادة حساب تقدم جميع الخطط';

    public function handle(MemorizationPlanProgressService $service): int
    {
        $studentId = $this->option('student');

        $query = MemorizationPlan::whereIn('status', ['pending', 'in_progress']);

        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        $plans = $query->get();

        $this->info("جاري إعادة حساب تقدم {$plans->count()} خطة...");

        $bar = $this->output->createProgressBar($plans->count());

        foreach ($plans as $plan) {
            $service->recalculate($plan);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✅ تم إعادة حساب التقدم بنجاح!');

        return Command::SUCCESS;
    }
}