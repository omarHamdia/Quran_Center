<?php

namespace App\Filament\Teacher\Resources\MemorizationPlanResource\Pages;

use App\Filament\Teacher\Resources\MemorizationPlanResource;
use App\Models\MemorizationRecord;
use App\Models\Surah;
use App\Services\QuranDataService;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\View;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\HtmlString;

class ViewMemorizationPlan extends ViewRecord
{
    protected static string $resource = MemorizationPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->label('تعديل'),
            Actions\DeleteAction::make()->label('حذف'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // ═══════════════════════════════════════
                // بيانات الخطة الأساسية
                // ═══════════════════════════════════════
                Section::make('بيانات الخطة')
                    ->schema([
                        TextEntry::make('student.user.name')
                            ->label('الطالب')
                            ->icon('heroicon-o-user'),

                        TextEntry::make('plan_type')
                            ->label('نوع الخطة')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                'weekly' => 'info',
                                'monthly' => 'warning',
                                'yearly' => 'success',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'weekly' => '📅 أسبوعية',
                                'monthly' => '📆 شهرية',
                                'yearly' => '📆 سنوية',
                                default => $state,
                            }),

                        TextEntry::make('title')
                            ->label('العنوان'),

                        TextEntry::make('start_date')
                            ->label('تاريخ البداية')
                            ->date('Y/m/d')
                            ->icon('heroicon-o-calendar'),

                        TextEntry::make('end_date')
                            ->label('تاريخ النهاية')
                            ->date('Y/m/d')
                            ->icon('heroicon-o-calendar'),

                        TextEntry::make('description')
                            ->label('الوصف')
                            ->default('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                // ═══════════════════════════════════════
                // نطاق الحفظ المطلوب
                // ═══════════════════════════════════════
                Section::make('📖 نطاق الحفظ المطلوب')
                    ->schema([
                        TextEntry::make('range_summary')
                            ->label('النطاق')
                            ->state(function ($record) {
                                $fromName = QuranDataService::getSurahName($record->from_surah_id);
                                $toName = QuranDataService::getSurahName($record->to_surah_id);

                                if ($record->from_surah_id == $record->to_surah_id) {
                                    return "سورة {$fromName} (آية {$record->from_ayah} - {$record->to_ayah})";
                                }

                                return "من {$fromName}:{$record->from_ayah} إلى {$toName}:{$record->to_ayah}";
                            })
                            ->icon('heroicon-o-book-open')
                            ->weight('bold')
                            ->size('lg')
                            ->columnSpanFull(),

                        TextEntry::make('from_surah_display')
                            ->label('من سورة')
                            ->state(fn ($record) => QuranDataService::getSurahName($record->from_surah_id))
                            ->badge()
                            ->color('success'),

                        TextEntry::make('to_surah_display')
                            ->label('إلى سورة')
                            ->state(fn ($record) => QuranDataService::getSurahName($record->to_surah_id))
                            ->badge()
                            ->color('danger'),

                        TextEntry::make('from_ayah')
                            ->label('من آية')
                            ->badge()
                            ->color('success'),

                        TextEntry::make('to_ayah')
                            ->label('إلى آية')
                            ->badge()
                            ->color('danger'),

                        TextEntry::make('from_page')
                            ->label('من صفحة')
                            ->default('-'),

                        TextEntry::make('to_page')
                            ->label('إلى صفحة')
                            ->default('-'),
                    ])
                    ->columns(3),

                // ═══════════════════════════════════════
                // 📊 تقدم الحفظ (القسم الجديد)
                // ═══════════════════════════════════════
                Section::make('📊 تقدم الحفظ')
                    ->schema([
                        // شريط التقدم
                        TextEntry::make('progress_bar')
                            ->label('')
                            ->state(function ($record) {
                                $percent = $record->progress_percentage ?? 0;
                                $color = match (true) {
                                    $percent >= 100 => 'bg-green-500',
                                    $percent >= 75 => 'bg-blue-500',
                                    $percent >= 50 => 'bg-yellow-500',
                                    $percent >= 25 => 'bg-orange-500',
                                    default => 'bg-red-500',
                                };

                                return new HtmlString("
                                    <div class='w-full'>
                                        <div class='flex justify-between mb-1'>
                                            <span class='text-sm font-medium'>نسبة الإنجاز</span>
                                            <span class='text-sm font-bold text-primary-600'>{$percent}%</span>
                                        </div>
                                        <div class='w-full bg-gray-200 rounded-full h-4'>
                                            <div class='{$color} h-4 rounded-full transition-all duration-500' style='width: {$percent}%'></div>
                                        </div>
                                    </div>
                                ");
                            })
                            ->columnSpanFull(),

                        // إحصائيات الآيات
                        TextEntry::make('ayahs_stats')
                            ->label('📖 الآيات')
                            ->state(function ($record) {
                                $completed = $record->completed_ayahs ?? 0;
                                $total = $record->total_ayahs ?? 0;
                                $remaining = $total - $completed;

                                return new HtmlString("
                                    <div class='space-y-1'>
                                        <div class='flex justify-between'>
                                            <span>المحفوظة:</span>
                                            <span class='font-bold text-green-600'>{$completed} آية</span>
                                        </div>
                                        <div class='flex justify-between'>
                                            <span>الإجمالي:</span>
                                            <span class='font-bold'>{$total} آية</span>
                                        </div>
                                        <div class='flex justify-between'>
                                            <span>المتبقية:</span>
                                            <span class='font-bold text-orange-600'>{$remaining} آية</span>
                                        </div>
                                    </div>
                                ");
                            }),

                        // إحصائيات الصفحات
                        TextEntry::make('pages_stats')
                            ->label('📄 الصفحات')
                            ->state(function ($record) {
                                $completed = $record->completed_pages ?? 0;
                                $total = $record->total_pages ?? 0;
                                $remaining = $total - $completed;

                                return new HtmlString("
                                    <div class='space-y-1'>
                                        <div class='flex justify-between'>
                                            <span>المسمّعة:</span>
                                            <span class='font-bold text-green-600'>{$completed} صفحة</span>
                                        </div>
                                        <div class='flex justify-between'>
                                            <span>الإجمالي:</span>
                                            <span class='font-bold'>{$total} صفحة</span>
                                        </div>
                                        <div class='flex justify-between'>
                                            <span>المتبقية:</span>
                                            <span class='font-bold text-orange-600'>{$remaining} صفحة</span>
                                        </div>
                                    </div>
                                ");
                            }),

                        // حالة الخطة
                        TextEntry::make('plan_status')
                            ->label('حالة الخطة')
                            ->badge()
                            ->size('lg')
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'not_started' => '⏳ لم تبدأ',
                                'in_progress' => '🔄 قيد التنفيذ',
                                'completed' => '✅ مكتملة',
                                'cancelled' => '❌ ملغاة',
                                default => $state ?? 'غير محدد',
                            })
                            ->color(fn ($state) => match ($state) {
                                'not_started' => 'gray',
                                'in_progress' => 'warning',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                default => 'gray',
                            }),
                    ])
                    ->columns(3),

                // ═══════════════════════════════════════
                // 📋 سجلات التسميع
                // ═══════════════════════════════════════
                Section::make('📋 سجلات التسميع')
                    ->schema([
                        TextEntry::make('memorization_records_list')
                            ->label('')
                            ->state(function ($record) {
                                $records = MemorizationRecord::where('student_id', $record->student_id)
                                    ->where('session_type', 'hifz')
                                    ->where('status', 'completed')
                                    ->orderBy('session_date', 'desc')
                                    ->limit(10)
                                    ->get();

                                if ($records->isEmpty()) {
                                    return new HtmlString("<p class='text-gray-500'>لا توجد سجلات تسميع بعد</p>");
                                }

                                $html = "<div class='space-y-2'>";
                                $html .= "<table class='w-full text-sm'>";
                                $html .= "<thead class='bg-gray-100'>";
                                $html .= "<tr>";
                                $html .= "<th class='p-2 text-right'>التاريخ</th>";
                                $html .= "<th class='p-2 text-right'>السورة</th>";
                                $html .= "<th class='p-2 text-right'>من آية</th>";
                                $html .= "<th class='p-2 text-right'>إلى آية</th>";
                                $html .= "<th class='p-2 text-right'>عدد الآيات</th>";
                                $html .= "<th class='p-2 text-right'>الصفحات</th>";
                                $html .= "<th class='p-2 text-right'>التقييم</th>";
                                $html .= "</tr>";
                                $html .= "</thead>";
                                $html .= "<tbody>";

                                foreach ($records as $rec) {
                                    $fromSurah = QuranDataService::getSurahName($rec->surah_id);
                                    $toSurah = $rec->to_surah_id ? QuranDataService::getSurahName($rec->to_surah_id) : $fromSurah;
                                    
                                    $surahDisplay = $rec->surah_id == $rec->to_surah_id 
                                        ? $fromSurah 
                                        : "{$fromSurah} → {$toSurah}";

                                    $pagesDisplay = ($rec->from_page && $rec->to_page) 
                                        ? "{$rec->from_page} - {$rec->to_page}" 
                                        : '-';

                                    $evalColor = match ($rec->evaluation) {
                                        'excellent' => 'text-green-600',
                                        'very_good' => 'text-blue-600',
                                        'good' => 'text-yellow-600',
                                        'acceptable' => 'text-orange-600',
                                        'needs_review' => 'text-red-600',
                                        default => 'text-gray-600',
                                    };

                                    $evalText = match ($rec->evaluation) {
                                        'excellent' => 'ممتاز',
                                        'very_good' => 'جيد جداً',
                                        'good' => 'جيد',
                                        'acceptable' => 'مقبول',
                                        'needs_review' => 'يحتاج مراجعة',
                                        default => '-',
                                    };

                                    $html .= "<tr class='border-b'>";
                                    $html .= "<td class='p-2'>" . $rec->session_date->format('Y/m/d') . "</td>";
                                    $html .= "<td class='p-2'>{$surahDisplay}</td>";
                                    $html .= "<td class='p-2'>{$rec->from_ayah}</td>";
                                    $html .= "<td class='p-2'>{$rec->to_ayah}</td>";
                                    $html .= "<td class='p-2 font-bold'>{$rec->ayahs_count}</td>";
                                    $html .= "<td class='p-2'>{$pagesDisplay}</td>";
                                    $html .= "<td class='p-2 {$evalColor} font-bold'>{$evalText}</td>";
                                    $html .= "</tr>";
                                }

                                $html .= "</tbody>";
                                $html .= "</table>";
                                $html .= "</div>";

                                return new HtmlString($html);
                            })
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                // ═══════════════════════════════════════
                // ملاحظات
                // ═══════════════════════════════════════
                Section::make('ملاحظات')
                    ->schema([
                        TextEntry::make('notes')
                            ->label('')
                            ->default('لا توجد ملاحظات')
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),

                // ═══════════════════════════════════════
                // معلومات إضافية
                // ═══════════════════════════════════════
                Section::make('معلومات إضافية')
                    ->schema([
                        TextEntry::make('teacher.user.name')
                            ->label('المحفظ')
                            ->icon('heroicon-o-user'),

                        TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->dateTime('Y/m/d H:i'),

                        TextEntry::make('updated_at')
                            ->label('آخر تحديث')
                            ->dateTime('Y/m/d H:i'),
                    ])
                    ->columns(3)
                    ->collapsed(),
            ]);
    }
}