<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\StudentResource;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (User::where('phone', $data['user']['phone'])->exists()) {
            Notification::make()
                ->title('رقم الهاتف مستخدم مسبقاً')
                ->danger()
                ->send();
            
            $this->halt();
        }

        $user = User::create([
            'name' => $data['user']['name'],
            'phone' => $data['user']['phone'],
            'password' => Hash::make($data['user']['password']),
            'gender' => $data['user']['gender'],
            'date_of_birth' => $data['user']['date_of_birth'] ?? null,
            'role' => UserRole::STUDENT,
            'is_active' => $data['user']['is_active'] ?? true,
        ]);

        unset($data['user']);
        $data['user_id'] = $user->id;

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إضافة الطالب بنجاح';
    }
}