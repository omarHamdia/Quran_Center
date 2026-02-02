<?php

namespace App\Filament\Teacher\Resources\MemorizationPlanResource\Pages;

use App\Filament\Teacher\Resources\MemorizationPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMemorizationPlan extends EditRecord
{
    protected static string $resource = MemorizationPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
