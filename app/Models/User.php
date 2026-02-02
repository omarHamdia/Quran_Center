<?php

namespace App\Models;

use App\Enums\UserRole;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'role',
        'gender',
        'date_of_birth',
        'address',
        'avatar',
        'is_active',
        'last_login_at',
        'phone_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'date_of_birth' => 'date',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ═══════════════════════════════════════════════════════════════
    // FILAMENT ACCESS
    // ═══════════════════════════════════════════════════════════════

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active;
    }

    // ═══════════════════════════════════════════════════════════════
    // ACTIVITY TRACKING
    // ═══════════════════════════════════════════════════════════════

    /**
     * تحديث وقت آخر تسجيل دخول
     */
    public function updateLastLogin(): bool
    {
        return $this->update([
            'last_login_at' => now(),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // ROLE HELPERS
    // ═══════════════════════════════════════════════════════════════

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isTeacher(): bool
    {
        return $this->role === UserRole::TEACHER;
    }

    public function isStudent(): bool
    {
        return $this->role === UserRole::STUDENT;
    }

    // public function hasAdminAccess(): bool
    // {
    //     return $this->role->isAdmin();
    // }

    // public function canManageStudents(): bool
    // {
    //     return $this->role->canManageStudents();
    // }

    // ═══════════════════════════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════════════════════════

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRole($query, UserRole $role)
    {
        return $query->where('role', $role);
    }

    public function scopeAdmins($query)
    {
        return $query->whereIn('role', [UserRole::SUPER_ADMIN, UserRole::ADMIN]);
    }

    public function scopeTeachers($query)
    {
        return $query->where('role', UserRole::TEACHER);
    }

    public function scopeStudents($query)
    {
        return $query->where('role', UserRole::STUDENT);
    }

    // ═══════════════════════════════════════════════════════════════
    // ACCESSORS
    // ═══════════════════════════════════════════════════════════════

    public function getRoleLabelAttribute(): string
    {
        return $this->role->label();
    }

    public function getGenderLabelAttribute(): string
    {
        return match($this->gender) {
            'male' => 'ذكر',
            'female' => 'أنثى',
            default => '-',
        };
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth?->age;
    }

    // ═══════════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════════════════════════════

    public function teacherProfile()
    {
        return $this->hasOne(Teacher::class);
    }

    public function studentProfile()
    {
        return $this->hasOne(Student::class);
    }
}