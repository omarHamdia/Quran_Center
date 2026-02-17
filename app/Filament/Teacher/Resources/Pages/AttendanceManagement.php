<?php

namespace App\Filament\Teacher\Pages;

use App\Models\MemorizationRecord;
use App\Models\Student;
use App\Models\Teacher;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class AttendanceManagement extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'الحضور والغياب';
    protected static ?string $title = 'سجل الحضور والغياب';
    protected static ?string $navigationGroup = 'المتابعة';
    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.teacher.pages.attendance-management';

    public $selectedWeek;
    public $weekStart;
    public $weekEnd;

    public function mount()
    {
        $this->selectedWeek = now()->format('Y-W');
        $this->updateWeekRange();
    }

    public function updatedSelectedWeek()
    {
        $this->updateWeekRange();
    }

    private function updateWeekRange()
    {
        $date = Carbon::now()->setISODate(
            (int) substr($this->selectedWeek, 0, 4),
            (int) substr($this->selectedWeek, 6, 2)
        );
        $this->weekStart = $date->startOfWeek();
        $this->weekEnd = $date->copy()->endOfWeek();
    }

    public function getTeacherId()
    {
        return Teacher::where('user_id', auth()->id())->value('id');
    }

    public function getStudents()
    {
        return Student::where('teacher_id', $this->getTeacherId())
            ->with(['user', 'memorizationRecords' => function ($q) {
                $q->whereBetween('session_date', [$this->weekStart, $this->weekEnd]);
            }])
            ->get();
    }

    public function getWeekDays(): array
    {
        $days = [];
        $current = $this->weekStart->copy();
        
        while ($current <= $this->weekEnd) {
            $days[] = [
                'date' => $current->copy(),
                'name' => $current->translatedFormat('l'),
                'short' => $current->translatedFormat('D'),
                'formatted' => $current->format('Y-m-d'),
            ];
            $current->addDay();
        }
        
        return $days;
    }

    public function getAttendanceStats(): array
    {
        $students = $this->getStudents();
        $totalStudents = $students->count();
        $weekDays = $this->getWeekDays();
        
        $stats = [
            'total_students' => $totalStudents,
            'total_sessions' => 0,
            'absent_count' => 0,
            'attendance_rate' => 0,
        ];

        $presentDays = 0;
        $totalPossible = $totalStudents * count($weekDays);

        foreach ($students as $student) {
            $studentRecords = $student->memorizationRecords;
            $presentDays += $studentRecords->where('status', 'completed')->count();
            $stats['total_sessions'] += $studentRecords->count();
            $stats['absent_count'] += $studentRecords->whereIn('status', ['absent', 'excused'])->count();
        }

        $stats['attendance_rate'] = $totalPossible > 0 
            ? round(($presentDays / $totalPossible) * 100, 1) 
            : 0;

        return $stats;
    }
}