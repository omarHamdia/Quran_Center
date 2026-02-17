<?php

namespace App\Filament\Teacher\Widgets;

use App\Services\TeacherDashboardService;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class InactiveStudentsWidget extends Widget
{
    protected static ?string $heading = '🔴 طلاب بدون نشاط (3+ أيام)';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    protected static ?string $pollingInterval = '60s';

    protected static string $view = 'filament.teacher.widgets.inactive-students-widget';

    public function getInactiveData(): Collection
    {
        $teacherId = TeacherDashboardService::getTeacherId();

        if (!$teacherId) {
            return collect();
        }

        $service = new TeacherDashboardService($teacherId);
        return $service->getInactiveStudents();
    }
}
