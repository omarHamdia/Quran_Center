<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Surah extends Model
{
    protected $fillable = [
        'number',
        'name_arabic',
        'name_english',
        'total_ayahs',
        'revelation_type',
        'page_start',
        'page_end',
        'juz_start',
    ];

    // العرض: "الفاتحة (1)"
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name_arabic} ({$this->number})";
    }

    public function getRevelationTypeLabelAttribute(): string
    {
        return $this->revelation_type === 'meccan' ? 'مكية' : 'مدنية';
    }

    public function getPagesCountAttribute(): int
    {
        return $this->page_end - $this->page_start + 1;
    }
}