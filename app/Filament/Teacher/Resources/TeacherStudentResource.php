<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\TeacherStudentResource\Pages;
use App\Models\Student;
use App\Models\Teacher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TeacherStudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'طلابي';
    protected static ?string $modelLabel = 'طالب';
    protected static ?string $pluralModelLabel = 'طلابي';
    protected static ?string $navigationGroup = 'واجهة المحفظ';
    protected static ?int $navigationSort = 1;

    /**
     * ✅ إظهار المورد في لوحة المعلم فقط (UX)
     * (الأمان الحقيقي موجود أيضًا في Panel middleware role:teacher)
     */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check()
            && method_exists(auth()->user(), 'isTeacher')
            && auth()->user()->isTeacher();
    }

    /**
     * ✅ أمان إضافي: منع الوصول للمورد أصلًا لو ليس معلم
     */
    public static function canAccess(): bool
    {
        return auth()->check()
            && method_exists(auth()->user(), 'isTeacher')
            && auth()->user()->isTeacher();
    }

    /**
     * ✅ تقييد جدول الطلاب: يعرض فقط طلاب المعلم المسجّل
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (! auth()->check()) {
            return $query->whereRaw('0 = 1');
        }

        // نفترض أن Teacher مرتبط بـ user_id
        $teacherId = Teacher::where('user_id', auth()->id())->value('id');

        if (! $teacherId) {
            // لا يوجد Teacher مرتبط بهذا المستخدم -> لا تعرض شيئًا
            return $query->whereRaw('0 = 1');
        }

        return $query->where('teacher_id', $teacherId);
    }

    public static function form(Form $form): Form
    {
        $teacherId = auth()->check()
            ? Teacher::where('user_id', auth()->id())->value('id')
            : null;

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
                            ->maxLength(20),

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
                        /**
                         * ✅ لا نُظهر اختيار المحفظ
                         * ✅ نملأ teacher_id تلقائيًا من المعلم المسجل
                         * ✅ نثبّت القيمة (لا تتغير عند edit إلا لو أردت)
                         */
                        Forms\Components\Hidden::make('teacher_id')
                            ->default($teacherId)
                            ->dehydrated(true),

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
                            ->label('عدد الأجزاء المحفوظة')
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

                Forms\Components\Section::make('ولي الأمر')
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
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('الاسم')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.phone')->label('الهاتف')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('current_level')->label('المستوى')->badge(),
                Tables\Columns\TextColumn::make('memorized_juz')->label('الأجزاء')->suffix(' جزء'),
                Tables\Columns\TextColumn::make('enrollment_date')->label('تاريخ الالتحاق')->date(),
                Tables\Columns\IconColumn::make('user.is_active')->label('نشط')->boolean(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('current_level')->label('المستوى')->options([
                    'beginner' => 'مبتدئ',
                    'elementary' => 'أساسي',
                    'intermediate' => 'متوسط',
                    'advanced' => 'متقدم',
                    'memorizer' => 'حافظ',
                ]),
                Tables\Filters\SelectFilter::make('status')->label('الحالة')->options([
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
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeacherStudents::route('/'),
            'create' => Pages\CreateTeacherStudent::route('/create'),
            'edit' => Pages\EditTeacherStudent::route('/{record}/edit'),
            'view' => Pages\ViewTeacherStudent::route('/{record}'),
        ];
    }
}
