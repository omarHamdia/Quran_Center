<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-clock class="w-5 h-5 text-danger-500" />
                <span>طلاب بدون نشاط (3+ أيام)</span>
            </div>
        </x-slot>

        @php
            $inactiveStudents = $this->getInactiveData();
        @endphp

        @if($inactiveStudents->isEmpty())
            <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                <x-heroicon-o-face-smile class="w-12 h-12 mx-auto mb-2 text-success-500" />
                <p class="text-sm font-medium">جميع الطلاب نشطون 🎉</p>
            </div>
        @else
            <div class="space-y-3 max-h-80 overflow-y-auto">
                @foreach($inactiveStudents as $student)
                    <div class="flex items-center justify-between p-3 bg-danger-50 dark:bg-danger-900/10 rounded-lg border border-danger-200 dark:border-danger-800">
                        <div>
                            <a href="{{ url('/teacher/teacher-student-report?student=' . $student['student_id']) }}"
   class="font-semibold text-sm text-primary-600 hover:text-primary-800 dark:text-primary-400 hover:underline">
    {{ $student['student_name'] }}
</a>
                        </div>
                        <div class="text-left">
                            <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-bold rounded-full bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400">
                                <x-heroicon-m-clock class="w-3 h-3" />
                                {{ $student['last_activity'] }}
                            </span>
                            @if($student['last_activity_date'])
                                <p class="text-xs text-gray-400 mt-1">
                                    آخر نشاط: {{ $student['last_activity_date'] }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-3 pt-3 border-t dark:border-gray-700">
                <p class="text-xs text-gray-400">
                    إجمالي غير النشطين: <span class="font-bold text-danger-600">{{ $inactiveStudents->count() }}</span> طالب
                </p>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
