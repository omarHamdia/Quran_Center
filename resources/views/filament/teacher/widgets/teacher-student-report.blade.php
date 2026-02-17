<x-filament-panels::page>
    <div class="space-y-6">

        {{-- ═══════════ بيانات الطالب ═══════════ --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-user class="w-5 h-5 text-primary-500" />
                    <span>بيانات الطالب</span>
                </div>
            </x-slot>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-500 dark:text-gray-400">الاسم</div>
                    <div class="font-bold text-lg">{{ $studentInfo['name'] }}</div>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-500 dark:text-gray-400">الهاتف</div>
                    <div class="font-medium" dir="ltr">{{ $studentInfo['phone'] }}</div>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-500 dark:text-gray-400">المستوى</div>
                    <x-filament::badge color="info">{{ $studentInfo['current_level'] }}</x-filament::badge>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-500 dark:text-gray-400">الأجزاء المحفوظة</div>
                    <div class="font-bold text-xl text-primary-600">{{ $studentInfo['memorized_juz'] }} جزء</div>
                </div>
            </div>
        </x-filament::section>

        {{-- ═══════════ إحصائيات سريعة ═══════════ --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="p-4 bg-gradient-to-br from-success-500 to-success-600 rounded-xl text-center text-white">
                <div class="text-3xl font-bold">{{ $weeklyStats['hifz_ayahs'] }}</div>
                <div class="text-sm opacity-90">آيات حفظ (الأسبوع)</div>
            </div>
            <div class="p-4 bg-gradient-to-br from-sky-500 to-sky-600 rounded-xl text-center text-white">
                <div class="text-3xl font-bold">{{ $weeklyStats['revision_ayahs'] }}</div>
                <div class="text-sm opacity-90">آيات مراجعة (الأسبوع)</div>
            </div>
            <div class="p-4 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl text-center text-white">
                <div class="text-3xl font-bold">{{ $weeklyStats['total_sessions'] }}</div>
                <div class="text-sm opacity-90">جلسات الأسبوع</div>
            </div>
            <div class="p-4 bg-gradient-to-br from-warning-500 to-warning-600 rounded-xl text-center text-white">
                <div class="text-3xl font-bold">{{ $monthSummary['sessions_count'] }}</div>
                <div class="text-sm opacity-90">جلسات الشهر</div>
            </div>
            <div class="p-4 bg-gradient-to-br from-violet-500 to-violet-600 rounded-xl text-center text-white">
                <div class="text-3xl font-bold">{{ $monthSummary['total_ayahs'] }}</div>
                <div class="text-sm opacity-90">إجمالي آيات الشهر</div>
            </div>
        </div>

        {{-- ═══════════ الخطة الحالية ═══════════ --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-calendar-days class="w-5 h-5 text-warning-500" />
                    <span>الخطة الحالية</span>
                </div>
            </x-slot>

            @if(!$planSummary['exists'])
                <div class="text-center py-6 text-gray-500">
                    <x-heroicon-o-calendar class="w-12 h-12 mx-auto mb-2 opacity-50" />
                    <p>لا توجد خطة نشطة لهذا الطالب</p>
                </div>
            @else
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <div class="text-xs text-gray-500">العنوان</div>
                        <div class="font-bold">{{ $planSummary['title'] }}</div>
                    </div>
                    <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <div class="text-xs text-gray-500">النوع</div>
                        <x-filament::badge>{{ $planSummary['type'] }}</x-filament::badge>
                    </div>
                    <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <div class="text-xs text-gray-500">الفترة</div>
                        <div class="text-sm">{{ $planSummary['date_range'] }}</div>
                    </div>
                    <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <div class="text-xs text-gray-500">النطاق</div>
                        <div class="text-sm">{{ $planSummary['from_surah'] }} ({{ $planSummary['from_ayah'] }}) → {{ $planSummary['to_surah'] }} ({{ $planSummary['to_ayah'] }})</div>
                    </div>
                </div>

                {{-- شريط التقدم --}}
                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="flex justify-between mb-2 text-sm">
                        <span>التقدم: {{ $planSummary['completed_ayahs'] }} / {{ $planSummary['total_ayahs'] }} آية</span>
                        <span class="font-bold">{{ number_format($planSummary['progress_percentage'], 1) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                        <div class="bg-success-500 h-3 rounded-full transition-all duration-500"
                             style="width: {{ min($planSummary['progress_percentage'], 100) }}%"></div>
                    </div>
                    <div class="mt-2 text-xs text-gray-500">
                        المتبقي: <span class="font-bold text-warning-600">{{ $planSummary['remaining_ayahs'] }}</span> آية
                    </div>
                </div>
            @endif
        </x-filament::section>

        {{-- ═══════════ ملخص الشهر ═══════════ --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-chart-bar class="w-5 h-5 text-info-500" />
                    <span>ملخص آخر 30 يوم</span>
                </div>
            </x-slot>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg text-center">
                    <div class="font-bold text-lg text-success-600">{{ $monthSummary['hifz_sessions'] }}</div>
                    <div class="text-xs text-gray-500">جلسات حفظ</div>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg text-center">
                    <div class="font-bold text-lg text-info-600">{{ $monthSummary['revision_sessions'] }}</div>
                    <div class="text-xs text-gray-500">جلسات مراجعة</div>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg text-center">
                    <div class="font-bold text-lg text-warning-600">{{ $monthSummary['test_sessions'] }}</div>
                    <div class="text-xs text-gray-500">اختبارات</div>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg text-center">
                    <div class="font-bold text-lg text-primary-600">{{ $monthSummary['total_ayahs'] }}</div>
                    <div class="text-xs text-gray-500">إجمالي الآيات</div>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg text-center">
                    <div class="font-bold text-lg text-danger-600">{{ $monthSummary['total_mistakes'] }}</div>
                    <div class="text-xs text-gray-500">إجمالي الأخطاء</div>
                </div>
            </div>
        </x-filament::section>

        {{-- ═══════════ سجلات التسميع ═══════════ --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-success-500" />
                    <span>آخر 15 سجل تسميع</span>
                </div>
            </x-slot>

            @if(count($recentRecords) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="p-3 text-right">التاريخ</th>
                                <th class="p-3 text-right">النوع</th>
                                <th class="p-3 text-right">السورة</th>
                                <th class="p-3 text-right">الآيات</th>
                                <th class="p-3 text-center">العدد</th>
                                <th class="p-3 text-center">التقييم</th>
                                <th class="p-3 text-center">الأخطاء</th>
                                <th class="p-3 text-right">ملاحظات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($recentRecords as $record)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="p-3 font-medium">{{ $record['date'] }}</td>
                                    <td class="p-3">
                                        <x-filament::badge :color="$record['session_type_color']" size="sm">
                                            {{ $record['session_type'] }}
                                        </x-filament::badge>
                                    </td>
                                    <td class="p-3">{{ $record['surah'] }}</td>
                                    <td class="p-3 text-gray-600 dark:text-gray-400">{{ $record['ayah_range'] }}</td>
                                    <td class="p-3 text-center">
                                        <x-filament::badge color="info" size="sm">{{ $record['ayahs_count'] }}</x-filament::badge>
                                    </td>
                                    <td class="p-3 text-center">
                                        <x-filament::badge :color="$record['evaluation_color']" size="sm">
                                            {{ $record['evaluation'] }}
                                        </x-filament::badge>
                                    </td>
                                    <td class="p-3 text-center">
                                        <x-filament::badge
                                            :color="$record['mistakes_count'] > 5 ? 'danger' : ($record['mistakes_count'] > 2 ? 'warning' : 'success')"
                                            size="sm">
                                            {{ $record['mistakes_count'] }}
                                        </x-filament::badge>
                                    </td>
                                    <td class="p-3 text-xs text-gray-500 max-w-xs truncate">
                                        {{ Str::limit($record['notes'], 40) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <x-heroicon-o-clipboard class="w-12 h-12 mx-auto mb-2 opacity-50" />
                    <p>لا توجد سجلات تسميع بعد</p>
                </div>
            @endif
        </x-filament::section>

    </div>
</x-filament-panels::page>
