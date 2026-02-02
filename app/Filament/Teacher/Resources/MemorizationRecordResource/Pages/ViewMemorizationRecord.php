<?php

namespace App\Filament\Teacher\Resources\MemorizationRecordResource\Pages;

use App\Filament\Teacher\Resources\MemorizationRecordResource;
use App\Services\QuranDataService;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewMemorizationRecord extends ViewRecord
{
    protected static string $resource = MemorizationRecordResource::class;

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
                Section::make('بيانات الجلسة')
                    ->schema([
                        TextEntry::make('student.user.name')
                            ->label('الطالب'),

                        TextEntry::make('session_date')
                            ->label('التاريخ')
                            ->date('Y/m/d'),

                        TextEntry::make('session_type')
                            ->label('نوع الجلسة')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'hifz' => 'حفظ جديد',
                                'revision' => 'مراجعة',
                                'test' => 'اختبار',
                                default => $state,
                            })
                            ->color(fn ($state) => match ($state) {
                                'hifz' => 'success',
                                'revision' => 'info',
                                'test' => 'warning',
                                default => 'gray',
                            }),

                        TextEntry::make('memorizationPlan.title')
                            ->label('الخطة')
                            ->default('بدون خطة'),

                        TextEntry::make('session_time')
                            ->label('الوقت')
                            ->time('H:i')
                            ->default('-'),

                        TextEntry::make('duration_minutes')
                            ->label('المدة')
                            ->suffix(' دقيقة')
                            ->default('-'),
                    ])
                    ->columns(3),

                Section::make('نطاق الحفظ')
                    ->schema([
                        TextEntry::make('surah_name')
                            ->label('السورة')
                            ->state(fn ($record) => QuranDataService::getSurahName($record->surah_id)),

                        TextEntry::make('from_ayah')
                            ->label('من آية')
                            ->badge()
                            ->color('success'),

                        TextEntry::make('to_ayah')
                            ->label('إلى آية')
                            ->badge()
                            ->color('danger'),

                        TextEntry::make('ayahs_count')
                            ->label('عدد الآيات')
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('from_page')
                            ->label('من صفحة')
                            ->default('-'),

                        TextEntry::make('to_page')
                            ->label('إلى صفحة')
                            ->default('-'),
                    ])
                    ->columns(3),

                Section::make('التقييم')
                    ->schema([
                        TextEntry::make('evaluation')
                            ->label('التقييم')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'excellent' => 'ممتاز',
                                'very_good' => 'جيد جداً',
                                'good' => 'جيد',
                                'acceptable' => 'مقبول',
                                'needs_review' => 'يحتاج مراجعة',
                                default => '-',
                            })
                            ->color(fn ($state) => match ($state) {
                                'excellent' => 'success',
                                'very_good' => 'info',
                                'good' => 'primary',
                                'acceptable' => 'warning',
                                'needs_review' => 'danger',
                                default => 'gray',
                            }),

                        TextEntry::make('mistakes_count')
                            ->label('عدد الأخطاء')
                            ->badge()
                            ->color(fn ($state) => match (true) {
                                $state > 5 => 'danger',
                                $state > 2 => 'warning',
                                default => 'success',
                            }),

                        TextEntry::make('status')
                            ->label('الحالة')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'completed' => 'مكتملة',
                                'incomplete' => 'غير مكتملة',
                                'absent' => 'غائب',
                                'excused' => 'غياب بعذر',
                                default => $state,
                            })
                            ->color(fn ($state) => match ($state) {
                                'completed' => 'success',
                                'incomplete' => 'warning',
                                'absent' => 'danger',
                                'excused' => 'info',
                                default => 'gray',
                            }),

                        TextEntry::make('teacher_notes')
                            ->label('ملاحظات المعلم')
                            ->default('لا توجد ملاحظات')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make('معلومات إضافية')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->dateTime('Y/m/d H:i'),

                        TextEntry::make('updated_at')
                            ->label('آخر تحديث')
                            ->dateTime('Y/m/d H:i'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}