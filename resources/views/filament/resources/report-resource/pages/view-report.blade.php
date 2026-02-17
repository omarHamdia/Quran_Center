<x-filament-panels::page>
    @php
        $teacher = $this->record;
        $students = $this->getStudents();
        $stats = $this->getWeeklyStats();
        $weeklyCompletions = $this->getWeeklyCompletions();
        $topStudents = $this->getTopStudents();
        $dailyPagesData = $this->getDailyPagesData();
        $monthlyData = $this->getMonthlyData();
        $evaluationData = $this->getEvaluationData();
        $todayRecords = $this->getTodayRecords();
    @endphp

    <div class="space-y-6">
        {{-- Header --}}
        <x-filament::section>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="p-3 rounded-xl bg-primary-500 text-white">
                        <x-heroicon-o-academic-cap class="w-10 h-10" />
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-950 dark:text-white">{{ $teacher->user->name }}</h2>
                        <p class="text-gray-600 dark:text-gray-400">محفظ القرآن الكريم</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="text-center px-6 py-3 rounded-xl bg-primary-50 dark:bg-primary-500/10">
                        <div class="text-3xl font-bold text-primary-600 dark:text-primary-400">{{ $students->count() }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">طالب</div>
                    </div>
                    <div class="text-center px-6 py-3 rounded-xl bg-success-50 dark:bg-success-500/10">
                        <div class="text-3xl font-bold text-success-600 dark:text-success-400">{{ $students->where('status', 'active')->count() }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">نشط</div>
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-filament::section>
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">جلسات الأسبوع</p>
                        <p class="text-3xl font-bold text-gray-950 dark:text-white mt-1">{{ $stats['total_sessions'] }}</p>
                        <p class="text-sm mt-2 {{ $stats['sessions_change'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                            {{ $stats['sessions_change'] >= 0 ? '↑' : '↓' }} {{ abs($stats['sessions_change']) }}%
                        </p>
                    </div>
                    <div class="p-3 rounded-lg bg-primary-500">
                        <x-heroicon-o-calendar class="w-6 h-6 text-white" />
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">صفحات محفوظة</p>
                        <p class="text-3xl font-bold text-gray-950 dark:text-white mt-1">{{ $stats['total_pages'] }}</p>
                        <p class="text-sm mt-2 {{ $stats['pages_change'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                            {{ $stats['pages_change'] >= 0 ? '↑' : '↓' }} {{ abs($stats['pages_change']) }}%
                        </p>
                    </div>
                    <div class="p-3 rounded-lg bg-success-500">
                        <x-heroicon-o-book-open class="w-6 h-6 text-white" />
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">نسبة الحضور</p>
                        <p class="text-3xl font-bold text-gray-950 dark:text-white mt-1">{{ $stats['attendance_rate'] }}%</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ $stats['students_with_records'] }} من {{ $stats['total_students'] }}</p>
                    </div>
                    <div class="p-3 rounded-lg bg-info-500">
                        <x-heroicon-o-user-group class="w-6 h-6 text-white" />
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">متوسط الأخطاء</p>
                        <p class="text-3xl font-bold text-gray-950 dark:text-white mt-1">{{ $stats['avg_mistakes'] }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ $stats['excellent_sessions'] }} جلسة ممتازة</p>
                    </div>
                    <div class="p-3 rounded-lg bg-warning-500">
                        <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-white" />
                    </div>
                </div>
            </x-filament::section>
        </div>

        {{-- Today Records --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-calendar-days class="w-5 h-5 text-success-500" />
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
                                <tr class="border-b border-gray-100 dark:border-gray-800 {{ $index < 3 ? 'bg-primary-50 dark:bg-primary-500/5' : '' }}">
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
                <div class="mt-4 p-3 rounded-lg bg-gray-100 dark:bg-gray-800 text-center">
                    <span class="text-gray-950 dark:text-white font-semibold">إجمالي: {{ $todayRecords->count() }} جلسة | {{ $todayRecords->sum('pages_count') }} صفحة</span>
                </div>
            @else
                <div class="text-center py-8">
                    <x-heroicon-o-inbox class="w-12 h-12 mx-auto text-gray-400" />
                    <p class="mt-2 text-gray-600 dark:text-gray-400">لا يوجد تسميع اليوم بعد</p>
                </div>
            @endif
        </x-filament::section>

        {{-- Charts --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <x-filament::section class="lg:col-span-2">
                <x-slot name="heading">📊 الصفحات المحفوظة - آخر 7 أيام</x-slot>
                <div class="h-64"><canvas id="dailyPagesChart"></canvas></div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">📈 توزيع التقييمات</x-slot>
                <div class="h-64"><canvas id="evaluationChart"></canvas></div>
            </x-filament::section>
        </div>

        {{-- Monthly --}}
        <x-filament::section>
            <x-slot name="heading">📅 الأداء الشهري - آخر 6 أشهر</x-slot>
            <div class="h-72"><canvas id="monthlyChart"></canvas></div>
        </x-filament::section>

        {{-- Plans --}}
        <x-filament::section>
            <x-slot name="heading">📋 حالة الخطط</x-slot>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="text-center p-4 rounded-xl bg-gray-100 dark:bg-gray-800">
                    <div class="text-3xl font-bold text-gray-950 dark:text-white">{{ $weeklyCompletions['total'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">إجمالي</div>
                </div>
                <div class="text-center p-4 rounded-xl bg-success-50 dark:bg-success-500/10">
                    <div class="text-3xl font-bold text-success-600 dark:text-success-400">{{ $weeklyCompletions['completed'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">مكتملة ✓</div>
                </div>
                <div class="text-center p-4 rounded-xl bg-info-50 dark:bg-info-500/10">
                    <div class="text-3xl font-bold text-info-600 dark:text-info-400">{{ $weeklyCompletions['in_progress'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">قيد التنفيذ</div>
                </div>
                <div class="text-center p-4 rounded-xl bg-danger-50 dark:bg-danger-500/10">
                    <div class="text-3xl font-bold text-danger-600 dark:text-danger-400">{{ $weeklyCompletions['not_started'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">لم تبدأ</div>
                </div>
            </div>

            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="font-medium text-gray-950 dark:text-white">نسبة الإنجاز</span>
                    <span class="font-bold text-gray-950 dark:text-white">{{ $weeklyCompletions['completion_rate'] }}%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                    <div class="h-full rounded-full bg-success-500 transition-all" style="width: {{ $weeklyCompletions['completion_rate'] }}%"></div>
                </div>
            </div>
        </x-filament::section>

        {{-- Top Students --}}
        <x-filament::section>
            <x-slot name="heading">🏆 أفضل الطلاب</x-slot>
            <x-slot name="description">مرتبين حسب عدد الصفحات المسمّعة</x-slot>

            @if($topStudents->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    @foreach($topStudents as $index => $student)
                        <div class="rounded-xl p-4 border-2 transition-all hover:shadow-lg
                            {{ $index === 0 ? 'border-yellow-400 bg-yellow-50 dark:bg-yellow-500/10' : '' }}
                            {{ $index === 1 ? 'border-gray-400 bg-gray-50 dark:bg-gray-500/10' : '' }}
                            {{ $index === 2 ? 'border-orange-400 bg-orange-50 dark:bg-orange-500/10' : '' }}
                            {{ $index > 2 ? 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800' : '' }}
                        ">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="text-2xl font-bold text-gray-950 dark:text-white">{{ $index + 1 }}</span>
                                @if($index === 0) 🥇 @elseif($index === 1) 🥈 @elseif($index === 2) 🥉 @endif
                            </div>
                            <div class="font-bold text-gray-950 dark:text-white mb-3">{{ $student->user->name }}</div>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">📄 صفحات</span>
                                    <span class="font-bold text-gray-950 dark:text-white">{{ $student->total_pages }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">📚 أجزاء</span>
                                    <span class="font-bold text-gray-950 dark:text-white">{{ $student->memorized_juz }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">❌ أخطاء</span>
                                    <span class="font-bold text-gray-950 dark:text-white">{{ $student->total_mistakes }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <x-heroicon-o-user-group class="w-12 h-12 mx-auto text-gray-400" />
                    <p class="mt-2 text-gray-600 dark:text-gray-400">لا يوجد طلاب</p>
                </div>
            @endif
        </x-filament::section>

        {{-- Students Table --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-table-cells class="w-5 h-5 text-primary-500" />
                    <span>جدول الطلاب الشامل</span>
                </div>
            </x-slot>
            <x-slot name="description">مرتب حسب عدد الصفحات المسمّعة ثم الأخطاء الأقل</x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="p-3 text-right font-semibold text-gray-950 dark:text-white">#</th>
                            <th class="p-3 text-right font-semibold text-gray-950 dark:text-white">الطالب</th>
                            <th class="p-3 text-center font-semibold text-gray-950 dark:text-white">📄 الصفحات</th>
                            <th class="p-3 text-center font-semibold text-gray-950 dark:text-white">📚 الأجزاء</th>
                            <th class="p-3 text-center font-semibold text-gray-950 dark:text-white">🔢 الجلسات</th>
                            <th class="p-3 text-center font-semibold text-gray-950 dark:text-white">❌ الأخطاء</th>
                            <th class="p-3 text-center font-semibold text-gray-950 dark:text-white">⭐ الممتاز</th>
                            <th class="p-3 text-center font-semibold text-gray-950 dark:text-white">✅ الحضور</th>
                            <th class="p-3 text-center font-semibold text-gray-950 dark:text-white">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $index => $student)
                            <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors
                                {{ $index < 3 ? 'bg-primary-50/50 dark:bg-primary-500/5' : '' }}
                            ">
                                <td class="p-3">
                                    @if($index === 0) <span class="text-xl">🥇</span>
                                    @elseif($index === 1) <span class="text-xl">🥈</span>
                                    @elseif($index === 2) <span class="text-xl">🥉</span>
                                    @else 
                                        <span class="w-7 h-7 flex items-center justify-center rounded-full bg-gray-200 dark:bg-gray-700 font-bold text-gray-950 dark:text-white text-xs">
                                            {{ $index + 1 }}
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-primary-500 flex items-center justify-center text-white font-bold text-sm">
                                            {{ mb_substr($student->user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-semibold text-gray-950 dark:text-white">{{ $student->user->name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $student->user->phone }}</div>
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
                                <td class="p-3 text-center">
                                    <x-filament::badge :color="$student->total_mistakes <= 10 ? 'success' : ($student->total_mistakes <= 30 ? 'warning' : 'danger')">
                                        {{ $student->total_mistakes }}
                                    </x-filament::badge>
                                </td>
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
                                <td colspan="9" class="p-8 text-center">
                                    <x-heroicon-o-user-group class="w-12 h-12 mx-auto text-gray-400" />
                                    <p class="mt-2 text-gray-600 dark:text-gray-400">لا يوجد طلاب في هذه الحلقة</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($students->count() > 0)
                <div class="mt-4 p-4 rounded-xl bg-gray-100 dark:bg-gray-800">
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-bold text-gray-950 dark:text-white">{{ $students->count() }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">طالب</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ $students->sum('total_pages') }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">صفحة</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-info-600 dark:text-info-400">{{ $students->sum('memorized_juz') }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">جزء</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-success-600 dark:text-success-400">{{ round($students->avg('excellent_rate'), 1) }}%</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">متوسط الممتاز</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-warning-600 dark:text-warning-400">{{ $students->sum('total_mistakes') }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">إجمالي الأخطاء</div>
                        </div>
                    </div>
                </div>
            @endif
        </x-filament::section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? 'rgb(255, 255, 255)' : 'rgb(17, 24, 39)';
            const gridColor = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';

            // Daily Pages Chart
            new Chart(document.getElementById('dailyPagesChart'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($dailyPagesData['labels']) !!},
                    datasets: [{
                        label: 'الصفحات',
                        data: {!! json_encode($dailyPagesData['data']) !!},
                        backgroundColor: ['#f59e0b', '#3b82f6', '#10b981', '#ec4899', '#8b5cf6', '#06b6d4', '#84cc16'],
                        borderRadius: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { color: textColor }, grid: { display: false } },
                        y: { beginAtZero: true, ticks: { color: textColor }, grid: { color: gridColor } }
                    }
                }
            });

            // Evaluation Chart
            new Chart(document.getElementById('evaluationChart'), {
                type: 'doughnut',
                data: {
                    labels: ['ممتاز', 'جيد جداً', 'جيد', 'مقبول', 'ضعيف'],
                    datasets: [{
                        data: [{{ $evaluationData['excellent'] }}, {{ $evaluationData['very_good'] }}, {{ $evaluationData['good'] }}, {{ $evaluationData['acceptable'] }}, {{ $evaluationData['needs_review'] }}],
                        backgroundColor: ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b', '#ef4444'],
                        borderWidth: 0,
                        cutout: '60%',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: true, position: 'bottom', rtl: true, labels: { color: textColor, padding: 15 } } }
                }
            });

            // Monthly Chart
            new Chart(document.getElementById('monthlyChart'), {
                type: 'line',
                data: {
                    labels: {!! json_encode($monthlyData['labels']) !!},
                    datasets: [
                        {
                            label: 'الجلسات',
                            data: {!! json_encode($monthlyData['sessions']) !!},
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 6,
                            pointBackgroundColor: '#3b82f6',
                        },
                        {
                            label: 'الصفحات',
                            data: {!! json_encode($monthlyData['pages']) !!},
                            borderColor: '#10b981',
                            tension: 0.4,
                            pointRadius: 6,
                            pointBackgroundColor: '#10b981',
                            yAxisID: 'y1',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { display: true, position: 'bottom', rtl: true, labels: { color: textColor, padding: 20 } } },
                    scales: {
                        x: { ticks: { color: textColor }, grid: { display: false } },
                        y: { beginAtZero: true, ticks: { color: textColor }, grid: { color: gridColor }, title: { display: true, text: 'الجلسات', color: textColor } },
                        y1: { beginAtZero: true, position: 'right', ticks: { color: textColor }, grid: { display: false }, title: { display: true, text: 'الصفحات', color: textColor } }
                    }
                }
            });
        });
    </script>
</x-filament-panels::page>