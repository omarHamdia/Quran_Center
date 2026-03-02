<x-filament::section>
    <x-slot name="heading">
        <div class="flex items-center gap-2">
            <x-heroicon-o-calendar-days class="w-5 h-5 text-primary-600 dark:text-primary-400" />
            <span>تسميع اليوم - {{ now()->translatedFormat('l j F Y') }}</span>
        </div>
    </x-slot>

    @php
        $todayRecords = $this->getTodayData();
        $summary = $this->getTodaySummary();

        $sortedRecords = $todayRecords
            ->sortByDesc(fn ($r) => (int) ($r['ayahs_count'] ?? 0))
            ->values();
    @endphp

    {{-- ✅ الإحصائيات بعرض الصفحة - 4 كروت أفقية --}}
    <div class="grid grid-cols-4 gap-3 mb-4">
        <div class="rounded-xl border border-emerald-200 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-900/20 p-4 text-center">
            <div class="text-2xl font-bold text-emerald-700 dark:text-emerald-300">{{ $summary['total_sessions'] }}</div>
            <div class="text-xs font-medium text-emerald-600 dark:text-emerald-400">جلسات</div>
        </div>
        <div class="rounded-xl border border-sky-200 dark:border-sky-700 bg-sky-50 dark:bg-sky-900/20 p-4 text-center">
            <div class="text-2xl font-bold text-sky-700 dark:text-sky-300">{{ $summary['hifz_ayahs'] }}</div>
            <div class="text-xs font-medium text-sky-600 dark:text-sky-400">آيات حفظ</div>
        </div>
        <div class="rounded-xl border border-violet-200 dark:border-violet-700 bg-violet-50 dark:bg-violet-900/20 p-4 text-center">
            <div class="text-2xl font-bold text-violet-700 dark:text-violet-300">{{ $summary['revision_ayahs'] }}</div>
            <div class="text-xs font-medium text-violet-600 dark:text-violet-400">آيات مراجعة</div>
        </div>
        <div class="rounded-xl border border-amber-200 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 p-4 text-center">
            <div class="text-2xl font-bold text-amber-700 dark:text-amber-300">{{ $summary['students_count'] }}</div>
            <div class="text-xs font-medium text-amber-600 dark:text-amber-400">طلاب شاركوا</div>
        </div>
    </div>

    @if($sortedRecords->count() > 0)
        {{-- ✅ زر PDF واحد فوق الجدول في الزاوية --}}
        <div class="flex justify-start mb-3">
            <x-filament::button
                size="sm"
                color="gray"
                icon="heroicon-o-arrow-down-tray"
                tag="a"
                :href="route('teacher.today.pdf')"
                target="_blank"
            >
                تحميل تقرير اليوم PDF
            </x-filament::button>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="p-3 text-right font-semibold text-gray-700 dark:text-gray-200">#</th>
                        <th class="p-3 text-right font-semibold text-gray-700 dark:text-gray-200">الطالب</th>
                        <th class="p-3 text-center font-semibold text-gray-700 dark:text-gray-200">السورة</th>
                        <th class="p-3 text-center font-semibold text-gray-700 dark:text-gray-200">الآيات</th>
                        <th class="p-3 text-center font-semibold text-gray-700 dark:text-gray-200">عدد الآيات</th>
                        <th class="p-3 text-center font-semibold text-gray-700 dark:text-gray-200">النوع</th>
                        <th class="p-3 text-center font-semibold text-gray-700 dark:text-gray-200">التقييم</th>
                        <th class="p-3 text-center font-semibold text-gray-700 dark:text-gray-200">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900">
                    @foreach($sortedRecords as $index => $record)
                        @php
                            $rankIcon = $index === 0 ? '🥇' : ($index === 1 ? '🥈' : ($index === 2 ? '🥉' : null));
                            $rowBg = $index < 3 ? 'bg-emerald-50/40 dark:bg-emerald-900/10' : '';
                            $studentReportUrl = url('/teacher/student-report?student=' . $record['student_id']);
                        @endphp
                        <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50/70 dark:hover:bg-gray-800/40 transition-colors {{ $rowBg }}">
                            <td class="p-3">
                                @if($rankIcon)
                                    <span class="text-xl">{{ $rankIcon }}</span>
                                @else
                                    <span class="font-bold text-gray-700 dark:text-gray-200">{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td class="p-3">
                                <div class="font-semibold text-gray-800 dark:text-gray-100">{{ $record['student_name'] }}</div>
                            </td>
                            <td class="p-3 text-center">
                                <x-filament::badge color="info">{{ $record['surah'] }}</x-filament::badge>
                            </td>
                            <td class="p-3 text-center text-gray-700 dark:text-gray-200">
                                {{ $record['ayah_range'] }}
                            </td>
                            <td class="p-3 text-center">
                                <x-filament::badge color="primary">{{ $record['ayahs_count'] }}</x-filament::badge>
                            </td>
                            <td class="p-3 text-center">
                                <x-filament::badge :color="$record['session_type_color']">
                                    {{ $record['session_type_label'] }}
                                </x-filament::badge>
                            </td>
                            <td class="p-3 text-center">
                                <x-filament::badge :color="$record['evaluation_color']">
                                    {{ $record['evaluation'] }}
                                </x-filament::badge>
                            </td>
                            <td class="p-3 text-center">
                                <x-filament::button
                                    size="sm"
                                    color="primary"
                                    icon="heroicon-o-chart-bar"
                                    tag="a"
                                    :href="$studentReportUrl"
                                >
                                    تقرير
                                </x-filament::button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4 p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-center">
            <span class="text-gray-700 dark:text-gray-200 font-semibold">
                إجمالي: {{ $sortedRecords->count() }} جلسة | مجموع الآيات: {{ $sortedRecords->sum('ayahs_count') }}
            </span>
        </div>
    @else
        <div class="text-center py-10">
            <x-heroicon-o-inbox class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-500" />
            <p class="mt-2 text-gray-600 dark:text-gray-400">لا يوجد تسميع اليوم بعد</p>
        </div>
    @endif
</x-filament::section>