<?php

namespace App\Filament\Widgets;

use App\Services\AdminDashboardService;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class TopStudentsWidget extends Widget
{
    protected static ?string $heading = '🏆 أفضل الطلاب هذا الشهر';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $pollingInterval = '60s';

    protected static string $view = 'filament.widgets.top-students-widget';

    public function getStudentsData(): Collection
    {
        $service = new AdminDashboardService();
        return $service->getTopStudents();
    }
}
