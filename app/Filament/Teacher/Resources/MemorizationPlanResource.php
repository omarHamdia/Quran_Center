<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\MemorizationPlanResource\Pages;
use App\Models\MemorizationPlan;
use App\Models\Student;
use App\Models\Surah;
use App\Models\Teacher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MemorizationPlanResource extends Resource
{
    protected static ?string $model = MemorizationPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'الخطط';
    protected static ?string $navigationGroup = 'الخطط';
    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->check() && ((auth()->user()->role->value ?? auth()->user()->role) === 'teacher');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $teacherId = Teacher::where('user_id', auth()->id())->value('id');
        return $teacherId
            ? $query->where('teacher_id', $teacherId)
            : $query->whereRaw('0=1');
    }

    // ✅ أسماء السور الثابتة
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

    private static function getSurahName($surah): string
    {
        $name = $surah->name_arabic ?? $surah->name_ar ?? $surah->arabic_name
            ?? $surah->surah_name ?? $surah->name ?? $surah->title ?? null;

        if ($name) return $name;

        $number = $surah->number ?? $surah->surah_number ?? $surah->id;
        return self::$surahNames[$number] ?? "سورة رقم {$number}";
    }

    private static function getSurahAyahCount($surah): int
    {
        return (int) ($surah->ayah_count ?? $surah->verses_count ?? $surah->ayahs
            ?? $surah->total_ayahs ?? $surah->ayat ?? $surah->verses ?? 0);
    }

    private static function getSurahPageStart($surah): int
    {
        return (int) ($surah->page_start ?? $surah->start_page ?? $surah->page ?? 0);
    }

    private static function getSurahPageEnd($surah): int
    {
        return (int) ($surah->page_end ?? $surah->end_page ?? 0);
    }

    private static function getSurahOptions(): array
    {
        return Surah::query()
            ->orderBy('id')
            ->get()
            ->mapWithKeys(function ($surah) {
                $number = $surah->number ?? $surah->surah_number ?? $surah->id;
                $name = self::getSurahName($surah);
                return [$surah->id => "{$number} - {$name}"];
            })
            ->toArray();
    }

    public static function form(Form $form): Form
    {
        $teacherId = Teacher::where('user_id', auth()->id())->value('id');

        return $form->schema([
            Forms\Components\Section::make('بيانات الخطة')
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
                            ->with('user')
                            ->get()
                            ->mapWithKeys(fn ($s) => [$s->id => $s->user->name])
                            ->toArray()
                        ),

                    Forms\Components\Select::make('plan_type')
                        ->label('نوع الخطة')
                        ->required()
                        ->options([
                            'weekly' => 'أسبوعية',
                            'monthly' => 'شهرية',
                            'yearly' => 'سنوية',
                        ]),

                    Forms\Components\TextInput::make('title')
                        ->label('العنوان')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Textarea::make('description')
                        ->label('الوصف')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\DatePicker::make('start_date')
                        ->label('تاريخ البداية')
                        ->required(),

                    Forms\Components\DatePicker::make('end_date')
                        ->label('تاريخ النهاية')
                        ->required(),
                ])
                ->columns(2),

            // ✅ قسم نطاق الحفظ المحسّن
            Forms\Components\Section::make('نطاق الحفظ')
                ->description('حدد نطاق الحفظ من سورة وآية إلى سورة وآية أخرى')
                ->schema([

                    // ═══════════════════════════════════════
                    // 🟢 البداية: من سورة - من آية
                    // ═══════════════════════════════════════
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Select::make('from_surah_id')
                                ->label('من سورة')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->options(fn () => self::getSurahOptions())
                                ->live()
                                ->afterStateUpdated(function (Set $set, $state) {
                                    $set('from_ayah', 1);
                                    $set('to_surah_id', $state);
                                    $set('to_ayah', null);
                                    $set('from_page', null);
                                    $set('to_page', null);
                                })
                                ->helperText(function (Get $get) {
                                    $surah = Surah::find($get('from_surah_id'));
                                    if (!$surah) return null;
                                    $count = self::getSurahAyahCount($surah);
                                    $pageStart = self::getSurahPageStart($surah);
                                    $pageEnd = self::getSurahPageEnd($surah);
                                    return "عدد الآيات: {$count} | الصفحات: {$pageStart} - {$pageEnd}";
                                }),

                            Forms\Components\TextInput::make('from_ayah')
                                ->label('من آية')
                                ->required()
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->maxValue(function (Get $get) {
                                    $surah = Surah::find($get('from_surah_id'));
                                    return $surah ? self::getSurahAyahCount($surah) : 999;
                                })
                                ->disabled(fn (Get $get) => blank($get('from_surah_id')))
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                    $surah = Surah::find($get('from_surah_id'));
                                    if (!$surah) return;

                                    $maxAyah = self::getSurahAyahCount($surah);
                                    if ($state && (int) $state > $maxAyah) {
                                        $set('from_ayah', $maxAyah);
                                    }
                                    if ($state && (int) $state < 1) {
                                        $set('from_ayah', 1);
                                    }
                                })
                                ->helperText(function (Get $get) {
                                    $surah = Surah::find($get('from_surah_id'));
                                    if (!$surah) return null;
                                    return "الحد الأقصى: " . self::getSurahAyahCount($surah);
                                }),
                        ]),

                    // ═══════════════════════════════════════
                    // 🔴 النهاية: إلى سورة - إلى آية
                    // ═══════════════════════════════════════
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Select::make('to_surah_id')
                                ->label('إلى سورة')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->options(function (Get $get) {
                                    $fromSurahId = $get('from_surah_id');
                                    if (!$fromSurahId) return self::getSurahOptions();

                                    // فقط السور من السورة المحددة وما بعدها
                                    return Surah::query()
                                        ->where('id', '>=', $fromSurahId)
                                        ->orderBy('id')
                                        ->get()
                                        ->mapWithKeys(function ($surah) {
                                            $number = $surah->number ?? $surah->surah_number ?? $surah->id;
                                            $name = self::getSurahName($surah);
                                            return [$surah->id => "{$number} - {$name}"];
                                        })
                                        ->toArray();
                                })
                                ->disabled(fn (Get $get) => blank($get('from_surah_id')))
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                    $fromSurahId = $get('from_surah_id');
                                    $toSurah = Surah::find($state);

                                    if ($toSurah) {
                                        // إذا نفس السورة، تأكد أن to_ayah >= from_ayah
                                        if ($state == $fromSurahId) {
                                            $fromAyah = (int) $get('from_ayah');
                                            $set('to_ayah', max($fromAyah, 1));
                                        } else {
                                            // سورة مختلفة، ابدأ من الآية 1 أو آخر آية
                                            $set('to_ayah', self::getSurahAyahCount($toSurah));
                                        }
                                    }
                                    $set('from_page', null);
                                    $set('to_page', null);
                                })
                                ->helperText(function (Get $get) {
                                    $surah = Surah::find($get('to_surah_id'));
                                    if (!$surah) return null;
                                    $count = self::getSurahAyahCount($surah);
                                    $pageStart = self::getSurahPageStart($surah);
                                    $pageEnd = self::getSurahPageEnd($surah);
                                    return "عدد الآيات: {$count} | الصفحات: {$pageStart} - {$pageEnd}";
                                }),

                            Forms\Components\TextInput::make('to_ayah')
                                ->label('إلى آية')
                                ->required()
                                ->numeric()
                                ->minValue(function (Get $get) {
                                    // إذا نفس السورة، الحد الأدنى = from_ayah
                                    if ($get('from_surah_id') == $get('to_surah_id')) {
                                        return (int) ($get('from_ayah') ?? 1);
                                    }
                                    return 1;
                                })
                                ->maxValue(function (Get $get) {
                                    $surah = Surah::find($get('to_surah_id'));
                                    return $surah ? self::getSurahAyahCount($surah) : 999;
                                })
                                ->disabled(fn (Get $get) => blank($get('to_surah_id')))
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                    $toSurah = Surah::find($get('to_surah_id'));
                                    if (!$toSurah) return;

                                    $maxAyah = self::getSurahAyahCount($toSurah);
                                    $minAyah = 1;

                                    // إذا نفس السورة
                                    if ($get('from_surah_id') == $get('to_surah_id')) {
                                        $minAyah = (int) ($get('from_ayah') ?? 1);
                                    }

                                    if ($state && (int) $state > $maxAyah) {
                                        $set('to_ayah', $maxAyah);
                                    }
                                    if ($state && (int) $state < $minAyah) {
                                        $set('to_ayah', $minAyah);
                                    }
                                })
                                ->helperText(function (Get $get) {
                                    $surah = Surah::find($get('to_surah_id'));
                                    if (!$surah) return null;
                                    $max = self::getSurahAyahCount($surah);
                                    if ($get('from_surah_id') == $get('to_surah_id')) {
                                        $min = $get('from_ayah') ?? 1;
                                        return "من {$min} إلى {$max}";
                                    }
                                    return "الحد الأقصى: {$max}";
                                }),
                        ]),

                    // ═══════════════════════════════════════
                    // 📄 الصفحات (محسوبة تلقائياً)
                    // ═══════════════════════════════════════
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Select::make('from_page')
                                ->label('من صفحة')
                                ->required()
                                ->searchable()
                                ->options(function (Get $get) {
                                    $fromSurah = Surah::find($get('from_surah_id'));
                                    $toSurah = Surah::find($get('to_surah_id'));

                                    if (!$fromSurah) return [];

                                    // الصفحة الأولى من السورة الأولى
                                    $start = self::getSurahPageStart($fromSurah);
                                    // الصفحة الأخيرة من السورة الأخيرة
                                    $end = $toSurah ? self::getSurahPageEnd($toSurah) : self::getSurahPageEnd($fromSurah);

                                    if ($start <= 0 || $end <= 0 || $end < $start) return [];

                                    return array_combine(range($start, $end), range($start, $end));
                                })
                                ->disabled(fn (Get $get) => blank($get('from_surah_id')) || blank($get('to_surah_id')))
                                ->live()
                                ->afterStateUpdated(fn (Set $set) => $set('to_page', null)),

                            Forms\Components\Select::make('to_page')
                                ->label('إلى صفحة')
                                ->required()
                                ->searchable()
                                ->options(function (Get $get) {
                                    $fromPage = (int) ($get('from_page') ?? 0);
                                    $toSurah = Surah::find($get('to_surah_id'));

                                    if (!$toSurah || $fromPage <= 0) return [];

                                    $end = self::getSurahPageEnd($toSurah);
                                    if ($end <= 0 || $end < $fromPage) return [];

                                    return array_combine(range($fromPage, $end), range($fromPage, $end));
                                })
                                ->disabled(fn (Get $get) => blank($get('from_page'))),
                        ]),

                    // ═══════════════════════════════════════
                    // 📊 ملخص النطاق
                    // ═══════════════════════════════════════
                    Forms\Components\Placeholder::make('range_summary')
                        ->label('ملخص النطاق')
                        ->content(function (Get $get) {
                            $fromSurah = Surah::find($get('from_surah_id'));
                            $toSurah = Surah::find($get('to_surah_id'));
                            $fromAyah = $get('from_ayah');
                            $toAyah = $get('to_ayah');
                            $fromPage = $get('from_page');
                            $toPage = $get('to_page');

                            if (!$fromSurah || !$toSurah || !$fromAyah || !$toAyah) {
                                return 'حدد نطاق الحفظ لعرض الملخص';
                            }

                            $fromName = self::getSurahName($fromSurah);
                            $toName = self::getSurahName($toSurah);

                            $summary = "📖 من سورة {$fromName} (آية {$fromAyah}) إلى سورة {$toName} (آية {$toAyah})";

                            if ($fromPage && $toPage) {
                                $pageCount = (int) $toPage - (int) $fromPage + 1;
                                $summary .= "\n📄 الصفحات: من {$fromPage} إلى {$toPage} ({$pageCount} صفحة)";
                            }

                            return $summary;
                        })
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('الحالة والمتابعة')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label('الحالة')
                        ->required()
                        ->options([
                            'pending' => 'قيد الانتظار',
                            'in_progress' => 'قيد التنفيذ',
                            'completed' => 'مكتملة',
                            'cancelled' => 'ملغاة',
                        ])
                        ->default('pending'),

                    Forms\Components\TextInput::make('progress_percentage')
                        ->label('نسبة التقدم %')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->default(0),

                    Forms\Components\Textarea::make('notes')
                        ->label('ملاحظات')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.user.name')
                    ->label('الطالب')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('plan_type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'weekly' => 'أسبوعية',
                        'monthly' => 'شهرية',
                        'yearly' => 'سنوية',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->limit(30),

                // ✅ عمود نطاق الحفظ
                Tables\Columns\TextColumn::make('memorization_range')
                    ->label('نطاق الحفظ')
                    ->getStateUsing(function ($record) {
                        $fromSurah = Surah::find($record->from_surah_id);
                        $toSurah = Surah::find($record->to_surah_id);

                        if (!$fromSurah || !$toSurah) return '-';

                        $fromName = self::getSurahName($fromSurah);
                        $toName = self::getSurahName($toSurah);

                        if ($record->from_surah_id == $record->to_surah_id) {
                            return "{$fromName} ({$record->from_ayah}-{$record->to_ayah})";
                        }

                        return "{$fromName}:{$record->from_ayah} → {$toName}:{$record->to_ayah}";
                    })
                    ->wrap(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('البداية')
                    ->date('Y/m/d')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('النهاية')
                    ->date('Y/m/d'),

                Tables\Columns\TextColumn::make('status')
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

                Tables\Columns\TextColumn::make('progress_percentage')
                    ->label('التقدم')
                    ->suffix('%')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 100 => 'success',
                        $state >= 50 => 'info',
                        $state > 0 => 'warning',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('start_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('plan_type')
                    ->label('النوع')
                    ->options([
                        'weekly' => 'أسبوعية',
                        'monthly' => 'شهرية',
                        'yearly' => 'سنوية',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'in_progress' => 'قيد التنفيذ',
                        'completed' => 'مكتملة',
                        'cancelled' => 'ملغاة',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('عرض'),
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()->label('حذف المحدد'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMemorizationPlans::route('/'),
            'create' => Pages\CreateMemorizationPlan::route('/create'),
            'edit' => Pages\EditMemorizationPlan::route('/{record}/edit'),
            'view' => Pages\ViewMemorizationPlan::route('/{record}'),
        ];
    }
}