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
                <div class="text-3xl font-bold">{{ $weeklyStats['hifz_ayahs'] ?? 0 }}</div>
                <div class="text-sm opacity-90">آيات حفظ (الأسبوع)</div>
            </div>
            <div class="p-4 bg-gradient-to-br from-sky-500 to-sky-600 rounded-xl text-center text-white">
                <div class="text-3xl font-bold">{{ $weeklyStats['revision_ayahs'] ?? 0 }}</div>
                <div class="text-sm opacity-90">آيات مراجعة (الأسبوع)</div>
            </div>
            <div class="p-4 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl text-center text-white">
                <div class="text-3xl font-bold">{{ $weeklyStats['total_sessions'] ?? 0 }}</div>
                <div class="text-sm opacity-90">جلسات الأسبوع</div>
            </div>
            <div class="p-4 bg-gradient-to-br from-warning-500 to-warning-600 rounded-xl text-center text-white">
                <div class="text-3xl font-bold">{{ $monthSummary['sessions_count'] ?? 0 }}</div>
                <div class="text-sm opacity-90">جلسات الشهر</div>
            </div>
            <div class="p-4 bg-gradient-to-br from-violet-500 to-violet-600 rounded-xl text-center text-white">
                <div class="text-3xl font-bold">{{ $monthSummary['total_ayahs'] ?? 0 }}</div>
                <div class="text-sm opacity-90">آيات الشهر</div>
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

            @if($planSummary['exists'] ?? false)
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div class="text-sm text-gray-500">العنوان</div>
                            <div class="font-bold">{{ $planSummary['title'] }}</div>
                        </div>
                        <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div class="text-sm text-gray-500">النوع</div>
                            <x-filament::badge>{{ $planSummary['type'] }}</x-filament::badge>
                        </div>
                        <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div class="text-sm text-gray-500">الفترة</div>
                            <div class="font-medium">{{ $planSummary['date_range'] }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <div class="text-sm text-blue-600 dark:text-blue-400">النطاق</div>
                            <div class="font-bold text-lg">
                                من {{ $planSummary['from_surah'] }} (آية {{ $planSummary['from_ayah'] }})
                                إلى {{ $planSummary['to_surah'] }} (آية {{ $planSummary['to_ayah'] }})
                            </div>
                            @if(($planSummary['from_page'] ?? '-') !== '-')
                                <div class="text-sm text-gray-600 mt-1">
                                    الصفحات: {{ $planSummary['from_page'] }} - {{ $planSummary['to_page'] }}
                                </div>
                            @endif
                        </div>

                        <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <div class="text-sm text-green-600 dark:text-green-400">التقدم</div>
                            <div class="flex items-center gap-4 mt-2">
                                <div class="flex-1">
                                    <div class="w-full bg-gray-200 rounded-full h-4">
                                        <div class="bg-green-500 h-4 rounded-full transition-all"
                                             style="width: {{ $planSummary['progress_percentage'] ?? 0 }}%"></div>
                                    </div>
                                </div>
                                <div class="font-bold text-xl text-green-600">{{ $planSummary['progress_percentage'] ?? 0 }}%</div>
                            </div>
                            <div class="flex justify-between mt-2 text-sm">
                                <span class="text-green-600">{{ $planSummary['completed_ayahs'] ?? 0 }} آية محفوظة</span>
                                <span class="text-orange-600">{{ $planSummary['remaining_ayahs'] ?? 0 }} آية متبقية</span>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                    <x-heroicon-o-calendar class="w-12 h-12 mx-auto mb-2 opacity-50" />
                    <p class="text-sm font-medium">لا توجد خطة نشطة حالياً</p>
                </div>
            @endif
        </x-filament::section>

        {{-- ═══════════ ملخص آخر 30 يوم ═══════════ --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-chart-bar class="w-5 h-5 text-info-500" />
                    <span>ملخص آخر 30 يوم</span>
                </div>
            </x-slot>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg text-center">
                    <div class="text-2xl font-bold text-primary-600">{{ $monthSummary['sessions_count'] ?? 0 }}</div>
                    <div class="text-xs text-gray-500">إجمالي الجلسات</div>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg text-center">
                    <div class="text-2xl font-bold text-success-600">{{ $monthSummary['hifz_ayahs'] ?? 0 }}</div>
                    <div class="text-xs text-gray-500">آيات حفظ</div>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg text-center">
                    <div class="text-2xl font-bold text-sky-600">{{ $monthSummary['revision_ayahs'] ?? 0 }}</div>
                    <div class="text-xs text-gray-500">آيات مراجعة</div>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg text-center">
                    <div class="text-2xl font-bold text-warning-600">{{ $monthSummary['avg_mistakes'] ?? 0 }}</div>
                    <div class="text-xs text-gray-500">متوسط الأخطاء</div>
                </div>
            </div>
        </x-filament::section>

        {{-- ═══════════ آخر سجلات التسميع ═══════════ --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-success-500" />
                    <span>آخر 15 جلسة تسميع</span>
                </div>
            </x-slot>

            @if(count($recentRecords) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="p-3 text-right font-semibold">التاريخ</th>
                                <th class="p-3 text-right font-semibold">النوع</th>
                                <th class="p-3 text-right font-semibold">السورة</th>
                                <th class="p-3 text-right font-semibold">الآيات</th>
                                <th class="p-3 text-center font-semibold">العدد</th>
                                <th class="p-3 text-center font-semibold">التقييم</th>
</tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($recentRecords as $record)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="p-3 font-medium">{{ $record['date'] }}</td>
                                    <td class="p-3">
                                        <x-filament::badge :color="$record['type_color']" size="sm">
                                            {{ $record['type_label'] }}
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
                                        <x-filament::badge :color="$record['mistakes_count'] > 5 ? 'danger' : ($record['mistakes_count'] > 2 ? 'warning' : 'success')" size="sm">
                                            {{ $record['mistakes_count'] }}
                                        </x-filament::badge>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                    <x-heroicon-o-clipboard class="w-12 h-12 mx-auto mb-2 opacity-50" />
                    <p class="text-sm font-medium">لا توجد سجلات تسميع بعد</p>
                </div>
            @endif
        </x-filament::section>

    </div>
</x-filament-panels::page>