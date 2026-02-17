<x-filament-panels::page>
    @php
        $students = $this->getStudents();
        $weekDays = $this->getWeekDays();
        $stats = $this->getAttendanceStats();
    @endphp

    <div class="space-y-6">
        {{-- اختيار الأسبوع --}}
        <x-filament::section>
            <div class="flex flex-wrap items-center gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">اختر الأسبوع</label>
                    <input type="week" 
                           wire:model.live="selectedWeek" 
                           class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    من {{ $this->weekStart->format('Y/m/d') }} إلى {{ $this->weekEnd->format('Y/m/d') }}
                </div>
            </div>
        </x-filament::section>

        {{-- إحصائيات سريعة --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="p-4 bg-blue-100 dark:bg-blue-900/30 rounded-xl text-center">
                <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['total_students'] }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">إجمالي الطلاب</div>
            </div>
            <div class="p-4 bg-green-100 dark:bg-green-900/30 rounded-xl text-center">
                <div class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $stats['total_sessions'] }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">جلسات التسميع</div>
            </div>
            <div class="p-4 bg-red-100 dark:bg-red-900/30 rounded-xl text-center">
                <div class="text-3xl font-bold text-red-600 dark:text-red-400">{{ $stats['absent_count'] }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">حالات الغياب</div>
            </div>
            <div class="p-4 bg-purple-100 dark:bg-purple-900/30 rounded-xl text-center">
                <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ $stats['attendance_rate'] }}%</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">نسبة الحضور</div>
            </div>
        </div>

        {{-- جدول الحضور --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-table-cells class="w-5 h-5 text-primary-500" />
                    <span>جدول الحضور والغياب</span>
                </div>
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="p-3 text-right text-gray-700 dark:text-gray-300 sticky right-0 bg-gray-100 dark:bg-gray-800">الطالب</th>
                            @foreach($weekDays as $day)
                                <th class="p-3 text-center text-gray-700 dark:text-gray-300 min-w-[80px]">
                                    <div>{{ $day['short'] }}</div>
                                    <div class="text-xs text-gray-500">{{ $day['date']->format('m/d') }}</div>
                                </th>
                            @endforeach
                            <th class="p-3 text-center text-gray-700 dark:text-gray-300">الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($students as $student)
                            @php
                                $studentRecords = $student->memorizationRecords;
                                $presentCount = 0;
                                $absentCount = 0;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="p-3 sticky right-0 bg-white dark:bg-gray-900">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $student->user->name }}</div>
                                </td>
                                @foreach($weekDays as $day)
                                    @php
                                        $record = $studentRecords->firstWhere('session_date', $day['date']->format('Y-m-d'));
                                        $status = $record?->status;
                                        if ($status === 'completed') $presentCount++;
                                        if (in_array($status, ['absent', 'excused'])) $absentCount++;
                                    @endphp
                                    <td class="p-3 text-center">
                                        @if($status === 'completed')
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400">
                                                ✓
                                            </span>
                                        @elseif($status === 'absent')
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
                                                ✗
                                            </span>
                                        @elseif($status === 'excused')
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                                                ع
                                            </span>
                                        @elseif($record)
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                                                •
                                            </span>
                                        @else
                                            <span class="text-gray-300 dark:text-gray-600">-</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="p-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <span class="text-green-600 dark:text-green-400 font-medium">{{ $presentCount }}</span>
                                        <span class="text-gray-400">/</span>
                                        <span class="text-red-600 dark:text-red-400 font-medium">{{ $absentCount }}</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($weekDays) + 2 }}" class="p-8 text-center text-gray-500">
                                    لا يوجد طلاب
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- مفتاح الرموز --}}
            <div class="mt-4 flex flex-wrap gap-4 text-sm">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-100 text-green-600 text-xs">✓</span>
                    <span class="text-gray-600 dark:text-gray-400">حاضر</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-600 text-xs">✗</span>
                    <span class="text-gray-600 dark:text-gray-400">غائب</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-100 text-amber-600 text-xs">ع</span>
                    <span class="text-gray-600 dark:text-gray-400">غياب بعذر</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-600 text-xs">•</span>
                    <span class="text-gray-600 dark:text-gray-400">جلسة أخرى</span>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
