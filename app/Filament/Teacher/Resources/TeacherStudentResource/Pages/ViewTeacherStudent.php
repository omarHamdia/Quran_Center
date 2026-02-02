<?php

namespace App\Filament\Teacher\Resources\TeacherStudentResource\Pages;

use App\Filament\Teacher\Resources\TeacherStudentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTeacherStudent extends ViewRecord
{
    protected static string $resource = TeacherStudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
