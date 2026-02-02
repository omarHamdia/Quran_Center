<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'user_id',
        'teacher_id',
        'guardian_name',
        'guardian_phone',
        'enrollment_date',
        'current_level',
        'memorized_juz',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'enrollment_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function memorizationPlans(): HasMany
    {
        return $this->hasMany(MemorizationPlan::class);
    }

    public function memorizationRecords(): HasMany
    {
        return $this->hasMany(MemorizationRecord::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    // Accessors
    public function getNameAttribute(): string
    {
        return $this->user->name;
    }

    public function getPhoneAttribute(): string
    {
        return $this->user->phone;
    }

    public function getCurrentLevelLabelAttribute(): string
    {
        return match($this->current_level) {
            'beginner' => 'مبتدئ',
            'elementary' => 'أساسي',
            'intermediate' => 'متوسط',
            'advanced' => 'متقدم',
            'memorizer' => 'حافظ',
            default => '-',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'active' => 'نشط',
            'inactive' => 'غير نشط',
            'graduated' => 'متخرج',
            'withdrawn' => 'منسحب',
            default => '-',
        };
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }
}