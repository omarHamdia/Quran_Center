<?php

namespace App\Filament\Teacher\Resources\MemorizationRecordResource\Pages;

use App\Filament\Teacher\Resources\MemorizationRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMemorizationRecords extends ListRecords
{
    protected static string $resource = MemorizationRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('تسجيل تسميع جديد')
                ->icon('heroicon-o-plus'),
        ];
    }
}