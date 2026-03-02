<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-academic-cap class="w-5 h-5 text-primary-500" />
                <span>أداء المعلمين هذا الأسبوع</span>
            </div>
        </x-slot>

        @php
            $teachers = $this->getTeachersData();
        @endphp

        @if($teachers->isEmpty())
            <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                <x-heroicon-o-users class="w-12 h-12 mx-auto mb-2 text-gray-300" />
                <p class="text-sm">لا يوجد معلمون مسجلون</p>
            </div>
        @else
            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="p-3 text-right font-semibold text-gray-700 dark:text-gray-200">المعلم</th>
                            <th class="p-3 text-center font-semibold text-gray-700 dark:text-gray-200">الطلاب النشطون</th>
                            <th class="p-3 text-center font-semibold text-gray-700 dark:text-gray-200">جلسات الأسبوع</th>
                            <th class="p-3 text-center font-semibold text-gray-700 dark:text-gray-200">آيات الأسبوع</th>
                            <th class="p-3 text-center font-semibold text-gray-700 dark:text-gray-200">متوسط التقييم</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900">
                        @foreach($teachers as $teacher)
                            <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors cursor-pointer"
                                onclick="window.location.href='{{ url('/admin/teachers/' . $teacher['id']) }}'">
                                <td class="p-3">
                                    <div class="font-semibold text-gray-800 dark:text-gray-100">
                                        {{ $teacher['name'] }}
                                    </div>
                                </td>
                                <td class="p-3 text-center">
                                    <span class="inline-flex px-2 py-1 rounded-full bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 ring-1 ring-emerald-200 dark:ring-emerald-700 font-semibold">
                                        {{ $teacher['students_count'] }}
                                    </span>
                                </td>
                                <td class="p-3 text-center">
                                    <span class="inline-flex px-2 py-1 rounded-full bg-sky-50 dark:bg-sky-900/20 text-sky-700 dark:text-sky-300 ring-1 ring-sky-200 dark:ring-sky-700 font-semibold">
                                        {{ $teacher['weekly_sessions'] }}
                                    </span>
                                </td>
                                <td class="p-3 text-center">
                                    <span class="inline-flex px-2 py-1 rounded-full bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 ring-1 ring-amber-200 dark:ring-amber-700 font-semibold">
                                        {{ number_format($teacher['weekly_ayahs']) }}
                                    </span>
                                </td>
                                <td class="p-3 text-center">
                                    @php
                                        $score = (float) $teacher['avg_score'];
                                        $stars = str_repeat('⭐', (int) round($score));
                                        $scoreColor = $score >= 4 ? 'text-emerald-600 dark:text-emerald-400'
                                            : ($score >= 3 ? 'text-amber-600 dark:text-amber-400'
                                            : ($score > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-400'));
                                    @endphp
                                    <span class="{{ $scoreColor }} font-semibold text-xs">
                                        @if($score > 0)
                                            {{ $stars }} ({{ $score }})
                                        @else
                                            —
                                        @endif
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
