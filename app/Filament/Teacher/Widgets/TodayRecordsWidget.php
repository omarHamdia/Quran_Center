<?php

namespace App\Filament\Teacher\Widgets;

use App\Services\TeacherDashboardService;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class TodayRecordsWidget extends Widget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $pollingInterval = '30s';

    protected static string $view = 'filament.teacher.widgets.today-records-widget';

    public function getTodayData(): Collection
    {
        $teacherId = TeacherDashboardService::getTeacherId();

        if (!$teacherId) {
            return collect();
        }

        $service = new TeacherDashboardService($teacherId);
        return $service->getTodayRecords();
    }

    public function getTodaySummary(): array
    {
        $teacherId = TeacherDashboardService::getTeacherId();

        if (!$teacherId) {
            return ['total_sessions' => 0, 'hifz_ayahs' => 0, 'revision_ayahs' => 0, 'students_count' => 0];
        }

        $service = new TeacherDashboardService($teacherId);
        return $service->getTodaySummary();
    }
}
