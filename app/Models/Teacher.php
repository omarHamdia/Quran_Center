<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    protected $fillable = [
        'user_id',
        'specialty',
        'qualification',
        'ijazah_details',
        'hire_date',
        'max_students',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
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
        return $this->hasMany(Attendance::class, 'recorded_by');
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

    public function getStudentsCountAttribute(): int
    {
        return $this->students()->count();
    }

    public function getAvailableSlotsAttribute(): int
    {
        return $this->max_students - $this->students_count;
    }
}