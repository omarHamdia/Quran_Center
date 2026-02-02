<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MemorizationPlan extends Model
{
    protected $table = 'memorization_plans';

    /**
     * الحقول القابلة للتعبئة
     */
    protected $fillable = [
        'student_id',
        'teacher_id',
        'plan_type',
        'title',
        'description',
        'start_date',
        'end_date',

        // نطاق الحفظ (آيات)
        'from_surah_id',
        'from_ayah',
        'to_surah_id',
        'to_ayah',

        // نطاق الحفظ (صفحات)
        'from_page',
        'to_page',

        // الحالة والتقدم
        'status',              // pending | in_progress | completed | cancelled
        'plan_status',         // not_started | in_progress | completed | cancelled
        'progress_percentage', // cache فقط
        'total_ayahs',
        'completed_ayahs',

        'notes',
    ];

    /**
     * تحويلات تلقائية
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'from_page'  => 'integer',
        'to_page'    => 'integer',
        'from_ayah'  => 'integer',
        'to_ayah'    => 'integer',
        'total_ayahs' => 'integer',
        'completed_ayahs' => 'integer',
        'progress_percentage' => 'integer',
    ];

    /* ───────────────────────── العلاقات ───────────────────────── */

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
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

    /* ───────────────────────── Accessors ───────────────────────── */

    /**
     * هل الخطة مبنية على صفحات؟
     */
    public function getIsPageBasedAttribute(): bool
    {
        return !is_null($this->from_page) && !is_null($this->to_page);
    }

    /**
     * هل الخطة مبنية على آيات؟
     */
    public function getIsAyahBasedAttribute(): bool
    {
        return !is_null($this->from_surah_id) && !is_null($this->to_surah_id);
    }

    /**
     * حساب نسبة التقدم ديناميكيًا (كمصدر ثانوي)
     */
    public function getCalculatedProgressAttribute(): int
    {
        if ($this->total_ayahs <= 0) {
            return 0;
        }

        return (int) round(
            ($this->completed_ayahs / $this->total_ayahs) * 100
        );
    }

    /**
     * اسم الخطة مع نوعها (مفيد للـ Select)
     */
    public function getDisplayTitleAttribute(): string
    {
        return "{$this->title} ({$this->progress_percentage}%)";
    }
}
