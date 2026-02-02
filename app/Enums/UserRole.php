<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case TEACHER = 'teacher';
    case STUDENT = 'student';

    public function label(): string
    {
        return match($this) {
            self::SUPER_ADMIN => 'مدير النظام',
            self::ADMIN => 'أمير المركز',
            self::TEACHER => 'محفظ',
            self::STUDENT => 'طالب',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::SUPER_ADMIN => 'danger',
            self::ADMIN => 'warning',
            self::TEACHER => 'success',
            self::STUDENT => 'info',
        };
    }

    public static function toArray(): array
    {
        return collect(self::cases())->mapWithKeys(fn($role) => [
            $role->value => $role->label()
        ])->toArray();
    }
}