<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MemorizationPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'student_id',
        'plan_type',
        'title',
        'description',
        'start_date',
        'end_date',
        'from_surah_id',
        'to_surah_id',
        'from_page',
        'to_page',
        'from_ayah',
        'to_ayah',
        'status',
        'plan_status',
        'progress_percentage',
        'total_ayahs',
        'completed_ayahs',
        'total_pages',
        'completed_pages',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'progress_percentage' => 'decimal:2',
        'total_ayahs' => 'integer',
        'completed_ayahs' => 'integer',
        'total_pages' => 'integer',
        'completed_pages' => 'integer',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function fromSurah(): BelongsTo
    {
        return $this->belongsTo(Surah::class, 'from_surah_id');
    }

    public function toSurah(): BelongsTo
    {
        return $this->belongsTo(Surah::class, 'to_surah_id');
    }

    public function memorizationRecords(): HasMany
    {
        return $this->hasMany(MemorizationRecord::class);
    }

    /**
     * نسبة تقدم الآيات
     */
    public function getAyahsProgressAttribute(): string
    {
        return "{$this->completed_ayahs} / {$this->total_ayahs}";
    }

    /**
     * نسبة تقدم الصفحات
     */
    public function getPagesProgressAttribute(): string
    {
        return "{$this->completed_pages} / {$this->total_pages}";
    }
}