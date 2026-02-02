<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quran_ayahs', function (Blueprint $table) {
            $table->id();

            // السورة (مرتبط بجدول surahs عندك)
            $table->foreignId('surah_id')->constrained('surahs')->cascadeOnDelete();

            // رقم الآية داخل السورة
            $table->unsignedSmallInteger('ayah_number');

            // رقم الصفحة في مصحف المدينة (1..604)
            $table->unsignedSmallInteger('page_number');

            // اختياري: مفيد لاحقاً للتقارير/التقسيم
            $table->unsignedTinyInteger('juz_number')->nullable();   // 1..30
            $table->unsignedTinyInteger('hizb_number')->nullable();  // 1..60
            $table->unsignedTinyInteger('rub_number')->nullable();   // 1..240

            $table->timestamps();

            // يمنع تكرار نفس الآية لنفس السورة
            $table->unique(['surah_id', 'ayah_number']);

            // فهارس للبحث السريع
            $table->index('page_number');
            $table->index(['surah_id', 'page_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quran_ayahs');
    }
};
