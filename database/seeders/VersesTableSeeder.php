<?php

namespace Database\Seeders;

use App\Models\Verse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\LazyCollection;

class VersesTableSeeder extends Seeder
{
    public function run(): void
    {
        $path = 'quran/verses_pages.csv';

        if (! Storage::exists($path)) {
            $this->command?->error("ملف البيانات غير موجود: storage/app/{$path}");
            $this->command?->line("ضع الملف هنا ثم أعد تشغيل السيدر.");
            return;
        }

        // تفريغ الجدول (اختياري)
        DB::table('verses')->truncate();

        $fullPath = Storage::path($path);

        $rows = LazyCollection::make(function () use ($fullPath) {
            $handle = fopen($fullPath, 'r');

            if ($handle === false) {
                return;
            }

            $first = true;

            while (($data = fgetcsv($handle)) !== false) {
                if (! is_array($data) || count($data) < 3) {
                    continue;
                }

                // تخطي الهيدر إن وجد
                if ($first) {
                    $first = false;

                    $maybeHeader = strtolower(trim((string) $data[0]));
                    if (in_array($maybeHeader, ['surah_number', 'surah', 'sura'], true)) {
                        continue;
                    }
                }

                $surah = (int) trim((string) $data[0]);
                $ayah  = (int) trim((string) $data[1]);
                $page  = (int) trim((string) $data[2]);

                if ($surah <= 0 || $ayah <= 0 || $page <= 0) {
                    continue;
                }

                yield [
                    'surah_number' => $surah,
                    'ayah_number'  => $ayah,
                    'page_number'  => $page,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];
            }

            fclose($handle);
        });

        $chunkSize = 1000;

        $rows->chunk($chunkSize)->each(function ($chunk) {
            DB::table('verses')->insert($chunk->all());
        });

        $count = Verse::count();
        $this->command?->info("تم إدخال {$count} آية في جدول verses بنجاح.");
    }
}
