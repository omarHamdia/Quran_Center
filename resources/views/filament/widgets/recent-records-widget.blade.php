<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-document-text class="w-5 h-5 text-primary-500" />
                <span>آخر سجلات التحفيظ</span>
            </div>
        </x-slot>

        @php
            $records = $this->getRecentRecordsData();
        @endphp

        @if($records->isEmpty())
            <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                <x-heroicon-o-book-open class="w-12 h-12 mx-auto mb-2 text-gray-300" />
                <p class="text-sm">لا توجد سجلات حديثة</p>
            </div>
        @else
            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="p-3 text-right font-semibold text-gray-700 dark:text-gray-200">الطالب</th>
                            <th class="p-3 text-right font-semibold text-gray-700 dark:text-gray-200">المعلم</th>
                            <th class="p-3 text-right font-semibold text-gray-700 dark:text-gray-200">السورة</th>
                            <th class="p-3 text-center font-semibold text-gray-700 dark:text-gray-200">الآيات</th>
                            <th class="p-3 text-center font-semibold text-gray-700 dark:text-gray-200">التقييم</th>
                            <th class="p-3 text-center font-semibold text-gray-700 dark:text-gray-200">النوع</th>
                            <th class="p-3 text-center font-semibold text-gray-700 dark:text-gray-200">التاريخ</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900">
                        @foreach($records as $record)
                            @php
                                $evalColors = [
                                    'ممتاز'          => 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 ring-emerald-200 dark:ring-emerald-700',
                                    'جيد جداً'       => 'bg-sky-50 dark:bg-sky-900/20 text-sky-700 dark:text-sky-300 ring-sky-200 dark:ring-sky-700',
                                    'جيد'            => 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 ring-blue-200 dark:ring-blue-700',
                                    'مقبول'          => 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 ring-amber-200 dark:ring-amber-700',
                                    'يحتاج مراجعة'  => 'bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-300 ring-rose-200 dark:ring-rose-700',
                                ];
                                $typeColors = [
                                    'حفظ جديد' => 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300',
                                    'مراجعة'   => 'bg-sky-50 dark:bg-sky-900/20 text-sky-700 dark:text-sky-300',
                                    'اختبار'   => 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300',
                                ];
                                $evalClass = $evalColors[$record['evaluation']] ?? 'bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400 ring-gray-200 dark:ring-gray-700';
                                $typeClass = $typeColors[$record['session_type']] ?? 'bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400';
                            @endphp
                            <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="p-3 font-semibold text-gray-800 dark:text-gray-100">{{ $record['student_name'] }}</td>
                                <td class="p-3 text-gray-500 dark:text-gray-400">{{ $record['teacher_name'] }}</td>
                                <td class="p-3 text-gray-600 dark:text-gray-300">{{ $record['surah_name'] }}</td>
                                <td class="p-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 font-semibold text-xs">
                                        {{ $record['ayahs_count'] }}
                                    </span>
                                </td>
                                <td class="p-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-full ring-1 font-medium text-xs {{ $evalClass }}">
                                        {{ $record['evaluation'] }}
                                    </span>
                                </td>
                                <td class="p-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $typeClass }}">
                                        {{ $record['session_type'] }}
                                    </span>
                                </td>
                                <td class="p-3 text-center text-xs text-gray-500 dark:text-gray-400">{{ $record['session_date'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
