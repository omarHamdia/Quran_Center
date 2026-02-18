<x-filament-widgets::widget>
    <x-filament::section>
        @php
            $todayRecords = $this->getTodayData();
            $summary = $this->getTodaySummary();
        @endphp

        <x-slot name="heading">
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-clipboard-document-check class="w-5 h-5 text-success-500" />
                    <span>📋 تسميع اليوم - {{ now()->format('Y/m/d') }}</span>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    <span class="px-2 py-1 rounded-full bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400">
                        {{ $summary['total_sessions'] }} جلسة
                    </span>
                    <span class="px-2 py-1 rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400">
                        {{ $summary['students_count'] }} طالب
                    </span>
                    @if($summary['total_sessions'] > 0)
                        <a href="{{ route('teacher.today-pdf') }}"
                           target="_blank"
                           class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400 hover:bg-danger-200 transition-colors">
                            <x-heroicon-m-arrow-down-tray class="w-3 h-3" />
                            تنزيل PDF
                        </a>
                    @endif
                </div>
            </div>
        </x-slot>

        {{-- بطاقات ملخص اليوم --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
            <div class="p-3 bg-success-50 dark:bg-success-900/10 rounded-lg text-center">
                <div class="text-2xl font-bold text-success-600 dark:text-success-400">{{ $summary['total_sessions'] }}</div>
                <div class="text-xs text-gray-500">إجمالي الجلسات</div>
            </div>
            <div class="p-3 bg-emerald-50 dark:bg-emerald-900/10 rounded-lg text-center">
                <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $summary['hifz_ayahs'] }}</div>
                <div class="text-xs text-gray-500">آيات حفظ</div>
            </div>
            <div class="p-3 bg-sky-50 dark:bg-sky-900/10 rounded-lg text-center">
                <div class="text-2xl font-bold text-sky-600 dark:text-sky-400">{{ $summary['revision_ayahs'] }}</div>
                <div class="text-xs text-gray-500">آيات مراجعة</div>
            </div>
            <div class="p-3 bg-violet-50 dark:bg-violet-900/10 rounded-lg text-center">
                <div class="text-2xl font-bold text-violet-600 dark:text-violet-400">{{ $summary['students_count'] }}</div>
                <div class="text-xs text-gray-500">طلاب سمّعوا</div>
            </div>
        </div>

        @if($todayRecords->isEmpty())
            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                <x-heroicon-o-clipboard class="w-12 h-12 mx-auto mb-2 opacity-50" />
                <p class="text-sm font-medium">لا توجد جلسات تسميع مسجلة اليوم</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="p-3 text-right font-semibold">الطالب</th>
                            <th class="p-3 text-right font-semibold">النوع</th>
                            <th class="p-3 text-right font-semibold">السورة</th>
                            <th class="p-3 text-right font-semibold">الآيات</th>
                            <th class="p-3 text-center font-semibold">العدد</th>
                            <th class="p-3 text-center font-semibold">التقييم</th>
                            <th class="p-3 text-center font-semibold">الأخطاء</th>
                            <th class="p-3 text-center font-semibold">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($todayRecords as $record)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="p-3">
                                    <a href="{{ url('/teacher/student-report?student=' . $record['student_id']) }}"
                                       class="font-semibold text-primary-600 hover:text-primary-800 dark:text-primary-400 hover:underline">
                                        {{ $record['student_name'] }}
                                    </a>
                                </td>
                                <td class="p-3">
                                    <x-filament::badge :color="$record['session_type_color']" size="sm">
                                        {{ $record['session_type_label'] }}
                                    </x-filament::badge>
                                </td>
                                <td class="p-3 font-medium">{{ $record['surah'] }}</td>
                                <td class="p-3 text-gray-600 dark:text-gray-400">{{ $record['ayah_range'] }}</td>
                                <td class="p-3 text-center">
                                    <x-filament::badge color="info" size="sm">{{ $record['ayahs_count'] }}</x-filament::badge>
                                </td>
                                <td class="p-3 text-center">
                                    <x-filament::badge :color="$record['evaluation_color']" size="sm">{{ $record['evaluation'] }}</x-filament::badge>
                                </td>
                                <td class="p-3 text-center">
                                    <x-filament::badge :color="$record['mistakes_count'] > 5 ? 'danger' : ($record['mistakes_count'] > 2 ? 'warning' : 'success')" size="sm">
                                        {{ $record['mistakes_count'] }}
                                    </x-filament::badge>
                                </td>
                                <td class="p-3 text-center">
                                    <a href="{{ url('/teacher/student-report?student=' . $record['student_id']) }}"
                                       class="inline-flex items-center gap-1 text-xs text-primary-600 hover:text-primary-800 dark:text-primary-400">
                                        <x-heroicon-m-document-chart-bar class="w-4 h-4" />
                                        تقرير
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>