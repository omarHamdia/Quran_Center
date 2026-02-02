<?php

namespace Database\Seeders;

use App\Models\QuranAyah;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class QuranAyahSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/quran_ayah_pages_madinah_604.json');

        if (!File::exists($path)) {
            $this->command?->error("Dataset file not found: {$path}");
            $this->command?->line("Create it first, then re-run: php artisan db:seed --class=QuranAyahSeeder");
            return;
        }

        $json = File::get($path);
        $rows = json_decode($json, true);

        if (!is_array($rows) || empty($rows)) {
            $this->command?->error("Dataset JSON is invalid or empty: {$path}");
            return;
        }

        // تحقق أساسي: الصفحات ضمن 1..604
        foreach ($rows as $i => $r) {
            if (!isset($r['surah_id'], $r['ayah_number'], $r['page_number'])) {
                $this->command?->error("Row #{$i} missing keys (surah_id, ayah_number, page_number).");
                return;
            }
            if ($r['page_number'] < 1 || $r['page_number'] > 604) {
                $this->command?->error("Row #{$i} has invalid page_number={$r['page_number']} (must be 1..604).");
                return;
            }
        }

        DB::transaction(function () use ($rows) {
            // إدخال على دفعات لتجنب استهلاك الذاكرة/الوقت
            $chunks = array_chunk($rows, 1000);

            foreach ($chunks as $chunk) {
                // upsert لتحديث/إدخال بدون تكرار
                QuranAyah::query()->upsert(
                    $chunk,
                    ['surah_id', 'ayah_number'], // unique keys
                    ['page_number', 'juz_number', 'hizb_number', 'rub_number', 'updated_at']
                );
            }
        });

        $count = QuranAyah::query()->count();
        $this->command?->info("quran_ayahs seeded successfully. Total rows: {$count}");
    }
}
