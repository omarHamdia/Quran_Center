<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسميع اليوم - {{ $date }}</title>
    <style>
        @font-face {
            font-family: 'Cairo';
            src: url('{{ storage_path("fonts/cairo.ttf") }}');
        }
        * {
            font-family: 'Cairo', 'DejaVu Sans', sans-serif;
        }
        body {
            direction: rtl;
            text-align: right;
            font-size: 12px;
            color: #333;
            margin: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #059669;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #059669;
            font-size: 22px;
            margin: 0;
        }
        .header p {
            color: #666;
            margin: 5px 0 0;
            font-size: 14px;
        }
        .meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 13px;
        }
        .meta-item {
            display: inline-block;
            margin-left: 30px;
        }
        .meta-label {
            color: #888;
        }
        .meta-value {
            font-weight: bold;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #059669;
            color: white;
            padding: 10px 8px;
            font-size: 11px;
            text-align: right;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11px;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .summary {
            margin-top: 20px;
            padding: 15px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
        }
        .summary h3 {
            color: #059669;
            margin: 0 0 10px;
            font-size: 14px;
        }
        .summary-grid {
            display: inline-block;
            width: 24%;
            text-align: center;
        }
        .summary-number {
            font-size: 20px;
            font-weight: bold;
            color: #059669;
        }
        .summary-label {
            font-size: 10px;
            color: #666;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #999;
            font-size: 10px;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>📋 مركز تحفيظ القرآن الكريم</h1>
        <p>تقرير تسميع اليوم - {{ $date }}</p>
    </div>

    <div class="meta">
        <span class="meta-item">
            <span class="meta-label">المحفظ: </span>
            <span class="meta-value">{{ $teacherName }}</span>
        </span>
        <span class="meta-item">
            <span class="meta-label">التاريخ: </span>
            <span class="meta-value">{{ $dateHijri }}</span>
        </span>
        <span class="meta-item">
            <span class="meta-label">عدد الجلسات: </span>
            <span class="meta-value">{{ $records->count() }}</span>
        </span>
        <span class="meta-item">
            <span class="meta-label">عدد الطلاب: </span>
            <span class="meta-value">{{ $records->unique('student_id')->count() }}</span>
        </span>
    </div>

    @if($records->isEmpty())
        <div style="text-align: center; padding: 40px; color: #999;">
            <p style="font-size: 16px;">لا توجد جلسات تسميع مسجلة اليوم</p>
        </div>
    @else
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>الطالب</th>
                    <th>النوع</th>
                    <th>السورة</th>
                    <th>من آية</th>
                    <th>إلى آية</th>
                    <th>عدد الآيات</th>
                    <th>التقييم</th>
                    <th>الأخطاء</th>
                    <th>ملاحظات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $index => $record)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td style="font-weight: bold;">{{ $record->student?->user?->name ?? '-' }}</td>
                        <td>
                            @php
                                $typeLabel = match($record->session_type) {
                                    'hifz' => 'حفظ جديد',
                                    'revision' => 'مراجعة',
                                    'test' => 'اختبار',
                                    default => $record->session_type,
                                };
                                $typeBadge = match($record->session_type) {
                                    'hifz' => 'badge-success',
                                    'revision' => 'badge-info',
                                    'test' => 'badge-warning',
                                    default => '',
                                };
                            @endphp
                            <span class="badge {{ $typeBadge }}">{{ $typeLabel }}</span>
                        </td>
                        <td>{{ $record->surah?->name_arabic ?? '-' }}</td>
                        <td>{{ $record->from_ayah }}</td>
                        <td>{{ $record->to_ayah }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ $record->ayahs_count ?? 0 }}</td>
                        <td>
                            @php
                                $evalLabel = match($record->evaluation) {
                                    'excellent' => 'ممتاز',
                                    'very_good' => 'جيد جداً',
                                    'good' => 'جيد',
                                    'acceptable' => 'مقبول',
                                    'needs_review' => 'يحتاج مراجعة',
                                    default => '-',
                                };
                                $evalBadge = match($record->evaluation) {
                                    'excellent' => 'badge-success',
                                    'very_good' => 'badge-info',
                                    'good' => 'badge-info',
                                    'acceptable' => 'badge-warning',
                                    'needs_review' => 'badge-danger',
                                    default => '',
                                };
                            @endphp
                            <span class="badge {{ $evalBadge }}">{{ $evalLabel }}</span>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge {{ ($record->mistakes_count ?? 0) > 5 ? 'badge-danger' : (($record->mistakes_count ?? 0) > 2 ? 'badge-warning' : 'badge-success') }}">
                                {{ $record->mistakes_count ?? 0 }}
                            </span>
                        </td>
                        <td style="font-size: 10px; max-width: 120px;">{{ Str::limit($record->teacher_notes, 30) ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <h3>📊 ملخص اليوم</h3>
            <div>
                <div class="summary-grid">
                    <div class="summary-number">{{ $records->count() }}</div>
                    <div class="summary-label">إجمالي الجلسات</div>
                </div>
                <div class="summary-grid">
                    <div class="summary-number">{{ $records->where('session_type', 'hifz')->sum('ayahs_count') }}</div>
                    <div class="summary-label">آيات حفظ</div>
                </div>
                <div class="summary-grid">
                    <div class="summary-number">{{ $records->where('session_type', 'revision')->sum('ayahs_count') }}</div>
                    <div class="summary-label">آيات مراجعة</div>
                </div>
                <div class="summary-grid">
                    <div class="summary-number">{{ $records->unique('student_id')->count() }}</div>
                    <div class="summary-label">ع��د الطلاب</div>
                </div>
            </div>
        </div>
    @endif

    <div class="footer">
        تم إنشاء هذا التقرير آلياً من نظام إدارة مركز تحفيظ القرآن الكريم | {{ now()->format('Y/m/d H:i') }}
    </div>

</body>
</html>
