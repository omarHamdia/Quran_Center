<?php

namespace App\Http\Controllers;

use App\Models\MemorizationRecord;
use App\Models\Teacher;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TodayPdfController extends Controller
{
    public function __invoke(Request $request)
    {
        // ✅ التأكد أن المستخدم معلم
        if (!auth()->check()) {
            abort(403);
        }

        $teacherId = Teacher::where('user_id', auth()->id())->value('id');

        if (!$teacherId) {
            abort(403, 'لا يوجد حساب معلم مرتبط');
        }

        $today = now()->toDateString();

        $records = MemorizationRecord::where('teacher_id', $teacherId)
            ->where('session_date', $today)
            ->with(['student.user', 'surah', 'toSurah'])
            ->orderBy('created_at')
            ->get();

        $teacherName = auth()->user()->name;

        $pdf = Pdf::loadView('pdf.today-records', [
            'records' => $records,
            'teacherName' => $teacherName,
            'date' => now()->format('Y/m/d'),
            'dateHijri' => $today,
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download("تسميع-اليوم-{$today}.pdf");
    }
}