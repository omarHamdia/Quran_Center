<?php

namespace App\Filament\Teacher\Widgets;

use App\Services\TeacherDashboardService;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class OverdueStudentsWidget extends Widget
{
    protected static ?string $heading = '⚠️ طلاب متأخرون عن الخطة';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected static ?string $pollingInterval = '60s';

    protected static string $view = 'filament.teacher.widgets.overdue-students-widget';

    public function getOverdueData(): Collection
    {
        $teacherId = TeacherDashboardService::getTeacherId();

        if (!$teacherId) {
            return collect();
        }

        $service = new TeacherDashboardService($teacherId);
        return $service->getOverdueStudents();
    }
}
