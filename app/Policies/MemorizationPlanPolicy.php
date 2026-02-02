<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\MemorizationPlan;
use App\Models\Teacher;
use App\Models\User;

class MemorizationPlanPolicy
{
    public function viewAny(User $user): bool
    {
        // الجميع يرى (لكن سنقيّد الاستعلام حسب الدور)
        return in_array($user->role->value ?? $user->role, ['super_admin', 'admin', 'teacher', 'student'], true);
    }

    public function view(User $user, MemorizationPlan $plan): bool
    {
        $role = $user->role->value ?? $user->role;

        if (in_array($role, ['super_admin', 'admin'], true)) {
            return true; // الأدمن يرى كل شيء
        }

        if ($role === 'teacher') {
            $teacherId = Teacher::where('user_id', $user->id)->value('id');
            return $teacherId && (int) $plan->teacher_id === (int) $teacherId;
        }

        if ($role === 'student') {
            // الطالب يرى خطته فقط
            // نفترض Student مرتبط بـ user_id
            $studentId = \App\Models\Student::where('user_id', $user->id)->value('id');
            return $studentId && (int) $plan->student_id === (int) $studentId;
        }

        return false;
    }

    public function create(User $user): bool
    {
        $role = $user->role->value ?? $user->role;
        return $role === 'teacher'; // فقط المحفّظ ينشئ
    }

    public function update(User $user, MemorizationPlan $plan): bool
    {
        $role = $user->role->value ?? $user->role;
        if ($role !== 'teacher') return false;

        $teacherId = Teacher::where('user_id', $user->id)->value('id');
        return $teacherId && (int) $plan->teacher_id === (int) $teacherId;
    }

    public function delete(User $user, MemorizationPlan $plan): bool
    {
        // لو تحب تمنع الحذف حتى للمحفّظ اجعلها false
        return $this->update($user, $plan);
    }
}
