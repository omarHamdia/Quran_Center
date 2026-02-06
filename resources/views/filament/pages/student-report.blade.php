<x-filament-panels::page>
    <div class="space-y-6">
        {{-- بطاقة معلومات الطالب --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-user class="w-5 h-5" />
                    <span>معلومات الطالب</span>
                </div>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-500 dark:text-gray-400">الاسم</div>
                    <div class="font-bold text-lg">{{ $studentInfo['name'] }}</div>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-500 dark:text-gray-400">المحفظ</div>
                    <div class="font-bold">{{ $studentInfo['teacher'] }}</div>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-500 dark:text-gray-400">المستوى</div>
                    <x-filament::badge color="info">{{ $studentInfo['current_level'] }}</x-filament::badge>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-500 dark:text-gray-400">الحالة</div>
                    <x-filament::badge color="success">{{ $studentInfo['status'] }}</x-filament::badge>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-500 dark:text-gray-400">رقم الهاتف</div>
                    <div class="font-medium" dir="ltr">{{ $studentInfo['phone'] }}</div>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-500 dark:text-gray-400">تاريخ التسجيل</div>
                    <div class="font-medium">{{ $studentInfo['enrollment_date'] }}</div>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-500 dark:text-gray-400">الأجزاء المحفوظة</div>
                    <div class="font-bold text-xl text-primary-600">{{ $studentInfo['memorized_juz'] }} جزء</div>
                </div>
            </div>
        </x-filament::section>

        {{-- ملخص الخطة الحالية --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-calendar-days class="w-5 h-5" />
                    <span>الخطة الحالية</span>
                </div>
            </x-slot>

            @if($planSummary['exists'])
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
                            @if($planSummary['from_page'] !== '-')
                                <div class="text-sm text-gray-600">
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
                                             style="width: {{ $planSummary['progress_percentage'] }}%"></div>
                                    </div>
                                </div>
                                <div class="font-bold text-xl text-green-600">{{ $planSummary['progress_percentage'] }}%</div>
                            </div>
                            <div class="flex justify-between mt-2 text-sm">
                                <span class="text-green-600">✓ {{ $planSummary['completed_ayahs'] }} آية محفوظة</span>
                                <span class="text-orange-600">⏳ {{ $planSummary['remaining_ayahs'] }} آية متبقية</span>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <x-heroicon-o-calendar class="w-12 h-12 mx-auto mb-2 opacity-50" />
                    <p>لا توجد خطة نشطة حالياً</p>
                </div>
            @endif
        </x-filament::section>

        {{-- ملخص آخر 30 يوم --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-chart-bar class="w-5 h-5" />
                    <span>ملخص آخر 30 يوم</span>
                </div>
            </x-slot>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="p-4 bg-primary-50 dark:bg-primary-900/20 rounded-lg text-center">
                    <div class="text-3xl font-bold text-primary-600">{{ $monthSummary['sessions_count'] }}</div>
                    <div class="text-sm text-gray-500">إجمالي الجلسات</div>
                </div>
                <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg text-center">
                    <div class="text-3xl font-bold text-green-600">{{ $monthSummary['total_ayahs'] }}</div>
                    <div class="text-sm text-gray-500">إجمالي الآيات</div>
                </div>
                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-center">
                    <div class="text-xl font-bold text-blue-600">{{ $monthSummary['average_evaluation'] }}</div>
                    <div class="text-sm text-gray-500">متوسط التقييم</div>
                </div>
                <div class="p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg text-center">
                    <div class="text-3xl font-bold text-orange-600">{{ $monthSummary['gap_days'] }}</div>
                    <div class="text-sm text-gray-500">أيام بدون تسميع</div>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4 mt-4">
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg text-center">
                    <div class="font-bold text-success-600">{{ $monthSummary['hifz_sessions'] }}</div>
                    <div class="text-xs text-gray-500">جلسات حفظ</div>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg text-center">
                    <div class="font-bold text-info-600">{{ $monthSummary['revision_sessions'] }}</div>
                    <div class="text-xs text-gray-500">جلسات مراجعة</div>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg text-center">
                    <div class="font-bold text-warning-600">{{ $monthSummary['test_sessions'] }}</div>
                    <div class="text-xs text-gray-500">اختبارات</div>
                </div>
            </div>
        </x-filament::section>

        {{-- آخر 10 سجلات تسميع --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-clipboard-document-list class="w-5 h-5" />
                    <span>آخر 10 سجلات تسميع</span>
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
                                <th class="p-3 text-right">الصفحات</th>
                                <th class="p-3 text-right">عدد الآيات</th>
                                <th class="p-3 text-right">التقييم</th>
                                <th class="p-3 text-right">الأخطاء</th>
                                <th class="p-3 text-right">ملاحظات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentRecords as $record)
                                <tr class="border-b dark:border-gray-700">
                                    <td class="p-3">{{ $record['date'] }}</td>
                                    <td class="p-3">
                                        <x-filament::badge size="sm">{{ $record['session_type'] }}</x-filament::badge>
                                    </td>
                                    <td class="p-3 font-medium">{{ $record['surah'] }}</td>
                                    <td class="p-3">{{ $record['ayah_range'] }}</td>
                                    <td class="p-3">{{ $record['page_range'] }}</td>
                                    <td class="p-3">
                                        <x-filament::badge color="info">{{ $record['ayahs_count'] }}</x-filament::badge>
                                    </td>
                                    <td class="p-3">
                                        <x-filament::badge :color="$record['evaluation_color']">
                                            {{ $record['evaluation'] }}
                                        </x-filament::badge>
                                    </td>
                                    <td class="p-3">
                                        @if($record['mistakes_count'] > 0)
                                            <x-filament::badge color="danger">{{ $record['mistakes_count'] }}</x-filament::badge>
                                        @else
                                            <x-filament::badge color="success">0</x-filament::badge>
                                        @endif
                                    </td>
                                    <td class="p-3 text-xs text-gray-500 max-w-xs truncate">
                                        {{ Str::limit($record['notes'], 30) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <x-heroicon-o-document class="w-12 h-12 mx-auto mb-2 opacity-50" />
                    <p>لا توجد سجلات تسميع بعد</p>
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>