<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuranAyah extends Model
{
    protected $table = 'quran_ayahs';

    protected $fillable = [
        'surah_id',
        'ayah_number',
        'page_number',
        'juz_number',
        'hizb_number',
        'rub_number',
    ];

    public function surah()
    {
        return $this->belongsTo(Surah::class);
    }
}
