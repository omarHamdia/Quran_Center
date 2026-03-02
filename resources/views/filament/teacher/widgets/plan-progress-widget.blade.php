<x-filament::section>
    <x-slot name="heading">
        <div class="flex items-center gap-2">
            <x-heroicon-o-clipboard-document-check class="w-5 h-5 text-primary-600 dark:text-primary-400" />
            <span>تقدم الطلاب في الخطط</span>
        </div>
    </x-slot>

    @php
        $plans = $this->getPlanData();
        $completedCount = $plans->where('status', 'completed')->count();
        $inProgressCount = $plans->where('status', 'in_progress')->count();
        $pendingCount = $plans->where('status', 'pending')->count();
    @endphp

    {{-- ملخص سريع --}}
    <div class="grid grid-cols-3 gap-3 mb-4">
        <div class="rounded-xl border border-green-200 dark:border-green-700 bg-green-50 dark:bg-green-900/20 p-3 text-center">
            <div class="text-xl font-bold text-green-700 dark:text-green-300">{{ $completedCount }}</div>
            <div class="text-xs font-medium text-green-600 dark:text-green-400">مكتملة</div>
        </div>
        <div class="rounded-xl border border-sky-200 dark:border-sky-700 bg-sky-50 dark:bg-sky-900/20 p-3 text-center">
            <div class="text-xl font-bold text-sky-700 dark:text-sky-300">{{ $inProgressCount }}</div>
            <div class="text-xs font-medium text-sky-600 dark:text-sky-400">قيد التنفيذ</div>
        </div>
        <div class="rounded-xl border border-amber-200 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 p-3 text-center">
            <div class="text-xl font-bold text-amber-700 dark:text-amber-300">{{ $pendingCount }}</div>
            <div class="text-xs font-medium text-amber-600 dark:text-amber-400">قيد الانتظار</div>
        </div>
    </div>

    @if($plans->count() > 0)
        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="p-3 text-right font-semibold text-gray-700 dark:text-gray-200">#</th>
                        <th class="p-3 text-right font-semibold text-gray-700 dark:text-gray-200">الطالب</th>
                        <th class="p-3 text-right font-semibold text-gray-700 dark:text-gray-200">الخطة</th>
                        <th class="p-3 text-center font-semibold text-gray-700 dark:text-gray-200">النوع</th>
                        <th class="p-3 text-center font-semibold text-gray-700 dark:text-gray-200">الحالة</th>
                        <th class="p-3 text-center font-semibold text-gray-700 dark:text-gray-200" style="min-width: 200px;">التقدم</th>
                        <th class="p-3 text-center font-semibold text-gray-700 dark:text-gray-200">محفوظ / إجمالي</th>
                        <th class="p-3 text-center font-semibold text-gray-700 dark:text-gray-200">متبقي</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900">
                    @foreach($plans as $index => $plan)
                        @php
                            $progressColor = match(true) {
                                $plan['progress'] >= 100 => 'bg-green-500',
                                $plan['progress'] >= 75 => 'bg-emerald-500',
                                $plan['progress'] >= 50 => 'bg-sky-500',
                                $plan['progress'] >= 25 => 'bg-amber-500',
                                default => 'bg-rose-500',
                            };
                            $rowBg = $plan['status'] === 'completed' ? 'bg-green-50/30 dark:bg-green-900/10' : '';
                        @endphp
                        <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50/70 dark:hover:bg-gray-800/40 transition-colors {{ $rowBg }}">
                            <td class="p-3">
                                @if($plan['status'] === 'completed')
                                    <span class="text-lg">✅</span>
                                @else
                                    <span class="font-bold text-gray-700 dark:text-gray-200">{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td class="p-3">
                                <a href="{{ url('/teacher/student-report?student=' . $plan['student_id']) }}"
                                   class="font-semibold text-primary-600 hover:text-primary-800 dark:text-primary-400 hover:underline">
                                    {{ $plan['student_name'] }}
                                </a>
                            </td>
                            <td class="p-3 text-gray-700 dark:text-gray-200">
                                {{ $plan['title'] }}
                            </td>
                            <td class="p-3 text-center">
                                <x-filament::badge color="gray">{{ $plan['plan_type'] }}</x-filament::badge>
                            </td>
                            <td class="p-3 text-center">
                                <x-filament::badge :color="$plan['status_color']">{{ $plan['status_label'] }}</x-filament::badge>
                            </td>
                            <td class="p-3">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1">
                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                                            <div class="{{ $progressColor }} h-3 rounded-full transition-all duration-500"
                                                 style="width: {{ min($plan['progress'], 100) }}%"></div>
                                        </div>
                                    </div>
                                    <span class="font-bold text-sm text-gray-700 dark:text-gray-200 min-w-[40px] text-left">{{ $plan['progress'] }}%</span>
                                </div>
                            </td>
                            <td class="p-3 text-center">
                                <span class="font-semibold text-green-700 dark:text-green-300">{{ $plan['completed_ayahs'] }}</span>
                                <span class="text-gray-400 dark:text-gray-500"> / </span>
                                <span class="text-gray-700 dark:text-gray-200">{{ $plan['total_ayahs'] }}</span>
                            </td>
                            <td class="p-3 text-center">
                                @if($plan['remaining_ayahs'] > 0)
                                    <x-filament::badge color="warning">{{ $plan['remaining_ayahs'] }} آية</x-filament::badge>
                                @else
                                    <x-filament::badge color="success">مكتمل</x-filament::badge>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4 p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-center">
            <span class="text-gray-700 dark:text-gray-200 font-semibold">
                إجمالي الخطط: {{ $plans->count() }} |
                مكتملة: {{ $completedCount }} |
                مجموع الآيات المحفوظة: {{ $plans->sum('completed_ayahs') }} |
                مجموع المتبقية: {{ $plans->sum('remaining_ayahs') }}
            </span>
        </div>
    @else
        <div class="text-center py-10">
            <x-heroicon-o-clipboard class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-500" />
            <p class="mt-2 text-gray-600 dark:text-gray-400">لا توجد خطط حفظ حالياً</p>
        </div>
    @endif
</x-filament::section>