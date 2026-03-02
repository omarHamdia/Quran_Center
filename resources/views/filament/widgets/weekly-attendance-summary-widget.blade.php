<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-calendar-days class="w-5 h-5 text-info-500" />
                <span>ملخص الحضور - آخر 7 أيام</span>
            </div>
        </x-slot>

        @php
            $days = $this->getAttendanceData();
        @endphp

        @if(empty($days))
            <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                <x-heroicon-o-calendar class="w-12 h-12 mx-auto mb-2 text-gray-300" />
                <p class="text-sm">لا توجد بيانات حضور</p>
            </div>
        @else
            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="p-3 text-right font-semibold text-gray-700 dark:text-gray-200">اليوم</th>
                            <th class="p-3 text-right font-semibold text-gray-700 dark:text-gray-200">التاريخ</th>
                            <th class="p-3 text-center font-semibold text-emerald-700 dark:text-emerald-400">حاضر</th>
                            <th class="p-3 text-center font-semibold text-rose-700 dark:text-rose-400">غائب</th>
                            <th class="p-3 text-center font-semibold text-amber-700 dark:text-amber-400">متأخر</th>
                            <th class="p-3 text-center font-semibold text-sky-700 dark:text-sky-400">بعذر</th>
                            <th class="p-3 text-center font-semibold text-gray-700 dark:text-gray-200">نسبة الحضور</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900">
                        @foreach($days as $day)
                            @php
                                $rate = (int) $day['attendance_rate'];
                                $rateColor = $rate >= 80
                                    ? 'bg-emerald-500'
                                    : ($rate >= 60 ? 'bg-amber-500' : 'bg-rose-500');
                            @endphp
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="p-3 font-medium text-gray-800 dark:text-gray-100">{{ $day['day_name'] }}</td>
                                <td class="p-3 text-gray-500 dark:text-gray-400 text-xs">{{ $day['date'] }}</td>
                                <td class="p-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 font-semibold text-xs">
                                        {{ $day['present'] }}
                                    </span>
                                </td>
                                <td class="p-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-300 font-semibold text-xs">
                                        {{ $day['absent'] }}
                                    </span>
                                </td>
                                <td class="p-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 font-semibold text-xs">
                                        {{ $day['late'] }}
                                    </span>
                                </td>
                                <td class="p-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-sky-50 dark:bg-sky-900/20 text-sky-700 dark:text-sky-300 font-semibold text-xs">
                                        {{ $day['excused'] }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            <div class="h-2 rounded-full {{ $rateColor }}" style="width: {{ $rate }}%"></div>
                                        </div>
                                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 w-10 text-left">{{ $rate }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
