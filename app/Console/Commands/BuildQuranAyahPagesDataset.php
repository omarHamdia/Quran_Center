<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class BuildQuranAyahPagesDataset extends Command
{
    protected $signature = 'quran:build-ayah-pages-dataset';

    protected $description = 'Build ayah → page mapping (Madinah Mushaf, 604 pages) into local JSON dataset';

    public function handle(): int
    {
        $outputPath = database_path('seeders/data/quran_ayah_pages_madinah_604.json');

        File::ensureDirectoryExists(dirname($outputPath));

        $dataset = [];

        for ($page = 1; $page <= 604; $page++) {
            $this->info("Fetching page {$page}/604");

            $response = Http::timeout(30)->get(
                "https://api.quran.com/api/v4/verses/by_page/{$page}"
            );

            if (!$response->ok()) {
                $this->error("Failed to fetch page {$page}");
                return self::FAILURE;
            }

            $json = $response->json();

            $verses = $json['verses']
                ?? $json['data']['verses']
                ?? null;

            if (!is_array($verses)) {
                $this->error("Unexpected response structure on page {$page}");
                return self::FAILURE;
            }

            foreach ($verses as $verse) {
                if (!isset($verse['verse_key'])) {
                    continue;
                }

                [$surah, $ayah] = array_map('intval', explode(':', $verse['verse_key']));

                $dataset[] = [
                    'surah_id'    => $surah,
                    'ayah_number' => $ayah,
                    'page_number' => $page,
                ];
            }
        }

        // إزالة أي تكرار (أمان إضافي)
        $unique = [];
        foreach ($dataset as $row) {
            $key = $row['surah_id'] . ':' . $row['ayah_number'];
            $unique[$key] = $row;
        }

        File::put(
            $outputPath,
            json_encode(array_values($unique), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        $this->info("Dataset generated successfully:");
        $this->info($outputPath);
        $this->info("Total ayahs: " . count($unique) . " (expected ≈ 6236)");

        return self::SUCCESS;
    }
}
