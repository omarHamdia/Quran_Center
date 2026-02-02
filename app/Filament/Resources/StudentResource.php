<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'الطلاب';
    protected static ?string $modelLabel = 'طالب';
    protected static ?string $pluralModelLabel = 'الطلاب';
    protected static ?string $navigationGroup = 'إدارة المستخدمين';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('البيانات الأساسية')
                    ->schema([
                        Forms\Components\TextInput::make('user.name')
                            ->label('اسم الطالب')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('user.phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->required()
                            ->maxLength(10),

                        Forms\Components\TextInput::make('user.password')
                            ->label('كلمة المرور')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->minLength(6)
                            ->helperText(fn (string $operation): string => 
                                $operation === 'edit' ? 'اتركه فارغاً إذا لم ترد التغيير' : ''
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

                Forms\Components\Section::make('بيانات الدراسة')
                    ->schema([
                        Forms\Components\Select::make('teacher_id')
                            ->label('المحفظ')
                            ->relationship('teacher', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->user->name)
                            ->searchable()
                            ->preload()
                            ->placeholder('اختر المحفظ'),

                        Forms\Components\Select::make('current_level')
                            ->label('المستوى الحالي')
                            ->options([
                                'beginner' => 'مبتدئ',
                                'elementary' => 'أساسي',
                                'intermediate' => 'متوسط',
                                'advanced' => 'متقدم',
                                'memorizer' => 'حافظ',
                            ])
                            ->default('beginner')
                            ->required(),

                        Forms\Components\TextInput::make('memorized_juz')
                            ->label('الأجزاء المحفوظة')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(30),

                        Forms\Components\DatePicker::make('enrollment_date')
                            ->label('تاريخ الالتحاق')
                            ->default(now()),

                        Forms\Components\Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'active' => 'نشط',
                                'inactive' => 'غير نشط',
                                'graduated' => 'متخرج',
                                'withdrawn' => 'منسحب',
                            ])
                            ->default('active')
                            ->required(),

                    ])->columns(2),

                Forms\Components\Section::make('بيانات ولي الأمر')
                    ->schema([
                        Forms\Components\TextInput::make('guardian_name')
                            ->label('اسم ولي الأمر')
                            ->maxLength(100),

                        Forms\Components\TextInput::make('guardian_phone')
                            ->label('هاتف ولي الأمر')
                            ->tel()
                            ->maxLength(20),

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

                Tables\Columns\TextColumn::make('teacher.user.name')
                    ->label('المحفظ')
                    ->placeholder('غير معين')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('current_level')
                    ->label('المستوى')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'beginner' => 'مبتدئ',
                        'elementary' => 'أساسي',
                        'intermediate' => 'متوسط',
                        'advanced' => 'متقدم',
                        'memorizer' => 'حافظ',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match($state) {
                        'beginner' => 'gray',
                        'elementary' => 'info',
                        'intermediate' => 'warning',
                        'advanced' => 'success',
                        'memorizer' => 'primary',
                        default => 'gray',
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('memorized_juz')
                    ->label('الأجزاء')
                    ->suffix(' جزء')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'active' => 'نشط',
                        'inactive' => 'غير نشط',
                        'graduated' => 'متخرج',
                        'withdrawn' => 'منسحب',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'graduated' => 'primary',
                        'withdrawn' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('guardian_name')
                    ->label('ولي الأمر')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('guardian_phone')
                    ->label('هاتف ولي الأمر')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('enrollment_date')
                    ->label('تاريخ الالتحاق')
                    ->date('Y/m/d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('user.is_active')
                    ->label('نشط')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y/m/d H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('teacher_id')
                    ->label('المحفظ')
                    ->relationship('teacher', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->user->name)
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('current_level')
                    ->label('المستوى')
                    ->options([
                        'beginner' => 'مبتدئ',
                        'elementary' => 'أساسي',
                        'intermediate' => 'متوسط',
                        'advanced' => 'متقدم',
                        'memorizer' => 'حافظ',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'active' => 'نشط',
                        'inactive' => 'غير نشط',
                        'graduated' => 'متخرج',
                        'withdrawn' => 'منسحب',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('عرض'),
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()->label('حذف المحدد'),
            ])
            ->emptyStateHeading('لا يوجد طلاب')
            ->emptyStateIcon('heroicon-o-user-group');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('البيانات الأساسية')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')->label('الاسم'),
                        Infolists\Components\TextEntry::make('user.phone')->label('الهاتف')->copyable(),
                        Infolists\Components\TextEntry::make('user.gender')
                            ->label('الجنس')
                            ->formatStateUsing(fn ($state) => $state === 'male' ? 'ذكر' : 'أنثى'),
                        Infolists\Components\TextEntry::make('user.date_of_birth')
                            ->label('تاريخ الميلاد')
                            ->date('Y/m/d'),
                        Infolists\Components\IconEntry::make('user.is_active')->label('نشط')->boolean(),
                    ])->columns(3),

                Infolists\Components\Section::make('بيانات الدراسة')
                    ->schema([
                        Infolists\Components\TextEntry::make('teacher.user.name')
                            ->label('المحفظ')
                            ->default('غير معين'),
                        Infolists\Components\TextEntry::make('current_level')
                            ->label('المستوى')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match($state) {
                                'beginner' => 'مبتدئ',
                                'elementary' => 'أساسي',
                                'intermediate' => 'متوسط',
                                'advanced' => 'متقدم',
                                'memorizer' => 'حافظ',
                                default => $state,
                            }),
                        Infolists\Components\TextEntry::make('memorized_juz')->label('الأجزاء المحفوظة'),
                        Infolists\Components\TextEntry::make('status')
                            ->label('الحالة')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match($state) {
                                'active' => 'نشط',
                                'inactive' => 'غير نشط',
                                'graduated' => 'متخرج',
                                'withdrawn' => 'منسحب',
                                default => $state,
                            }),
                        Infolists\Components\TextEntry::make('enrollment_date')
                            ->label('تاريخ الالتحاق')
                            ->date('Y/m/d'),
                    ])->columns(3),

                Infolists\Components\Section::make('بيانات ولي الأمر')
                    ->schema([
                        Infolists\Components\TextEntry::make('guardian_name')->label('الاسم')->default('-'),
                        Infolists\Components\TextEntry::make('guardian_phone')->label('الهاتف')->default('-'),
                    ])->columns(2),

                Infolists\Components\Section::make('ملاحظات')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')->label('ملاحظات')->default('لا توجد'),
                    ])->collapsed(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'view' => Pages\ViewStudent::route('/{record}'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}