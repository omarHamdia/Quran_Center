<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\MemorizationRecordResource\Pages;
use App\Models\MemorizationPlan;
use App\Models\MemorizationRecord;
use App\Models\QuranAyah;
use App\Models\Student;
use App\Models\Surah;
use App\Models\Teacher;
use App\Services\MemorizationPlanProgressService;
use App\Services\QuranDataService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MemorizationRecordResource extends Resource
{
    protected static ?string $model = MemorizationRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'التسميع اليومي';
    protected static ?string $modelLabel = 'تسميع';
    protected static ?string $pluralModelLabel = 'التسميع اليومي';
    protected static ?string $navigationGroup = 'الحفظ والمراجعة';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->check() && ((auth()->user()->role->value ?? auth()->user()->role) === 'teacher');
    }

    public static function getEloquentQuery(): Builder
    {
        $teacherId = Teacher::where('user_id', auth()->id())->value('id');

        return parent::getEloquentQuery()
            ->where('teacher_id', $teacherId)
            ->with(['student.user', 'surah', 'memorizationPlan']);
    }

    public static function form(Form $form): Form
    {
        $teacherId = Teacher::where('user_id', auth()->id())->value('id');

        return $form->schema([
            // ═���═════════════════════════════════════
            // القسم الأول: بيانات الجلسة
            // ═══════════════════════════════════════
            Forms\Components\Section::make('بيانات الجلسة')
                ->schema([
                    Forms\Components\Hidden::make('teacher_id')
                        ->default($teacherId)
                        ->dehydrated(true),

                    Forms\Components\Select::make('student_id')
                        ->label('الطالب')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->options(fn () => Student::query()
                            ->where('teacher_id', $teacherId)
                            ->whereHas('user', fn ($q) => $q->where('is_active', true))
                            ->with('user')
                            ->get()
                            ->mapWithKeys(fn ($s) => [$s->id => $s->user->name])
                            ->toArray()
                        )
                        ->live()
                        ->afterStateUpdated(function (Set $set, $state) {
                            $activePlan = MemorizationPlan::where('student_id', $state)
                                ->whereIn('status', ['pending', 'in_progress'])
                                ->first();

                            $set('memorization_plan_id', $activePlan?->id);
                        }),

                    Forms\Components\DatePicker::make('session_date')
                        ->label('تاريخ الجلسة')
                        ->required()
                        ->default(now())
                        ->maxDate(now()),

                    Forms\Components\Select::make('session_type')
                        ->label('نوع الجلسة')
                        ->required()
                        ->options([
                            'hifz' => '📗 حفظ جديد',
                            'revision' => '📘 مراجعة',
                            'test' => '📝 اختبار',
                        ])
                        ->default('hifz')
                        ->live()
                        ->helperText(fn (Get $get) => match ($get('session_type')) {
                            'hifz' => '✅ هذا النوع يؤثر على تقدم الخطة',
                            'revision', 'test' => '⚠️ هذا النوع لا يؤثر على تقدم الخطة',
                            default => '',
                        }),

                    // Forms\Components\Select::make('memorization_plan_id')
                    //     ->label('خطة الحفظ')
                    //     ->searchable()
                    //     ->preload()
                    //     ->options(function (Get $get) {
                    //         $studentId = $get('student_id');
                    //         if (!$studentId) return [];

                    //         return MemorizationPlan::where('student_id', $studentId)
                    //             ->whereIn('status', ['pending', 'in_progress'])
                    //             ->get()
                    //             ->mapWithKeys(fn ($p) => [
                    //                 $p->id => "{$p->title} ({$p->progress_percentage}%)"
                    //             ])
                    //             ->toArray();
                    //     })
                    //     ->disabled(fn (Get $get) => blank($get('student_id')))
                    //     ->live()
                    //     ->helperText(function (Get $get) {
                    //         $planId = $get('memorization_plan_id');
                    //         if (!$planId) return 'اختر الطالب أولاً';

                    //         $plan = MemorizationPlan::find($planId);
                    //         if (!$plan) return null;

                    //         return "التقدم: {$plan->completed_ayahs}/{$plan->total_ayahs} آية ({$plan->progress_percentage}%)";
                    //     }),

                    Forms\Components\TimePicker::make('session_time')
                        ->label('وقت الجلسة')
                        ->seconds(false),

                    Forms\Components\TextInput::make('duration_minutes')
                        ->label('مدة الجلسة (دقيقة)')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(180)
                        ->suffix('دقيقة'),
                ])
                ->columns(3),

            // ═══════════════════════════════════════
            // القسم الثاني: نطاق الحفظ/المراجعة
            // ═══════════════════════════════════════
            Forms\Components\Section::make('نطاق الحفظ')
                ->description('حدد السورة والآيات التي تم تسميعها')
                ->schema([
                    // اختيار السورة من قاعدة البيانات
                    Forms\Components\Select::make('surah_id')
                        ->label('السورة')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->options(fn () => QuranDataService::getSurahOptions())
                        ->live()
                        ->afterStateUpdated(function (Set $set, $state) {
                            if (!$state) return;

                            $set('from_ayah', 1);
                            $set('to_ayah', null);

                            // جلب صفحة الآية الأولى
                            $page = QuranDataService::getAyahPage($state, 1);
                            $set('from_page', $page);
                            $set('to_page', null);
                        })
                        ->helperText(function (Get $get) {
                            $surahId = $get('surah_id');
                            if (!$surahId) return null;

                            $info = QuranDataService::getSurahInfo($surahId);
                            if (!$info) return null;

                            return "عدد الآيات: {$info['ayah_count']} | الصفحات: {$info['page_start']} - {$info['page_end']}";
                        }),

                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('from_ayah')
                                ->label('من آية')
                                ->required()
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->maxValue(function (Get $get) {
                                    $surahId = $get('surah_id');
                                    return $surahId ? QuranDataService::getSurahAyahCount($surahId) : 999;
                                })
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                    $surahId = $get('surah_id');
                                    if (!$surahId || !$state) return;

                                    $maxAyah = QuranDataService::getSurahAyahCount($surahId);
                                    if ((int) $state > $maxAyah) {
                                        $set('from_ayah', $maxAyah);
                                        $state = $maxAyah;
                                    }

                                    // تحديث الصفحة
                                    $page = QuranDataService::getAyahPage($surahId, (int) $state);
                                    if ($page) $set('from_page', $page);
                                })
                                ->helperText(function (Get $get) {
                                    $surahId = $get('surah_id');
                                    if (!$surahId) return null;
                                    $max = QuranDataService::getSurahAyahCount($surahId);
                                    return "الحد الأقصى: {$max}";
                                }),

                            Forms\Components\TextInput::make('to_ayah')
                                ->label('إلى آية')
                                ->required()
                                ->numeric()
                                ->minValue(function (Get $get) {
                                    return (int) ($get('from_ayah') ?? 1);
                                })
                                ->maxValue(function (Get $get) {
                                    $surahId = $get('surah_id');
                                    return $surahId ? QuranDataService::getSurahAyahCount($surahId) : 999;
                                })
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                    $surahId = $get('surah_id');
                                    $fromAyah = (int) ($get('from_ayah') ?? 1);
                                    if (!$surahId || !$state) return;

                                    $maxAyah = QuranDataService::getSurahAyahCount($surahId);

                                    if ((int) $state > $maxAyah) {
                                        $set('to_ayah', $maxAyah);
                                        $state = $maxAyah;
                                    }
                                    if ((int) $state < $fromAyah) {
                                        $set('to_ayah', $fromAyah);
                                        $state = $fromAyah;
                                    }

                                    // تحديث الصفحة
                                    $page = QuranDataService::getAyahPage($surahId, (int) $state);
                                    if ($page) $set('to_page', $page);
                                })
                                ->helperText(function (Get $get) {
                                    $from = $get('from_ayah');
                                    $surahId = $get('surah_id');
                                    if (!$surahId) return null;
                                    $max = QuranDataService::getSurahAyahCount($surahId);
                                    return $from ? "من {$from} إلى {$max}" : "الحد الأقصى: {$max}";
                                }),
                        ]),

                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('from_page')
                                ->label('من صفحة')
                                ->numeric()
                                ->disabled()
                                ->dehydrated(true),

                            Forms\Components\TextInput::make('to_page')
                                ->label('إلى صفحة')
                                ->numeric()
                                ->disabled()
                                ->dehydrated(true),
                        ]),

                    // عدد الآيات المحسوب
                    Forms\Components\Placeholder::make('ayahs_count_display')
                        ->label('عدد الآيات')
                        ->content(function (Get $get) {
                            $from = (int) ($get('from_ayah') ?? 0);
                            $to = (int) ($get('to_ayah') ?? 0);

                            if ($from && $to && $to >= $from) {
                                $count = $to - $from + 1;
                                return "📖 {$count} آية";
                            }
                            return '-';
                        }),
                ]),

            // ═══════════════════════════════════════
            // القسم الثالث: التقييم
            // ═══════════════════════════════════════
            Forms\Components\Section::make('التقييم')
                ->schema([
                    Forms\Components\Select::make('evaluation')
                        ->label('التقييم')
                        ->options([
                            'excellent' => '⭐ ممتاز',
                            'very_good' => '🌟 جيد جداً',
                            'good' => '👍 جيد',
                            'acceptable' => '👌 مقبول',
                            'needs_review' => '📚 يحتاج مراجعة',
                        ])
                        ->required(),

                    Forms\Components\TextInput::make('mistakes_count')
                        ->label('عدد الأخطاء')
                        ->numeric()
                        ->minValue(0)
                        ->default(0),

                    Forms\Components\Select::make('status')
                        ->label('حالة الجلسة')
                        ->options([
                            'completed' => '✅ مكتملة',
                            'incomplete' => '⏳ غير مكتملة',
                            'absent' => '❌ غائب',
                            'excused' => '📝 غياب بعذر',
                        ])
                        ->default('completed')
                        ->required(),

                    Forms\Components\Textarea::make('teacher_notes')
                        ->label('ملاحظات المعلم')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(3),

            // ═══════════════════════════════════════
            // القسم الرابع: معاينة التقدم
            // ═══════════════════════════════════════
            Forms\Components\Section::make('تقدم الخطة بعد الحفظ')
                ->schema([
                    Forms\Components\Placeholder::make('progress_preview')
                        ->label('')
                        ->content(function (Get $get) {
                            $sessionType = $get('session_type');
                            $planId = $get('memorization_plan_id');
                            $surahId = $get('surah_id');
                            $fromAyah = (int) ($get('from_ayah') ?? 0);
                            $toAyah = (int) ($get('to_ayah') ?? 0);

                            if ($sessionType !== 'hifz') {
                                return '⚠️ المراجعة والاختبار لا تؤثر على تقدم الخطة';
                            }

                            if (!$planId || !$surahId || !$fromAyah || !$toAyah) {
                                return 'أكمل البيانات لعرض التقدم المتوقع';
                            }

                            $plan = MemorizationPlan::find($planId);
                            if (!$plan) return 'لم يتم تحديد خطة';

                            $service = app(MemorizationPlanProgressService::class);
                            $expected = $service->calculateExpectedProgress($plan, $surahId, $fromAyah, $toAyah);

                            $progressBar = self::renderProgressBar($expected['expected_progress']);

                            return new \Illuminate\Support\HtmlString("
                                <div class='space-y-2'>
                                    <div class='flex justify-between text-sm'>
                                        <span>الآيات المحفوظة حالياً:</span>
                                        <span class='font-bold'>{$expected['current_completed']}</span>
                                    </div>
                                    <div class='flex justify-between text-sm'>
                                        <span>الآيات الجديدة:</span>
                                        <span class='font-bold text-green-600'>+{$expected['unique_new_ayahs']}</span>
                                    </div>
                                    <div class='flex justify-between text-sm'>
                                        <span>المجموع بعد الحفظ:</span>
                                        <span class='font-bold'>{$expected['expected_completed']} / {$expected['total_ayahs']}</span>
                                    </div>
                                    <div class='mt-2'>
                                        {$progressBar}
                                    </div>
                                    <div class='text-center font-bold text-lg text-primary-600'>
                                        {$expected['expected_progress']}%
                                    </div>
                                </div>
                            ");
                        })
                        ->columnSpanFull(),
                ])
                ->visible(fn (Get $get) => $get('session_type') === 'hifz' && $get('memorization_plan_id')),
        ]);
    }

    /**
     * رسم شريط التقدم
     */
    private static function renderProgressBar(float $percent): string
    {
        $color = match (true) {
            $percent >= 100 => 'bg-green-500',
            $percent >= 75 => 'bg-blue-500',
            $percent >= 50 => 'bg-yellow-500',
            $percent >= 25 => 'bg-orange-500',
            default => 'bg-red-500',
        };

        return "
            <div class='w-full bg-gray-200 rounded-full h-4'>
                <div class='{$color} h-4 rounded-full transition-all duration-500' style='width: {$percent}%'></div>
            </div>
        ";
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('session_date')
                    ->label('التاريخ')
                    ->date('Y/m/d')
                    ->sortable(),

                Tables\Columns\TextColumn::make('student.user.name')
                    ->label('الطالب')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('session_type')
                    ->label('النوع')
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

                Tables\Columns\TextColumn::make('surah_display')
                    ->label('السورة والآيات')
                    ->getStateUsing(function ($record) {
                        $surahName = QuranDataService::getSurahName($record->surah_id);
                        return "{$surahName} ({$record->from_ayah}-{$record->to_ayah})";
                    }),

                Tables\Columns\TextColumn::make('ayahs_count')
                    ->label('عدد الآيات')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('evaluation')
                    ->label('التقييم')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'excellent' => 'ممتاز',
                        'very_good' => 'جيد جداً',
                        'good' => 'جيد',
                        'acceptable' => 'مقبول',
                        'needs_review' => 'يحتاج مراجعة',
                        default => $state ?? '-',
                    })
                    ->color(fn ($state) => match ($state) {
                        'excellent' => 'success',
                        'very_good' => 'info',
                        'good' => 'primary',
                        'acceptable' => 'warning',
                        'needs_review' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('mistakes_count')
                    ->label('الأخطاء')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state > 5 => 'danger',
                        $state > 2 => 'warning',
                        default => 'success',
                    }),

                Tables\Columns\TextColumn::make('status')
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
            ])
            ->defaultSort('session_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('student_id')
                    ->label('الطالب')
                    ->options(function () {
                        $teacherId = Teacher::where('user_id', auth()->id())->value('id');
                        return Student::where('teacher_id', $teacherId)
                            ->with('user')
                            ->get()
                            ->mapWithKeys(fn ($s) => [$s->id => $s->user->name])
                            ->toArray();
                    })
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('session_type')
                    ->label('نوع الجلسة')
                    ->options([
                        'hifz' => 'حفظ جديد',
                        'revision' => 'مراجعة',
                        'test' => 'اختبار',
                    ]),

                Tables\Filters\SelectFilter::make('evaluation')
                    ->label('التقييم')
                    ->options([
                        'excellent' => 'ممتاز',
                        'very_good' => 'جيد جداً',
                        'good' => 'جيد',
                        'acceptable' => 'مقبول',
                        'needs_review' => 'يحتاج مراجعة',
                    ]),

                Tables\Filters\Filter::make('today')
                    ->label('اليوم فقط')
                    ->query(fn (Builder $query) => $query->whereDate('session_date', today())),

                Tables\Filters\Filter::make('this_week')
                    ->label('هذا الأسبوع')
                    ->query(fn (Builder $query) => $query->whereBetween('session_date', [
                        now()->startOfWeek(),
                        now()->endOfWeek(),
                    ])),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('عرض'),
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()->label('حذف المحدد'),
            ])
            ->emptyStateHeading('لا توجد سجلات تسميع')
            ->emptyStateDescription('ابدأ بإضافة سجل تسميع جديد')
            ->emptyStateIcon('heroicon-o-clipboard-document-check');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMemorizationRecords::route('/'),
            'create' => Pages\CreateMemorizationRecord::route('/create'),
            'view' => Pages\ViewMemorizationRecord::route('/{record}'),
            'edit' => Pages\EditMemorizationRecord::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $teacherId = Teacher::where('user_id', auth()->id())->value('id');
        if (!$teacherId) return null;

        $todayCount = MemorizationRecord::where('teacher_id', $teacherId)
            ->whereDate('session_date', today())
            ->count();

        return $todayCount > 0 ? (string) $todayCount : null;
    }
}