<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditTeacher extends EditRecord
{
    protected static string $resource = TeacherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('حذف'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['user'] = $this->record->user->toArray();
        $data['user']['password'] = null;
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $userData = $data['user'];
        $currentUserId = $this->record->user_id;

        // التحقق من تكرار رقم الهاتف
        if (User::where('phone', $userData['phone'])->where('id', '!=', $currentUserId)->exists()) {
            Notification::make()
                ->title('رقم الهاتف مستخدم مسبقاً')
                ->danger()
                ->send();
            
            $this->halt();
        }

        // التحقق من تكرار البريد الإلكتروني
        if (!empty($userData['email']) && User::where('email', $userData['email'])->where('id', '!=', $currentUserId)->exists()) {
            Notification::make()
                ->title('البريد الإلكتروني مستخدم مسبقاً')
                ->danger()
                ->send();
            
            $this->halt();
        }

        unset($data['user']);

        // تحديث المستخدم
        $updateData = [
            'name' => $userData['name'],
            'phone' => $userData['phone'],
            'email' => $userData['email'] ?? null,
            'gender' => $userData['gender'],
            'date_of_birth' => $userData['date_of_birth'] ?? null,
            'is_active' => $userData['is_active'] ?? true,
        ];

        if (!empty($userData['password'])) {
            $updateData['password'] = Hash::make($userData['password']);
        }

        $this->record->user->update($updateData);

        return $data;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم تحديث بيانات المحفظ';
    }
}