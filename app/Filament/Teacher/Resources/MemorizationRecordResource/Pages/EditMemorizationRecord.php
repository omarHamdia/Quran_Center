<?php

namespace App\Filament\Teacher\Resources\MemorizationRecordResource\Pages;

use App\Filament\Teacher\Resources\MemorizationRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditMemorizationRecord extends EditRecord
{
    protected static string $resource = MemorizationRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()->label('عرض'),
            Actions\DeleteAction::make()->label('حذف'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('تم التحديث')
            ->body('تم تحديث سجل التسميع بنجاح');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['from_ayah']) && isset($data['to_ayah'])) {
            $data['ayahs_count'] = $data['to_ayah'] - $data['from_ayah'] + 1;
        }

        return $data;
    }
}