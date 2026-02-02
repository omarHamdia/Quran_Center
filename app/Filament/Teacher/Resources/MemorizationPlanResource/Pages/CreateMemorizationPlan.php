<?php

namespace App\Filament\Teacher\Resources\MemorizationPlanResource\Pages;

use App\Filament\Teacher\Resources\MemorizationPlanResource;
use App\Models\Student;
use App\Models\Teacher;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateMemorizationPlan extends CreateRecord
{
    protected static string $resource = MemorizationPlanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $teacherId = Teacher::where('user_id', auth()->id())->value('id');

        if (! $teacherId) {
            Notification::make()->title('لا يوجد محفظ مرتبط بهذا المستخدم')->danger()->send();
            $this->halt();
        }

        // ✅ فرض teacher_id (لا ثقة بالـ Hidden)
        $data['teacher_id'] = $teacherId;

        // ✅ تأكد الطالب تابع لنفس المحفظ
        $ok = Student::where('id', $data['student_id'])
            ->where('teacher_id', $teacherId)
            ->exists();

        if (! $ok) {
            Notification::make()->title('لا يمكنك إنشاء خطة لطالب غير تابع لك')->danger()->send();
            $this->halt();
        }

        // ✅ تحقق منطقي بسيط للنطاق (اختياري الآن)
        // يمكن تطويره لاحقًا اعتمادًا على رقم السورة والآيات
        return $data;
    }
}
