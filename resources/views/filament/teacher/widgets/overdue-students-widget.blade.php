<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-warning-500" />
                <span>طلاب متأخرون عن الخطة</span>
            </div>
        </x-slot>

        @php
            $overdueStudents = $this->getOverdueData();
        @endphp

        @if($overdueStudents->isEmpty())
            <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                <x-heroicon-o-check-circle class="w-12 h-12 mx-auto mb-2 text-success-500" />
                <p class="text-sm font-medium">جميع الطلاب على المسار الصحيح ✅</p>
            </div>
        @else
            <div class="space-y-3 max-h-80 overflow-y-auto">
                @foreach($overdueStudents as $student)
                    <div class="flex items-center justify-between p-3 bg-warning-50 dark:bg-warning-900/10 rounded-lg border border-warning-200 dark:border-warning-800">
                        <div>
                            <a href="{{ url('/teacher/student-report?student=' . $student['student_id']) }}"
   class="font-semibold text-sm text-primary-600 hover:text-primary-800 dark:text-primary-400 hover:underline">
    {{ $student['student_name'] }}
</a>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $student['plan_title'] ?? 'خطة أسبوعية' }}
                            </p>
                        </div>
                        <div class="text-left">
                            <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-bold rounded-full bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400">
                                <x-heroicon-m-arrow-trending-down class="w-3 h-3" />
                                متأخر {{ $student['deficit'] }} آية
                            </span>
                            <p class="text-xs text-gray-400 mt-1">
                                أنجز {{ $student['actual'] }} / {{ $student['expected'] }} متوقع
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-3 pt-3 border-t dark:border-gray-700">
                <p class="text-xs text-gray-400">
                    إجمالي المتأخرين: <span class="font-bold text-warning-600">{{ $overdueStudents->count() }}</span> طالب
                </p>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
