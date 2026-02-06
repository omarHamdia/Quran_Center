<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use Filament\Resources\Pages\ListRecords;

class ListReports extends ListRecords
{
    protected static string $resource = ReportResource::class;

    protected ?string $heading = 'تقارير المحفظين';
    protected ?string $subheading = 'اختر محفظ لعرض تقرير حلقته';
}