<?php

namespace App\Services;

use App\Models\Surah;
use App\Models\QuranAyah;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class QuranDataService
{
    /**
     * الحصول على جميع السور مع أسمائها
     */
    public static function getSurahOptions(): array
    {
        return Cache::remember('surah_options', 3600, function () {
            return Surah::query()
                ->orderBy('id')
                ->get()
                ->mapWithKeys(function ($surah) {
                    $name = $surah->name_arabic ?? $surah->name_ar ?? $surah->name ?? "سورة {$surah->id}";
                    return [$surah->id => "{$surah->id} - {$name}"];
                })
                ->toArray();
        });
    }

    /**
     * الحصول على اسم سورة
     */
    public static function getSurahName(int $surahId): string
    {
        $surah = Surah::find($surahId);
        if ($surah) {
            return $surah->name_arabic ?? $surah->name_ar ?? $surah->name ?? "سورة {$surahId}";
        }
        return "سورة {$surahId}";
    }

    /**
     * الحصول على عدد آيات سورة
     */
    public static function getSurahAyahCount(int $surahId): int
    {
        return Cache::remember("surah_{$surahId}_ayah_count", 3600, function () use ($surahId) {
            // من جدول surahs
            $surah = Surah::find($surahId);
            if ($surah) {
                $count = $surah->ayah_count ?? $surah->verses_count ?? $surah->total_verses ?? null;
                if ($count) return (int) $count;
            }

            // من جدول quran_ayahs
            return QuranAyah::where('surah_id', $surahId)->count();
        });
    }

    /**
     * الحصول على نطاق صفحات سورة
     */
    public static function getSurahPageRange(int $surahId): array
    {
        return Cache::remember("surah_{$surahId}_page_range", 3600, function () use ($surahId) {
            // من جدول surahs
            $surah = Surah::find($surahId);
            if ($surah && $surah->page_start && $surah->page_end) {
                return [
                    'start' => (int) $surah->page_start,
                    'end' => (int) $surah->page_end,
                ];
            }

            // من جدول quran_ayahs
            $result = QuranAyah::where('surah_id', $surahId)
                ->selectRaw('MIN(page_number) as min_page, MAX(page_number) as max_page')
                ->first();

            return [
                'start' => $result->min_page ?? 1,
                'end' => $result->max_page ?? 604,
            ];
        });
    }

    /**
     * الحصول على صفحة آية معينة
     */
    public static function getAyahPage(int $surahId, int $ayahNumber): ?int
    {
        return QuranAyah::where('surah_id', $surahId)
            ->where('ayah_number', $ayahNumber)
            ->value('page_number');
    }

    /**
     * الحصول على معلومات سورة كاملة
     */
    public static function getSurahInfo(int $surahId): ?array
    {
        $surah = Surah::find($surahId);
        if (!$surah) return null;

        $pageRange = self::getSurahPageRange($surahId);
        $ayahCount = self::getSurahAyahCount($surahId);

        return [
            'id' => $surah->id,
            'name' => self::getSurahName($surahId),
            'ayah_count' => $ayahCount,
            'page_start' => $pageRange['start'],
            'page_end' => $pageRange['end'],
        ];
    }

    /**
     * مسح الكاش
     */
    public static function clearCache(): void
    {
        Cache::forget('surah_options');
        for ($i = 1; $i <= 114; $i++) {
            Cache::forget("surah_{$i}_ayah_count");
            Cache::forget("surah_{$i}_page_range");
        }
    }
}