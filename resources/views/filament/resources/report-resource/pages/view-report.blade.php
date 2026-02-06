<x-filament-panels::page>
    @php
        $teacher = $this->record;
        $students = $this->getStudents();
        $weeklyStats = $this->getWeeklyStats();
        $monthlyStats = $this->getMonthlyStats();
    @endphp

    <div class="space-y-6">
        {{-- معلومات المحفظ --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-academic-cap class="w-5 h-5 text-primary-500" />
                    <span>معلومات المحفظ</span>
                </div>
            </x-slot>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-500">الاسم</div>
                    <div class="font-bold">{{ $teacher->user->name }}</div>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-500">التخصص</div>
                    <div class="font-medium">
                        @switch($teacher->specialty)
                            @case('hifz') تحفيظ @break
                            @case('tajweed') تجويد @break
                            @case('qiraat') قراءات @break
                            @case('hifz_tajweed') تحفيظ وتجويد @break
                            @default -
                        @endswitch
                    </div>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-500">عدد الطلاب</div>
                    <div class="font-bold text-xl text-primary-600">{{ $students->count() }}</div>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-500">الطلاب النشطين</div>
                    <div class="font-bold text-xl text-success-600">{{ $students->where('status', 'active')->count() }}</div>
                </div>
            </div>
        </x-filament::section>

        {{-- إحصائيات الأسبوع --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-calendar class="w-5 h-5 text-info-500" />
                    <span>إحصائيات الأسبوع الحالي</span>
                </div>
            </x-slot>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="p-4 bg-primary-50 dark:bg-primary-900/20 rounded-lg text-center">
                    <div class="text-3xl font-bold text-primary-600">{{ $weeklyStats['total_sessions'] }}</div>
                    <div class="text-sm text-gray-500">إجمالي الجلسات</div>
                </div>
                <div class="p-4 bg-success-50 dark:bg-success-900/20 rounded-lg text-center">
                    <div class="text-3xl font-bold text-success-600">{{ $weeklyStats['hifz_sessions'] }}</div>
                    <div class="text-sm text-gray-500">جلسات حفظ</div>
                </div>
                <div class="p-4 bg-info-50 dark:bg-info-900/20 rounded-lg text-center">
                    <div class="text-3xl font-bold text-info-600">{{ $weeklyStats['revision_sessions'] }}</div>
                    <div class="text-sm text-gray-500">جلسات مراجعة</div>
                </div>
                <div class="p-4 bg-warning-50 dark:bg-warning-900/20 rounded-lg text-center">
                    <div class="text-3xl font-bold text-warning-600">{{ $weeklyStats['total_ayahs'] }}</div>
                    <div class="text-sm text-gray-500">آيات محفوظة</div>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg text-center">
                    <div class="text-3xl font-bold text-gray-600">{{ $weeklyStats['students_with_records'] }}</div>
                    <div class="text-sm text-gray-500">طلاب نشطين</div>
                </div>
            </div>
        </x-filament::section>

        {{-- جدول الطلاب --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-user-group class="w-5 h-5 text-success-500" />
                    <span>طلاب الحلقة</span>
                </div>
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="p-3 text-right">الطالب</th>
                            <th class="p-3 text-right">المستوى</th>
                            <th class="p-3 text-right">الحالة</th>
                            <th class="p-3 text-right">الخطة النشطة</th>
                            <th class="p-3 text-right">إنجاز الأسبوع</th>
                            <th class="p-3 text-right">آخر تسميع</th>
                            <th class="p-3 text-right">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                            @php
                                $activePlan = $student->memorizationPlans->whereIn('status', ['pending', 'in_progress'])->first();
                                $weeklyAyahs = $student->memorizationRecords
                                    ->where('session_date', '>=', now()->startOfWeek())
                                    ->where('session_type', 'hifz')
                                    ->sum('ayahs_count');
                                $lastRecord = $student->memorizationRecords->sortByDesc('session_date')->first();
                            @endphp
                            <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="p-3 font-medium">{{ $student->user->name }}</td>
                                <td class="p-3">
                                    <x-filament::badge color="info">
                                        @switch($student->current_level)
                                            @case('beginner') مبتدئ @break
                                            @case('intermediate') متوسط @break
                                            @case('advanced') متقدم @break
                                            @case('memorizer') حافظ @break
                                            @default -
                                        @endswitch
                                    </x-filament::badge>
                                </td>
                                <td class="p-3">
                                    <x-filament::badge :color="$student->status === 'active' ? 'success' : 'danger'">
                                        {{ $student->status === 'active' ? 'نشط' : 'غير نشط' }}
                                    </x-filament::badge>
                                </td>
                                <td class="p-3">
                                    @if($activePlan)
                                        <x-filament::badge color="warning">
                                            {{ $activePlan->progress_percentage ?? 0 }}%
                                        </x-filament::badge>
                                    @else
                                        <span class="text-gray-400">لا توجد</span>
                                    @endif
                                </td>
                                <td class="p-3">
                                    <x-filament::badge :color="$weeklyAyahs > 0 ? 'success' : 'gray'">
                                        {{ $weeklyAyahs }} آية
                                    </x-filament::badge>
                                </td>
                                <td class="p-3 text-gray-500">
                                    {{ $lastRecord?->session_date?->format('Y/m/d') ?? 'لا يوجد' }}
                                </td>
                                <td class="p-3">
                                    <x-filament::button 
                                        size="sm" 
                                        color="info"
                                        href="{{ route('filament.admin.resources.students.view', $student) }}"
                                        tag="a"
                                    >
                                        عرض
                                    </x-filament::button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-gray-500">
                                    لا يوجد طلاب في هذه الحلقة
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>