<?php

namespace App\Filament\Teacher\Resources\MemorizationPlanResource\Pages;

use App\Filament\Teacher\Resources\MemorizationPlanResource;
use App\Models\Surah;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewMemorizationPlan extends ViewRecord
{
    protected static string $resource = MemorizationPlanResource::class;

    // أسماء السور
    private static array $surahNames = [
        1 => 'الفاتحة', 2 => 'البقرة', 3 => 'آل عمران', 4 => 'النساء', 5 => 'المائدة',
        6 => 'الأنعام', 7 => 'الأعراف', 8 => 'الأنفال', 9 => 'التوبة', 10 => 'يونس',
        11 => 'هود', 12 => 'يوسف', 13 => 'الرعد', 14 => 'إبراهيم', 15 => 'الحجر',
        16 => 'النحل', 17 => 'الإسراء', 18 => 'الكهف', 19 => 'مريم', 20 => 'طه',
        21 => 'الأنبياء', 22 => 'الحج', 23 => 'المؤمنون', 24 => 'النور', 25 => 'الفرقان',
        26 => 'الشعراء', 27 => 'النمل', 28 => 'القصص', 29 => 'العنكبوت', 30 => 'الروم',
        31 => 'لقمان', 32 => 'السجدة', 33 => 'الأحزاب', 34 => 'سبأ', 35 => 'فاطر',
        36 => 'يس', 37 => 'الصافات', 38 => 'ص', 39 => 'الزمر', 40 => 'غافر',
        41 => 'فصلت', 42 => 'الشورى', 43 => 'الزخرف', 44 => 'الدخان', 45 => 'الجاثية',
        46 => 'الأحقاف', 47 => 'محمد', 48 => 'الفتح', 49 => 'الحجرات', 50 => 'ق',
        51 => 'الذاريات', 52 => 'الطور', 53 => 'النجم', 54 => 'القمر', 55 => 'الرحمن',
        56 => 'الواقعة', 57 => 'الحديد', 58 => 'المجادلة', 59 => 'الحشر', 60 => 'الممتحنة',
        61 => 'الصف', 62 => 'الجمعة', 63 => 'المنافقون', 64 => 'التغابن', 65 => 'الطلاق',
        66 => 'التحريم', 67 => 'الملك', 68 => 'القلم', 69 => 'الحاقة', 70 => 'المعارج',
        71 => 'نوح', 72 => 'الجن', 73 => 'المزمل', 74 => 'المدثر', 75 => 'القيامة',
        76 => 'الإنسان', 77 => 'المرسلات', 78 => 'النبأ', 79 => 'النازعات', 80 => 'عبس',
        81 => 'التكوير', 82 => 'الانفطار', 83 => 'المطففين', 84 => 'الانشقاق', 85 => 'البروج',
        86 => 'الطارق', 87 => 'الأعلى', 88 => 'الغاشية', 89 => 'الفجر', 90 => 'البلد',
        91 => 'الشمس', 92 => 'الليل', 93 => 'الضحى', 94 => 'الشرح', 95 => 'التين',
        96 => 'العلق', 97 => 'القدر', 98 => 'البينة', 99 => 'الزلزلة', 100 => 'العاديات',
        101 => 'القارعة', 102 => 'التكاثر', 103 => 'العصر', 104 => 'الهمزة', 105 => 'الفيل',
        106 => 'قريش', 107 => 'الماعون', 108 => 'الكوثر', 109 => 'الكافرون', 110 => 'النصر',
        111 => 'المسد', 112 => 'الإخلاص', 113 => 'الفلق', 114 => 'الناس',
    ];

    private function getSurahName($surahId): string
    {
        $surah = Surah::find($surahId);
        if ($surah) {
            $name = $surah->name_arabic ?? $surah->name_ar ?? $surah->arabic_name
                ?? $surah->surah_name ?? $surah->name ?? $surah->title ?? null;
            if ($name) return $name;
        }
        return self::$surahNames[$surahId] ?? "سورة رقم {$surahId}";
    }

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
                Section::make('بيانات الخطة')
                    ->schema([
                        TextEntry::make('student.user.name')
                            ->label('الطالب'),

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
                                'weekly' => 'أسبوعية',
                                'monthly' => 'شهرية',
                                'yearly' => 'سنوية',
                                default => $state,
                            }),

                        TextEntry::make('title')
                            ->label('العنوان'),

                        TextEntry::make('description')
                            ->label('الوصف')
                            ->default('-')
                            ->columnSpanFull(),

                        TextEntry::make('start_date')
                            ->label('تاريخ البداية')
                            ->date('Y/m/d'),

                        TextEntry::make('end_date')
                            ->label('تاريخ النهاية')
                            ->date('Y/m/d'),
                    ])
                    ->columns(2),

                Section::make('نطاق الحفظ')
                    ->schema([
                        // ✅ ملخص النطاق
                        TextEntry::make('range_summary')
                            ->label('النطاق')
                            ->state(function ($record) {
                                $fromName = $this->getSurahName($record->from_surah_id);
                                $toName = $this->getSurahName($record->to_surah_id);

                                if ($record->from_surah_id == $record->to_surah_id) {
                                    return "سورة {$fromName} (من آية {$record->from_ayah} إلى آية {$record->to_ayah})";
                                }

                                return "من سورة {$fromName} (آية {$record->from_ayah}) إلى سورة {$toName} (آية {$record->to_ayah})";
                            })
                            ->icon('heroicon-o-book-open')
                            ->columnSpanFull(),

                        // ✅ من سورة
                        TextEntry::make('from_surah_display')
                            ->label('من سورة')
                            ->state(fn ($record) => $this->getSurahName($record->from_surah_id))
                            ->icon('heroicon-o-arrow-right-circle')
                            ->color('success'),

                        // ✅ إلى سورة
                        TextEntry::make('to_surah_display')
                            ->label('إلى سورة')
                            ->state(fn ($record) => $this->getSurahName($record->to_surah_id))
                            ->icon('heroicon-o-arrow-left-circle')
                            ->color('danger'),

                        // ✅ من آية
                        TextEntry::make('from_ayah')
                            ->label('من آية')
                            ->icon('heroicon-o-hashtag')
                            ->badge()
                            ->color('success'),

                        // ✅ إلى آية
                        TextEntry::make('to_ayah')
                            ->label('إلى آية')
                            ->icon('heroicon-o-hashtag')
                            ->badge()
                            ->color('danger'),

                        // ✅ من صفحة
                        TextEntry::make('from_page')
                            ->label('من صفحة')
                            ->icon('heroicon-o-document')
                            ->default('غير محدد')
                            ->placeholder('غير محدد'),

                        // ✅ إلى صفحة
                        TextEntry::make('to_page')
                            ->label('إلى صفحة')
                            ->icon('heroicon-o-document')
                            ->default('غير محدد')
                            ->placeholder('غير محدد'),

                        // ✅ عدد الصفحات
                        TextEntry::make('pages_count')
                            ->label('عدد الصفحات')
                            ->state(function ($record) {
                                if ($record->from_page && $record->to_page) {
                                    $count = (int) $record->to_page - (int) $record->from_page + 1;
                                    return "{$count} صفحة";
                                }
                                return 'غير محدد';
                            })
                            ->icon('heroicon-o-calculator')
                            ->badge()
                            ->color('info'),
                    ])
                    ->columns(3),

                Section::make('الحالة والمتابعة')
                    ->schema([
                        TextEntry::make('status')
                            ->label('الحالة')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                'pending' => 'warning',
                                'in_progress' => 'info',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'pending' => 'قيد الانتظار',
                                'in_progress' => 'قيد التنفيذ',
                                'completed' => 'مكتملة',
                                'cancelled' => 'ملغاة',
                                default => $state,
                            }),

                        TextEntry::make('progress_percentage')
                            ->label('نسبة التقدم')
                            ->suffix('%')
                            ->badge()
                            ->color(fn ($state) => match (true) {
                                $state >= 100 => 'success',
                                $state >= 50 => 'info',
                                $state > 0 => 'warning',
                                default => 'gray',
                            }),

                        TextEntry::make('notes')
                            ->label('ملاحظات')
                            ->default('لا توجد ملاحظات')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

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