<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-trophy class="w-5 h-5 text-warning-500" />
                <span>أفضل الطلاب هذا الشهر</span>
            </div>
        </x-slot>

        @php
            $students = $this->getStudentsData();
        @endphp

        @if($students->isEmpty())
            <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                <x-heroicon-o-face-frown class="w-12 h-12 mx-auto mb-2 text-gray-300" />
                <p class="text-sm">لا توجد بيانات لهذا الشهر</p>
            </div>
        @else
            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="p-3 text-right font-semibold text-gray-700 dark:text-gray-200">#</th>
                            <th class="p-3 text-right font-semibold text-gray-700 dark:text-gray-200">الطالب</th>
                            <th class="p-3 text-right font-semibold text-gray-700 dark:text-gray-200">المعلم</th>
                            <th class="p-3 text-center font-semibold text-gray-700 dark:text-gray-200">آيات الشهر</th>
                            <th class="p-3 text-center font-semibold text-gray-700 dark:text-gray-200">متوسط التقييم</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900">
                        @foreach($students as $student)
                            @php
                                $rank = $student['rank'];
                                $medal = $rank === 1 ? '🥇' : ($rank === 2 ? '🥈' : ($rank === 3 ? '🥉' : null));
                                $rowBg = $rank <= 3 ? 'bg-amber-50/50 dark:bg-amber-900/10' : '';
                            @endphp
                            <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors cursor-pointer {{ $rowBg }}"
                                onclick="window.location.href='{{ url('/admin/students/' . $student['student_id']) }}'">
                                <td class="p-3 align-middle">
                                    @if($medal)
                                        <span class="text-lg leading-none">{{ $medal }}</span>
                                    @else
                                        <span class="w-7 h-7 inline-flex items-center justify-center rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-bold text-xs">
                                            {{ $rank }}
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3 align-middle">
                                    <div class="font-semibold text-gray-800 dark:text-gray-100">{{ $student['student_name'] }}</div>
                                </td>
                                <td class="p-3 align-middle">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $student['teacher_name'] }}</div>
                                </td>
                                <td class="p-3 text-center align-middle">
                                    <span class="inline-flex px-2 py-1 rounded-full bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 ring-1 ring-emerald-200 dark:ring-emerald-700 font-semibold">
                                        {{ number_format($student['monthly_ayahs']) }}
                                    </span>
                                </td>
                                <td class="p-3 text-center align-middle">
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                                        {{ $student['avg_evaluation'] }}
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
