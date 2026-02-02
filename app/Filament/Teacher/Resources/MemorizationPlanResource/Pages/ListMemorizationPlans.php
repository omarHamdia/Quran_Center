<?php

namespace App\Filament\Teacher\Resources\MemorizationPlanResource\Pages;

use App\Filament\Teacher\Resources\MemorizationPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMemorizationPlans extends ListRecords
{
    protected static string $resource = MemorizationPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
