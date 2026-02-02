<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherResource\Pages;
use App\Models\Teacher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TeacherResource extends Resource
{
    protected static ?string $model = Teacher::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'المحفظين';
    protected static ?string $modelLabel = 'محفظ';
    protected static ?string $pluralModelLabel = 'المحفظين';
    protected static ?string $navigationGroup = 'إدارة المستخدمين';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('البيانات الأساسية')
                    ->schema([
                        Forms\Components\TextInput::make('user.name')
                            ->label('الاسم الكامل')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('user.phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->required()
                            ->maxLength(10),

                        Forms\Components\TextInput::make('user.email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('user.password')
                            ->label('كلمة المرور')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->minLength(6)
                            ->helperText(fn (string $operation): string => 
                                $operation === 'edit' ? 'اتركه فار��اً إذا لم ترد تغييره' : ''
                            ),

                        Forms\Components\Select::make('user.gender')
                            ->label('الجنس')
                            ->options([
                                'male' => 'ذكر',
                                'female' => 'أنثى',
                            ])
                            ->required(),

                        Forms\Components\DatePicker::make('user.date_of_birth')
                            ->label('تاريخ الميلاد'),

                    ])->columns(2),

                Forms\Components\Section::make('البيانات المهنية')
                    ->schema([
                        Forms\Components\Select::make('specialty')
                            ->label('التخصص')
                            ->options([
                                'hifz' => 'تحفيظ القرآن',
                                'tajweed' => 'التجويد',
                                'qiraat' => 'القراءات',
                                'hifz_tajweed' => 'تحفيظ وتجويد',
                            ])
                            ->searchable(),

                        Forms\Components\TextInput::make('qualification')
                            ->label('المؤهل العلمي')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('ijazah_details')
                            ->label('الإجازات القرآنية')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\DatePicker::make('hire_date')
                            ->label('تاريخ التعيين')
                            ->default(now()),

                        Forms\Components\TextInput::make('max_students')
                            ->label('الحد الأقصى للطلاب')
                            ->numeric()
                            ->default(30)
                            ->minValue(1)
                            ->maxValue(100),

                    ])->columns(2),

                Forms\Components\Section::make('ملاحظات')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('user.is_active')
                            ->label('نشط')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.phone')
                    ->label('الهاتف')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('specialty')
                    ->label('التخصص')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match($state) {
                        'hifz' => 'تحفيظ',
                        'tajweed' => 'تجويد',
                        'qiraat' => 'قراءات',
                        'hifz_tajweed' => 'تحفيظ وتجويد',
                        default => '-',
                    })
                    ->color(fn (?string $state): string => match($state) {
                        'hifz' => 'success',
                        'tajweed' => 'info',
                        'qiraat' => 'warning',
                        'hifz_tajweed' => 'primary',
                        default => 'gray',
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('qualification')
                    ->label('المؤهل')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('students_count')
                    ->label('عدد الطلاب')
                    ->counts('students')
                    ->badge()
                    ->color('success')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('max_students')
                    ->label('الحد الأقصى')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('hire_date')
                    ->label('تاريخ التعيين')
                    ->date('Y/m/d')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('user.is_active')
                    ->label('نشط')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y/m/d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime('Y/m/d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('specialty')
                    ->label('التخصص')
                    ->options([
                        'hifz' => 'تحفيظ',
                        'tajweed' => 'تجويد',
                        'qiraat' => 'قراءات',
                        'hifz_tajweed' => 'تحفيظ وتجويد',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->queries(
                        true: fn ($query) => $query->whereHas('user', fn ($q) => $q->where('is_active', true)),
                        false: fn ($query) => $query->whereHas('user', fn ($q) => $q->where('is_active', false)),
                    )
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط')
                    ->placeholder('الكل'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('عرض'),
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ])
            ->emptyStateHeading('لا يوجد محفظين')
            ->emptyStateDescription('قم بإضافة محفظ جديد')
            ->emptyStateIcon('heroicon-o-academic-cap');
    }

    // صفحة العرض (Infolist)
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('البيانات الأساسية')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('الاسم'),
                        Infolists\Components\TextEntry::make('user.phone')
                            ->label('الهاتف')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('user.email')
                            ->label('البريد الإلكتروني')
                            ->default('-'),
                        Infolists\Components\TextEntry::make('user.gender')
                            ->label('الجنس')
                            ->formatStateUsing(fn ($state) => $state === 'male' ? 'ذكر' : 'أنثى'),
                        Infolists\Components\TextEntry::make('user.date_of_birth')
                            ->label('تاريخ الميلاد')
                            ->date('Y/m/d')
                            ->default('-'),
                        Infolists\Components\IconEntry::make('user.is_active')
                            ->label('نشط')
                            ->boolean(),
                    ])->columns(3),

                Infolists\Components\Section::make('البيانات المهنية')
                    ->schema([
                        Infolists\Components\TextEntry::make('specialty')
                            ->label('التخصص')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => match($state) {
                                'hifz' => 'تحفيظ',
                                'tajweed' => 'تجويد',
                                'qiraat' => 'قراءات',
                                'hifz_tajweed' => 'تحفيظ وتجويد',
                                default => '-',
                            }),
                        Infolists\Components\TextEntry::make('qualification')
                            ->label('المؤهل العلمي')
                            ->default('-'),
                        Infolists\Components\TextEntry::make('hire_date')
                            ->label('تاريخ التعيين')
                            ->date('Y/m/d')
                            ->default('-'),
                        Infolists\Components\TextEntry::make('students_count')
                            ->label('عدد الطلاب')
                            ->state(fn ($record) => $record->students()->count()),
                        Infolists\Components\TextEntry::make('max_students')
                            ->label('الحد الأقصى'),
                        Infolists\Components\TextEntry::make('ijazah_details')
                            ->label('الإجازات القرآنية')
                            ->default('-')
                            ->columnSpanFull(),
                    ])->columns(3),

                Infolists\Components\Section::make('ملاحظات')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->label('ملاحظات')
                            ->default('لا توجد ملاحظات')
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeachers::route('/'),
            'create' => Pages\CreateTeacher::route('/create'),
            'view' => Pages\ViewTeacher::route('/{record}'),
            'edit' => Pages\EditTeacher::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}