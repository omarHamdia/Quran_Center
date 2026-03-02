<?php

namespace App\Filament\Teacher\Widgets;

use App\Models\MemorizationPlan;
use App\Models\Teacher;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class PlanProgressWidget extends Widget
{
    protected static ?string $heading = '📊 تقدم الطلاب في الخطط';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $pollingInterval = '60s';

    protected static string $view = 'filament.teacher.widgets.plan-progress-widget';

    public function getPlanData(): Collection
    {
        $teacherId = Teacher::where('user_id', auth()->id())->value('id');

        if (!$teacherId) {
            return collect();
        }

        return MemorizationPlan::where('teacher_id', $teacherId)
            ->whereIn('status', ['pending', 'in_progress', 'completed'])
            ->with(['student.user'])
            ->orderByDesc('progress_percentage')
            ->get()
            ->map(function ($plan) {
                $statusLabel = match ($plan->status) {
                    'completed' => 'مكتملة',
                    'in_progress' => 'قيد التنفيذ',
                    'pending' => 'قيد الانتظار',
                    'cancelled' => 'ملغاة',
                    default => $plan->status,
                };

                $statusColor = match ($plan->status) {
                    'completed' => 'success',
                    'in_progress' => 'info',
                    'pending' => 'warning',
                    'cancelled' => 'danger',
                    default => 'gray',
                };

                $typeLabel = match ($plan->plan_type) {
                    'weekly' => 'أسبوعية',
                    'monthly' => 'شهرية',
                    'yearly' => 'سنوية',
                    default => $plan->plan_type,
                };

                return [
                    'student_name' => $plan->student?->user?->name ?? 'غير معروف',
                    'student_id' => $plan->student_id,
                    'title' => $plan->title ?? 'بدون عنوان',
                    'plan_type' => $typeLabel,
                    'status' => $plan->status,
                    'status_label' => $statusLabel,
                    'status_color' => $statusColor,
                    'progress' => (int) ($plan->progress_percentage ?? 0),
                    'completed_ayahs' => (int) ($plan->completed_ayahs ?? 0),
                    'total_ayahs' => (int) ($plan->total_ayahs ?? 0),
                    'remaining_ayahs' => max(0, (int) ($plan->total_ayahs ?? 0) - (int) ($plan->completed_ayahs ?? 0)),
                ];
            });
    }
}