<?php

namespace App\Filament\Widgets;

use App\Services\AdminDashboardService;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class InactiveStudentsAlertWidget extends Widget
{
    protected static ?string $heading = '🔴 طلاب بدون نشاط (7+ أيام)';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $pollingInterval = '60s';

    protected static string $view = 'filament.widgets.inactive-students-alert-widget';

    public function getInactiveData(): Collection
    {
        $service = new AdminDashboardService();
        return $service->getInactiveStudents();
    }
}
