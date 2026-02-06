<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages;
use App\Models\Teacher;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReportResource extends Resource
{
    protected static ?string $model = Teacher::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'التقارير';
    protected static ?string $modelLabel = 'تقرير';
    protected static ?string $pluralModelLabel = 'التقارير';
    protected static ?string $navigationGroup = 'التقارير';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'reports';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('اسم المحفظ')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('students_count')
                    ->label('عدد الطلاب')
                    ->counts('students')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('active_students')
                    ->label('الطلاب النشطين')
                    ->getStateUsing(fn ($record) => $record->students()->where('status', 'active')->count())
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('weekly_sessions')
                    ->label('جلسات الأسبوع')
                    ->getStateUsing(function ($record) {
                        return $record->memorizationRecords()
                            ->where('session_date', '>=', now()->startOfWeek())
                            ->count();
                    })
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('weekly_ayahs')
                    ->label('آيات الأسبوع')
                    ->getStateUsing(function ($record) {
                        return $record->memorizationRecords()
                            ->where('session_date', '>=', now()->startOfWeek())
                            ->where('session_type', 'hifz')
                            ->sum('ayahs_count');
                    })
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('specialty')
                    ->label('التخصص')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'hifz' => 'تحفيظ',
                        'tajweed' => 'تجويد',
                        'qiraat' => 'قراءات',
                        'hifz_tajweed' => 'تحفيظ وتجويد',
                        default => '-',
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('view_report')
                    ->label('عرض التقرير')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn ($record) => static::getUrl('view', ['record' => $record])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
            'view' => Pages\ViewReport::route('/{record}'),
        ];
    }
}