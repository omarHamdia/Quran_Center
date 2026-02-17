<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use App\Models\Surah;
use App\Services\QuranDataService;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewStudent extends ViewRecord
{
    protected static string $resource = StudentResource::class;
    
    protected static string $view = 'filament.resources.student-resource.pages.view-student';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->label('تعديل'),
            Actions\DeleteAction::make()->label('حذف'),
        ];
    }

    // الحصول على الخطط
    public function getPlans()
    {
        return $this->record->memorizationPlans()
            ->with(['teacher.user'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // الحصول على آخر 15 سجل تسميع
    public function getRecentRecords()
    {
        return $this->record->memorizationRecords()
            ->with(['memorizationPlan'])
            ->orderBy('session_date', 'desc')
            ->limit(15)
            ->get();
    }

    // إحصائيات الأسبوع
    public function getWeeklyStats(): array
    {
        $weekStart = now()->startOfWeek();
        
        $records = $this->record->memorizationRecords()
            ->where('session_date', '>=', $weekStart)
            ->get();

        return [
            'total_sessions' => $records->count(),
            'hifz_sessions' => $records->where('session_type', 'hifz')->count(),
            'revision_sessions' => $records->where('session_type', 'revision')->count(),
            'total_ayahs' => $records->where('session_type', 'hifz')->sum('ayahs_count'),
        ];
    }

    // إحصائيات الشهر
    public function getMonthlyStats(): array
    {
        $monthStart = now()->startOfMonth();
        
        $records = $this->record->memorizationRecords()
            ->where('session_date', '>=', $monthStart)
            ->get();

        return [
            'total_sessions' => $records->count(),
            'total_ayahs' => $records->where('session_type', 'hifz')->sum('ayahs_count'),
        ];
    }

    // أسماء السور
    public function getSurahName($surahId): string
    {
        if (!$surahId) return '-';
        
        $surah = Surah::find($surahId);
        if ($surah) {
            return $surah->name_arabic ?? $surah->name_ar ?? $surah->name ?? "سورة {$surahId}";
        }
        
        return QuranDataService::getSurahName($surahId);
    }
}