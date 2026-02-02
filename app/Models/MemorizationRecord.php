<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemorizationRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'teacher_id',
        'student_id',
        'memorization_plan_id',
        'session_type',
        'session_date',
        'session_time',
        'duration_minutes',
        'surah_id',        // من سورة
        'to_surah_id',     // إلى سورة
        'from_ayah',
        'to_ayah',
        'from_page',
        'to_page',
        'ayahs_count',
        'grade',
        'evaluation',
        'mistakes_count',
        'hesitation_count',
        'score',
        'teacher_notes',
        'improvement_notes',
        'status',
    ];

    protected $casts = [
        'session_date' => 'date',
        'session_time' => 'datetime:H:i',
        'duration_minutes' => 'integer',
        'from_ayah' => 'integer',
        'to_ayah' => 'integer',
        'from_page' => 'integer',
        'to_page' => 'integer',
        'ayahs_count' => 'integer',
        'mistakes_count' => 'integer',
    ];

    // العلاقات
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function surah(): BelongsTo
    {
        return $this->belongsTo(Surah::class, 'surah_id');
    }

    public function toSurah(): BelongsTo
    {
        return $this->belongsTo(Surah::class, 'to_surah_id');
    }

    public function memorizationPlan(): BelongsTo
    {
        return $this->belongsTo(MemorizationPlan::class);
    }

    // Boot - حساب عدد الآيات تلقائياً
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($record) {
            // إذا لم يتم تحديد to_surah_id، استخدم surah_id
            if (empty($record->to_surah_id)) {
                $record->to_surah_id = $record->surah_id;
            }

            // حساب عدد الآيات
            if ($record->from_ayah && $record->to_ayah) {
                if ($record->surah_id === $record->to_surah_id) {
                    // نفس السورة
                    $record->ayahs_count = $record->to_ayah - $record->from_ayah + 1;
                } else {
                    // سور متعددة - حساب من quran_ayahs
                    $record->ayahs_count = \App\Models\QuranAyah::where(function ($q) use ($record) {
                        $q->where(function ($sub) use ($record) {
                            $sub->where('surah_id', $record->surah_id)
                                ->where('ayah_number', '>=', $record->from_ayah);
                        })
                        ->orWhere(function ($sub) use ($record) {
                            $sub->where('surah_id', '>', $record->surah_id)
                                ->where('surah_id', '<', $record->to_surah_id);
                        })
                        ->orWhere(function ($sub) use ($record) {
                            $sub->where('surah_id', $record->to_surah_id)
                                ->where('ayah_number', '<=', $record->to_ayah);
                        });
                    })->count();
                }
            }
        });
    }
}