<x-filament-panels::page>
    @php
        $teacher = $this->record;
        $students = $this->getStudents();
        $stats = $this->getWeeklyStats();
        $weeklyCompletions = $this->getWeeklyCompletions();
        $topStudents = $this->getTopStudents();
        $dailyPagesData = $this->getDailyPagesData();
        $monthlyData = $this->getMonthlyData();
        $todayRecords = $this->getTodayRecords();

        // ✅ بديل مهم عن "توزيع التقييمات": توزيع أنواع الجلسات (اليوم)
        $sessionTypeCounts = [
            'hifz'     => $todayRecords->where('session_type', 'hifz')->count(),
            'revision' => $todayRecords->where('session_type', 'revision')->count(),
            'test'     => $todayRecords->where('session_type', 'test')->count(),
            'other'    => $todayRecords->whereNotIn('session_type', ['hifz','revision','test'])->count(),
        ];
    @endphp

    <div class="space-y-6">
        {{-- Header --}}
        <x-filament::section>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="p-3 rounded-xl bg-primary-500 text-white shadow-sm ring-1 ring-black/5 dark:ring-white/10">
                        <x-heroicon-o-academic-cap class="w-10 h-10" />
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-950 dark:text-white">{{ $teacher->user->name }}</h2>
                        <p class="text-gray-600 dark:text-gray-400">محفظ القرآن الكريم</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="text-center px-6 py-3 rounded-xl bg-primary-50 ring-1 ring-black/5 dark:bg-primary-500/10 dark:ring-white/10">
                        <div class="text-3xl font-bold text-primary-700 dark:text-primary-400">{{ $students->count() }}</div>
                        <div class="text-sm text-gray-700 dark:text-gray-400">طالب</div>
                    </div>
                    <div class="text-center px-6 py-3 rounded-xl bg-success-50 ring-1 ring-black/5 dark:bg-success-500/10 dark:ring-white/10">
                        <div class="text-3xl font-bold text-success-700 dark:text-success-400">{{ $students->where('status', 'active')->count() }}</div>
                        <div class="text-sm text-gray-700 dark:text-gray-400">نشط</div>
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-filament::section class="ring-1 ring-black/5 dark:ring-white/10">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-400">جلسات الأسبوع</p>
                        <p class="text-3xl font-bold text-gray-950 dark:text-white mt-1">{{ $stats['total_sessions'] }}</p>
                        <p class="text-sm mt-2 {{ $stats['sessions_change'] >= 0 ? 'text-success-700 dark:text-success-400' : 'text-danger-700 dark:text-danger-400' }}">
                            {{ $stats['sessions_change'] >= 0 ? '↑' : '↓' }} {{ abs($stats['sessions_change']) }}%
                        </p>
                    </div>
                    <div class="p-3 rounded-lg bg-primary-500 shadow-sm">
                        <x-heroicon-o-calendar class="w-6 h-6 text-white" />
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section class="ring-1 ring-black/5 dark:ring-white/10">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-400">صفحات محفوظة</p>
                        <p class="text-3xl font-bold text-gray-950 dark:text-white mt-1">{{ $stats['total_pages'] }}</p>
                        <p class="text-sm mt-2 {{ $stats['pages_change'] >= 0 ? 'text-success-700 dark:text-success-400' : 'text-danger-700 dark:text-danger-400' }}">
                            {{ $stats['pages_change'] >= 0 ? '↑' : '↓' }} {{ abs($stats['pages_change']) }}%
                        </p>
                    </div>
                    <div class="p-3 rounded-lg bg-success-500 shadow-sm">
                        <x-heroicon-o-book-open class="w-6 h-6 text-white" />
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section class="ring-1 ring-black/5 dark:ring-white/10">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-400">نسبة الحضور</p>
                        <p class="text-3xl font-bold text-gray-950 dark:text-white mt-1">{{ $stats['attendance_rate'] }}%</p>
                        <p class="text-sm text-gray-700 dark:text-gray-400 mt-2">{{ $stats['students_with_records'] }} من {{ $stats['total_students'] }}</p>
                    </div>
                    <div class="p-3 rounded-lg bg-info-500 shadow-sm">
                        <x-heroicon-o-user-group class="w-6 h-6 text-white" />
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section class="ring-1 ring-black/5 dark:ring-white/10">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-400">متوسط الأخطاء</p>
                        <p class="text-3xl font-bold text-gray-950 dark:text-white mt-1">{{ $stats['avg_mistakes'] }}</p>
                        <p class="text-sm text-gray-700 dark:text-gray-400 mt-2">{{ $stats['excellent_sessions'] }} جلسة ممتازة</p>
                    </div>
                    <div class="p-3 rounded-lg bg-warning-500 shadow-sm">
                        <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-white" />
                    </div>
                </div>
            </x-filament::section>
        </div>

        {{-- Today Records --}}
        <x-filament::section class="ring-1 ring-black/5 dark:ring-white/10">
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-calendar-days class="w-5 h-5 text-success-600 dark:text-success-400" />
                    <span>تسميع اليوم - {{ now()->translatedFormat('l j F Y') }}</span>
                </div>
            </x-slot>
            <x-slot name="description">مرتب حسب عدد الصفحات ثم الأخطاء الأقل</x-slot>

            @if($todayRecords->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="p-3 text-right font-semibold text-gray-950 dark:text-white">#</th>
                                <th class="p-3 text-right font-semibold text-gray-950 dark:text-white">الطالب</th>
                                <th class="p-3 text-center font-semibold text-gray-950 dark:text-white">السورة</th>
                                <th class="p-3 text-center font-semibold text-gray-950 dark:text-white">الآيات</th>
                                <th class="p-3 text-center font-semibold text-gray-950 dark:text-white">الصفحات</th>
                                <th class="p-3 text-center font-semibold text-gray-950 dark:text-white">النوع</th>
                                <th class="p-3 text-center font-semibold text-gray-950 dark:text-white">التقييم</th>
                                <th class="p-3 text-center font-semibold text-gray-950 dark:text-white">الأخطاء</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($todayRecords as $index => $record)
                                <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50/70 dark:hover:bg-gray-800/50 transition-colors {{ $index < 3 ? 'bg-primary-50/70 dark:bg-primary-500/5' : '' }}">
                                    <td class="p-3">
                                        @if($index === 0) <span class="text-xl">🥇</span>
                                        @elseif($index === 1) <span class="text-xl">🥈</span>
                                        @elseif($index === 2) <span class="text-xl">🥉</span>
                                        @else <span class="font-bold text-gray-950 dark:text-white">{{ $index + 1 }}</span>
                                        @endif
                                    </td>
                                    <td class="p-3 font-medium text-gray-950 dark:text-white">{{ $record->student->user->name ?? '-' }}</td>
                                    <td class="p-3 text-center">
                                        <x-filament::badge color="info">{{ $this->getSurahName($record->surah_id) }}</x-filament::badge>
                                    </td>
                                    <td class="p-3 text-center text-gray-950 dark:text-white">{{ $record->from_ayah ?? '-' }} - {{ $record->to_ayah ?? '-' }}</td>
                                    <td class="p-3 text-center">
                                        <x-filament::badge color="primary">{{ $record->pages_count }}</x-filament::badge>
                                    </td>
                                    <td class="p-3 text-center">
                                        <x-filament::badge :color="$record->session_type === 'hifz' ? 'success' : ($record->session_type === 'revision' ? 'info' : 'gray')">
                                            {{ $record->session_type === 'hifz' ? 'حفظ' : ($record->session_type === 'revision' ? 'مراجعة' : 'اختبار') }}
                                        </x-filament::badge>
                                    </td>
                                    <td class="p-3 text-center">
                                        @php
                                            $evalColors = ['excellent' => 'success', 'very_good' => 'info', 'good' => 'primary', 'acceptable' => 'warning', 'needs_review' => 'danger'];
                                            $evalNames = ['excellent' => 'ممتاز', 'very_good' => 'جيد جداً', 'good' => 'جيد', 'acceptable' => 'مقبول', 'needs_review' => 'ضعيف'];
                                        @endphp
                                        <x-filament::badge :color="$evalColors[$record->evaluation] ?? 'gray'">{{ $evalNames[$record->evaluation] ?? '-' }}</x-filament::badge>
                                    </td>
                                    <td class="p-3 text-center">
                                        <x-filament::badge :color="($record->mistakes_count ?? 0) <= 2 ? 'success' : (($record->mistakes_count ?? 0) <= 5 ? 'warning' : 'danger')">
                                            {{ $record->mistakes_count ?? 0 }}
                                        </x-filament::badge>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 p-3 rounded-lg bg-gray-100 text-gray-900 ring-1 ring-black/5 dark:bg-gray-800 dark:text-white dark:ring-white/10 text-center">
                    <span class="font-semibold">إجمالي: {{ $todayRecords->count() }} جلسة | {{ $todayRecords->sum('pages_count') }} صفحة</span>
                </div>
            @else
                <div class="text-center py-8">
                    <x-heroicon-o-inbox class="w-12 h-12 mx-auto text-gray-400" />
                    <p class="mt-2 text-gray-700 dark:text-gray-400">لا يوجد تسميع اليوم بعد</p>
                </div>
            @endif
        </x-filament::section>

        {{-- Charts --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <x-filament::section class="lg:col-span-2 ring-1 ring-black/5 dark:ring-white/10">
                <x-slot name="heading">📊 الصفحات المحفوظة - آخر 7 أيام</x-slot>
                <div class="h-64">
                    <canvas id="dailyPagesChart"></canvas>
                </div>
            </x-filament::section>

            {{-- ✅ بدل "توزيع التقييمات" --}}
            <x-filament::section class="ring-1 ring-black/5 dark:ring-white/10">
                <x-slot name="heading">🧩 توزيع أنواع الجلسات (اليوم)</x-slot>
                <x-slot name="description">حفظ / مراجعة / اختبار — أهم تشخيص للحلقة من التقييمات العامة</x-slot>
                <div class="h-64">
                    <canvas id="sessionTypeChart"></canvas>
                </div>
            </x-filament::section>
        </div>

        {{-- Monthly --}}
        <x-filament::section class="ring-1 ring-black/5 dark:ring-white/10">
            <x-slot name="heading">📅 الأداء الشهري - آخر 6 أشهر</x-slot>
            <div class="h-72">
                <canvas id="monthlyChart"></canvas>
            </div>
        </x-filament::section>

        {{-- Plans --}}
        <x-filament::section class="ring-1 ring-black/5 dark:ring-white/10">
            <x-slot name="heading">📋 حالة الخطط</x-slot>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="text-center p-4 rounded-xl bg-gray-100 ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
                    <div class="text-3xl font-bold text-gray-950 dark:text-white">{{ $weeklyCompletions['total'] }}</div>
                    <div class="text-sm text-gray-700 dark:text-gray-400">إجمالي</div>
                </div>
                <div class="text-center p-4 rounded-xl bg-success-50 ring-1 ring-black/5 dark:bg-success-500/10 dark:ring-white/10">
                    <div class="text-3xl font-bold text-success-700 dark:text-success-400">{{ $weeklyCompletions['completed'] }}</div>
                    <div class="text-sm text-gray-700 dark:text-gray-400">مكتملة ✓</div>
                </div>
                <div class="text-center p-4 rounded-xl bg-info-50 ring-1 ring-black/5 dark:bg-info-500/10 dark:ring-white/10">
                    <div class="text-3xl font-bold text-info-700 dark:text-info-400">{{ $weeklyCompletions['in_progress'] }}</div>
                    <div class="text-sm text-gray-700 dark:text-gray-400">قيد التنفيذ</div>
                </div>
                <div class="text-center p-4 rounded-xl bg-danger-50 ring-1 ring-black/5 dark:bg-danger-500/10 dark:ring-white/10">
                    <div class="text-3xl font-bold text-danger-700 dark:text-danger-400">{{ $weeklyCompletions['not_started'] }}</div>
                    <div class="text-sm text-gray-700 dark:text-gray-400">لم تبدأ</div>
                </div>
            </div>

            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="font-medium text-gray-950 dark:text-white">نسبة الإنجاز</span>
                    <span class="font-bold text-gray-950 dark:text-white">{{ $weeklyCompletions['completion_rate'] }}%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden ring-1 ring-black/5 dark:ring-white/10">
                    <div class="h-full rounded-full bg-success-500 transition-all" style="width: {{ $weeklyCompletions['completion_rate'] }}%"></div>
                </div>
            </div>
        </x-filament::section>

        {{-- Top Students --}}
        <x-filament::section class="ring-1 ring-black/5 dark:ring-white/10">
            <x-slot name="heading">🏆 أفضل الطلاب</x-slot>
            <x-slot name="description">مرتبين حسب عدد الصفحات المسمّعة</x-slot>

            @if($topStudents->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    @foreach($topStudents as $index => $student)
                        <div class="rounded-xl p-4 border-2 transition-all hover:shadow-lg
                            ring-1 ring-black/5 dark:ring-white/10
                            {{ $index === 0 ? 'border-yellow-400 bg-yellow-50 dark:bg-yellow-500/10' : '' }}

                            {{-- ✅ FIX المركز الثاني: استخدم dark:bg ثابتة بدون /40 --}}
                            {{ $index === 1 ? 'border-gray-300 bg-gray-50 dark:border-gray-600 dark:bg-gray-800' : '' }}

                            {{ $index === 2 ? 'border-orange-400 bg-orange-50 dark:bg-orange-500/10' : '' }}
                            {{ $index > 2 ? 'border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800' : '' }}
                        ">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="text-2xl font-bold text-gray-950 dark:text-white">{{ $index + 1 }}</span>
                                @if($index === 0) 🥇 @elseif($index === 1) 🥈 @elseif($index === 2) 🥉 @endif
                            </div>

                            <div class="font-bold text-gray-950 dark:text-white mb-3">{{ $student->user->name }}</div>

                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-700 dark:text-gray-400">📄 صفحات</span>
                                    <span class="font-bold text-gray-950 dark:text-white">{{ $student->total_pages }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-700 dark:text-gray-400">📚 أجزاء</span>
                                    <span class="font-bold text-gray-950 dark:text-white">{{ $student->memorized_juz }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-700 dark:text-gray-400">❌ أخطاء</span>
                                    <span class="font-bold text-gray-950 dark:text-white">{{ $student->total_mistakes }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <x-heroicon-o-user-group class="w-12 h-12 mx-auto text-gray-400" />
                    <p class="mt-2 text-gray-700 dark:text-gray-400">لا يوجد طلاب</p>
                </div>
            @endif
        </x-filament::section>

        {{-- Students Table --}}
        <x-filament::section class="ring-1 ring-black/5 dark:ring-white/10">
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-table-cells class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                    <span>جدول الطلاب الشامل</span>
                </div>
            </x-slot>
            <x-slot name="description">مرتب حسب عدد الصفحات المسمّعة</x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="p-3 text-right font-semibold text-gray-950 dark:text-white">#</th>
                            <th class="p-3 text-right font-semibold text-gray-950 dark:text-white">الطالب</th>
                            <th class="p-3 text-center font-semibold text-gray-950 dark:text-white">📄 الصفحات</th>
                            <th class="p-3 text-center font-semibold text-gray-950 dark:text-white">📚 الأجزاء</th>
                            <th class="p-3 text-center font-semibold text-gray-950 dark:text-white">🔢 الجلسات</th>

                            {{-- ✅ حذفنا الأخطاء --}}
                            {{-- <th class="p-3 text-center font-semibold text-gray-950 dark:text-white">❌ الأخطاء</th> --}}

                            <th class="p-3 text-center font-semibold text-gray-950 dark:text-white">⭐ الممتاز</th>
                            <th class="p-3 text-center font-semibold text-gray-950 dark:text-white">✅ الحضور</th>
                            <th class="p-3 text-center font-semibold text-gray-950 dark:text-white">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $index => $student)
                            <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50/70 dark:hover:bg-gray-800/50 transition-colors
                                {{ $index < 3 ? 'bg-primary-50/50 dark:bg-primary-500/5' : '' }}
                            ">
                                <td class="p-3">
                                    @if($index === 0) <span class="text-xl">🥇</span>
                                    @elseif($index === 1) <span class="text-xl">🥈</span>
                                    @elseif($index === 2) <span class="text-xl">🥉</span>
                                    @else
                                        <span class="w-7 h-7 flex items-center justify-center rounded-full bg-gray-200 text-gray-900 dark:bg-gray-700 dark:text-white font-bold text-xs">
                                            {{ $index + 1 }}
                                        </span>
                                    @endif
                                </td>

                                <td class="p-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-primary-600 dark:bg-primary-500 flex items-center justify-center text-white font-bold text-sm shadow-sm">
                                            {{ mb_substr($student->user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-semibold text-gray-950 dark:text-white">{{ $student->user->name }}</div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">{{ $student->user->phone }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="p-3 text-center">
                                    <x-filament::badge color="primary" size="lg">{{ $student->total_pages }}</x-filament::badge>
                                </td>

                                <td class="p-3 text-center">
                                    <x-filament::badge color="info">{{ $student->memorized_juz }}</x-filament::badge>
                                </td>

                                <td class="p-3 text-center">
                                    <span class="font-semibold text-gray-950 dark:text-white">{{ $student->sessions_count }}</span>
                                </td>

                                {{-- ✅ حذفنا الأخطاء --}}
                                {{-- <td class="p-3 text-center">
                                    <x-filament::badge :color="$student->total_mistakes <= 10 ? 'success' : ($student->total_mistakes <= 30 ? 'warning' : 'danger')">
                                        {{ $student->total_mistakes }}
                                    </x-filament::badge>
                                </td> --}}

                                <td class="p-3 text-center">
                                    <x-filament::badge :color="$student->excellent_rate >= 70 ? 'success' : ($student->excellent_rate >= 40 ? 'info' : 'gray')">
                                        {{ $student->excellent_rate }}%
                                    </x-filament::badge>
                                </td>

                                <td class="p-3 text-center">
                                    <x-filament::badge :color="$student->attendance_rate >= 70 ? 'success' : ($student->attendance_rate >= 40 ? 'warning' : 'danger')">
                                        {{ $student->attendance_rate }}%
                                    </x-filament::badge>
                                </td>

                                <td class="p-3 text-center">
                                    <x-filament::button
                                        size="sm"
                                        color="primary"
                                        icon="heroicon-o-chart-bar"
                                        :href="route('filament.admin.pages.student-report', ['student' => $student->id])"
                                        tag="a"
                                    >
                                        التقرير
                                    </x-filament::button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center">
                                    <x-heroicon-o-user-group class="w-12 h-12 mx-auto text-gray-400" />
                                    <p class="mt-2 text-gray-700 dark:text-gray-400">لا يوجد طلاب في هذه الحلقة</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($students->count() > 0)
                <div class="mt-4 p-4 rounded-xl bg-gray-100 text-gray-900 ring-1 ring-black/5 dark:bg-gray-800 dark:text-white dark:ring-white/10">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-bold">{{ $students->count() }}</div>
                            <div class="text-sm text-gray-700 dark:text-gray-400">طالب</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-primary-700 dark:text-primary-400">{{ $students->sum('total_pages') }}</div>
                            <div class="text-sm text-gray-700 dark:text-gray-400">صفحة</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-info-700 dark:text-info-400">{{ $students->sum('memorized_juz') }}</div>
                            <div class="text-sm text-gray-700 dark:text-gray-400">جزء</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-success-700 dark:text-success-400">{{ round($students->avg('excellent_rate'), 1) }}%</div>
                            <div class="text-sm text-gray-700 dark:text-gray-400">متوسط الممتاز</div>
                        </div>

                        {{-- ✅ حذفنا إجمالي الأخطاء --}}
                    </div>
                </div>
            @endif
        </x-filament::section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3"></script>
    <script>
        (function () {
            if (window.__teacherReportChartsBooted) return;
            window.__teacherReportChartsBooted = true;

            const charts = {};

            function hasDark() {
                return document.documentElement.classList.contains('dark') || document.body.classList.contains('dark');
            }

            function theme() {
                const isDark = hasDark();

                // ✅ ألوان ثابتة ومضمونة (بدون الاعتماد على CSS vars التي قد ترجع فاضي)
                return {
                    isDark,
                    text: isDark ? 'rgba(255,255,255,0.92)' : 'rgba(17,24,39,0.92)',
                    muted: isDark ? 'rgba(255,255,255,0.72)' : 'rgba(17,24,39,0.72)',
                    grid: isDark ? 'rgba(255,255,255,0.10)' : 'rgba(0,0,0,0.10)',
                    card: isDark ? 'rgba(15,23,42,1)' : 'rgba(255,255,255,1)',

                    // palette
                    primary: '#3B82F6',
                    success: '#22C55E',
                    info:    '#06B6D4',
                    warning: '#F59E0B',
                    danger:  '#EF4444',
                    violet:  '#8B5CF6',
                    pink:    '#EC4899',
                };
            }

            function destroyAll() {
                Object.values(charts).forEach(ch => { try { ch.destroy(); } catch(e) {} });
                for (const k in charts) delete charts[k];
            }

            function render() {
                const t = theme();

                const dailyEl = document.getElementById('dailyPagesChart');
                const typeEl  = document.getElementById('sessionTypeChart');
                const monEl   = document.getElementById('monthlyChart');

                if (!dailyEl || !typeEl || !monEl || !window.Chart) return;

                destroyAll();

                Chart.defaults.color = t.text;
                Chart.defaults.font.family = getComputedStyle(document.body).fontFamily || 'system-ui, sans-serif';

                // ---------------- Daily Pages (Bar) - ✅ متعدد الألوان
                const dailyLabels = {!! json_encode($dailyPagesData['labels']) !!};
                const dailyValues = {!! json_encode($dailyPagesData['data']) !!};

                const dayPalette = [t.primary, t.success, t.warning, t.violet, t.info, t.pink, t.danger];

                charts.daily = new Chart(dailyEl, {
                    type: 'bar',
                    data: {
                        labels: dailyLabels,
                        datasets: [{
                            label: 'الصفحات',
                            data: dailyValues,
                            backgroundColor: dailyValues.map((_, i) => dayPalette[i % dayPalette.length]),
                            borderRadius: 10,
                            borderSkipped: false,
                            maxBarThickness: 42,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: t.isDark ? 'rgba(2,6,23,0.92)' : 'rgba(255,255,255,0.98)',
                                borderColor: t.isDark ? 'rgba(255,255,255,0.14)' : 'rgba(0,0,0,0.10)',
                                borderWidth: 1,
                                titleColor: t.text,
                                bodyColor: t.text,
                                padding: 12,
                            }
                        },
                        scales: {
                            x: { ticks: { color: t.text }, grid: { display: false } },
                            y: { beginAtZero: true, ticks: { color: t.text }, grid: { color: t.grid } }
                        }
                    }
                });

                // ---------------- Session Type (Doughnut) - ✅ بديل التقييمات
                const typeLabels = ['حفظ', 'مراجعة', 'اختبار', 'أخرى'];
                const typeValues = [
                    {{ $sessionTypeCounts['hifz'] }},
                    {{ $sessionTypeCounts['revision'] }},
                    {{ $sessionTypeCounts['test'] }},
                    {{ $sessionTypeCounts['other'] }},
                ];

                charts.sessionType = new Chart(typeEl, {
                    type: 'doughnut',
                    data: {
                        labels: typeLabels,
                        datasets: [{
                            data: typeValues,
                            backgroundColor: [t.success, t.info, t.violet, t.warning],
                            borderColor: t.card,
                            borderWidth: 6,
                            spacing: 2,
                            hoverOffset: 10,
                            cutout: '64%',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom',
                                labels: {
                                    color: t.text,
                                    padding: 16,
                                    boxWidth: 12,
                                    boxHeight: 12,
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                }
                            },
                            tooltip: {
                                backgroundColor: t.isDark ? 'rgba(2,6,23,0.92)' : 'rgba(255,255,255,0.98)',
                                borderColor: t.isDark ? 'rgba(255,255,255,0.14)' : 'rgba(0,0,0,0.10)',
                                borderWidth: 1,
                                titleColor: t.text,
                                bodyColor: t.text,
                                padding: 12,
                            }
                        }
                    }
                });

                // ---------------- Monthly (Line)
                const monthlyLabels = {!! json_encode($monthlyData['labels']) !!};
                const monthlySessions = {!! json_encode($monthlyData['sessions']) !!};
                const monthlyPages = {!! json_encode($monthlyData['pages']) !!};

                charts.monthly = new Chart(monEl, {
                    type: 'line',
                    data: {
                        labels: monthlyLabels,
                        datasets: [
                            {
                                label: 'الجلسات',
                                data: monthlySessions,
                                borderColor: t.primary,
                                backgroundColor: t.isDark ? 'rgba(59,130,246,0.18)' : 'rgba(59,130,246,0.10)',
                                fill: true,
                                tension: 0.35,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                pointBackgroundColor: t.primary,
                                borderWidth: 2,
                            },
                            {
                                label: 'الصفحات',
                                data: monthlyPages,
                                borderColor: t.success,
                                backgroundColor: 'transparent',
                                tension: 0.35,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                pointBackgroundColor: t.success,
                                borderWidth: 2,
                                yAxisID: 'y1',
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { display: true, position: 'bottom', labels: { color: t.text, padding: 18 } },
                            tooltip: {
                                backgroundColor: t.isDark ? 'rgba(2,6,23,0.92)' : 'rgba(255,255,255,0.98)',
                                borderColor: t.isDark ? 'rgba(255,255,255,0.14)' : 'rgba(0,0,0,0.10)',
                                borderWidth: 1,
                                titleColor: t.text,
                                bodyColor: t.text,
                                padding: 12,
                            }
                        },
                        scales: {
                            x: { ticks: { color: t.text }, grid: { display: false } },
                            y: { beginAtZero: true, ticks: { color: t.text }, grid: { color: t.grid }, title: { display: true, text: 'الجلسات', color: t.muted } },
                            y1: { beginAtZero: true, position: 'right', ticks: { color: t.text }, grid: { display: false }, title: { display: true, text: 'الصفحات', color: t.muted } }
                        }
                    }
                });
            }

            function observeTheme() {
                const obs = new MutationObserver((muts) => {
                    for (const m of muts) {
                        if (m.type === 'attributes' && m.attributeName === 'class') {
                            render();
                            break;
                        }
                    }
                });
                obs.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
            }

            document.addEventListener('DOMContentLoaded', function () {
                render();
                observeTheme();
                document.addEventListener('livewire:navigated', render);
            });

            /*
              مصادر مفيدة (روابط داخل تعليق كما طلبت):
              https://www.chartjs.org/docs/latest/
              https://tailwindcss.com/docs/dark-mode
              https://filamentphp.com/docs
            */
        })();
    </script>
</x-filament-panels::page>