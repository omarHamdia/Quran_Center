<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-clock class="w-5 h-5 text-danger-500" />
                <span>طلاب بدون نشاط (7+ أيام)</span>
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
            <div class="space-y-2 max-h-96 overflow-y-auto">
                @foreach($inactiveStudents as $student)
                    @php
                        $days = $student['days_inactive'];
                        $isVeryInactive = $days === null || $days >= 14;
                        $bgClass = $isVeryInactive
                            ? 'bg-danger-50 dark:bg-danger-900/10 border-danger-200 dark:border-danger-800'
                            : 'bg-warning-50 dark:bg-warning-900/10 border-warning-200 dark:border-warning-800';
                        $badgeClass = $isVeryInactive
                            ? 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400'
                            : 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400';
                    @endphp
                    <div class="flex items-center justify-between p-3 rounded-lg border {{ $bgClass }}">
                        <div>
                            <a href="{{ url('/admin/students/' . $student['student_id']) }}"
                               class="font-semibold text-sm text-primary-600 hover:text-primary-800 dark:text-primary-400 hover:underline">
                                {{ $student['student_name'] }}
                            </a>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                المعلم: {{ $student['teacher_name'] }}
                            </p>
                        </div>
                        <div class="text-left">
                            <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-bold rounded-full {{ $badgeClass }}">
                                <x-heroicon-m-clock class="w-3 h-3" />
                                {{ $days !== null ? $days . ' يوم' : 'لا توجد جلسات' }}
                            </span>
                            <p class="text-xs text-gray-400 mt-1">
                                آخر جلسة: {{ $student['last_session_date'] }}
                            </p>
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
