<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-danger-500" />
                <span>الخطط المتأخرة</span>
            </div>
        </x-slot>

        @php
            $plans = $this->getOverduePlansData();
        @endphp

        @if($plans->isEmpty())
            <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                <x-heroicon-o-check-circle class="w-12 h-12 mx-auto mb-2 text-success-500" />
                <p class="text-sm font-medium">لا توجد خطط متأخرة ✅</p>
            </div>
        @else
            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="w-full text-sm">
                    <thead class="bg-danger-50 dark:bg-danger-900/20">
                        <tr class="border-b border-danger-200 dark:border-danger-800">
                            <th class="p-3 text-right font-semibold text-gray-700 dark:text-gray-200">الطالب</th>
                            <th class="p-3 text-right font-semibold text-gray-700 dark:text-gray-200">المعلم</th>
                            <th class="p-3 text-right font-semibold text-gray-700 dark:text-gray-200">الخطة</th>
                            <th class="p-3 text-center font-semibold text-gray-700 dark:text-gray-200">التقدم</th>
                            <th class="p-3 text-center font-semibold text-gray-700 dark:text-gray-200">تاريخ الانتهاء</th>
                            <th class="p-3 text-center font-semibold text-danger-700 dark:text-danger-400">التأخر</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900">
                        @foreach($plans as $plan)
                            @php
                                $progress = (float) $plan['progress_percentage'];
                                $progressColor = $progress >= 75 ? 'bg-emerald-500'
                                    : ($progress >= 50 ? 'bg-amber-500' : 'bg-rose-500');
                            @endphp
                            <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-danger-50/50 dark:hover:bg-danger-900/10 transition-colors">
                                <td class="p-3 font-semibold text-gray-800 dark:text-gray-100">{{ $plan['student_name'] }}</td>
                                <td class="p-3 text-gray-500 dark:text-gray-400">{{ $plan['teacher_name'] }}</td>
                                <td class="p-3 text-gray-600 dark:text-gray-300 text-xs">{{ $plan['plan_title'] }}</td>
                                <td class="p-3">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            <div class="h-2 rounded-full {{ $progressColor }}" style="width: {{ min(100, $progress) }}%"></div>
                                        </div>
                                        <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 w-10 text-left">{{ number_format($progress, 0) }}%</span>
                                    </div>
                                </td>
                                <td class="p-3 text-center text-xs text-gray-500 dark:text-gray-400">{{ $plan['end_date'] }}</td>
                                <td class="p-3 text-center">
                                    <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-bold rounded-full bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400">
                                        <x-heroicon-m-clock class="w-3 h-3" />
                                        {{ $plan['days_overdue'] }} يوم
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3 pt-3 border-t dark:border-gray-700">
                <p class="text-xs text-gray-400">
                    إجمالي الخطط المتأخرة: <span class="font-bold text-danger-600">{{ $plans->count() }}</span> خطة
                </p>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
