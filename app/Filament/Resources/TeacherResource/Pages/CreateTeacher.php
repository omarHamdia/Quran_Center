<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\TeacherResource;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CreateTeacher extends CreateRecord
{
    protected static string $resource = TeacherResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // التحقق من تكرار رقم الهاتف
        if (User::where('phone', $data['user']['phone'])->exists()) {
            Notification::make()
                ->title('رقم الهاتف مستخدم مسبقاً')
                ->danger()
                ->send();
            
            $this->halt();
        }

        // التحقق من تكرار البريد الإلكتروني
        if (!empty($data['user']['email']) && User::where('email', $data['user']['email'])->exists()) {
            Notification::make()
                ->title('البريد الإلكتروني مستخدم مسبقاً')
                ->danger()
                ->send();
            
            $this->halt();
        }

        // إنشاء المستخدم
        $user = User::create([
            'name' => $data['user']['name'],
            'phone' => $data['user']['phone'],
            'email' => $data['user']['email'] ?? null,
            'password' => Hash::make($data['user']['password']),
            'gender' => $data['user']['gender'],
            'date_of_birth' => $data['user']['date_of_birth'] ?? null,
            'role' => UserRole::TEACHER,
            'is_active' => $data['user']['is_active'] ?? true,
        ]);

        unset($data['user']);
        $data['user_id'] = $user->id;

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إضافة المحفظ بنجاح';
    }
}