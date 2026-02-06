<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use App\Models\Student;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Illuminate\Contracts\View\View;

class ViewReport extends ViewRecord
{
    protected static string $resource = ReportResource::class;

    protected static string $view = 'filament.resources.report-resource.pages.view-report';

    public function getTitle(): string
    {
        return "تقرير حلقة: {$this->record->user->name}";
    }

    public function getStudents()
    {
        return $this->record->students()
            ->with(['user', 'memorizationPlans', 'memorizationRecords'])
            ->get();
    }

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
            'students_with_records' => $records->pluck('student_id')->unique()->count(),
        ];
    }

    public function getMonthlyStats(): array
    {
        $monthStart = now()->startOfMonth();
        
        $records = $this->record->memorizationRecords()
            ->where('session_date', '>=', $monthStart)
            ->get();

        return [
            'total_sessions' => $records->count(),
            'total_ayahs' => $records->where('session_type', 'hifz')->sum('ayahs_count'),
            'average_per_student' => $this->record->students()->count() > 0 
                ? round($records->count() / $this->record->students()->count(), 1) 
                : 0,
        ];
    }
}