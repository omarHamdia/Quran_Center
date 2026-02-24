<x-filament::section>
    <x-slot name="heading">
        <div class="flex items-center gap-2">
            <x-heroicon-o-calendar-days class="w-5 h-5 text-primary-600 dark:text-primary-400" />
            <span>تسميع اليوم - {{ now()->translatedFormat('l j F Y') }}</span>
        </div>
    </x-slot>

    <x-slot name="description">
        مرتب حسب عدد الآيات (الأكثر) ثم الصفحات (الأكثر) ثم الأخطاء (الأقل) — مع إخفاء عمود الأخطاء من الجدول
    </x-slot>

    @php
        $todayRecords = $this->getTodayData();
        $summary = $this->getTodaySummary();

        // ✅ ترتيب: آيات أكثر، صفحات أكثر، أخطاء أقل
        $sortedRecords = $todayRecords
            ->sortBy(fn ($r) => (int) ($r['mistakes'] ?? 0))
            ->sortByDesc(fn ($r) => (int) ($r['pages_count'] ?? 0))
            ->sortByDesc(fn ($r) => (int) ($r['ayahs_count'] ?? 0))
            ->values();

        $sessionColors = [
            'hifz' => 'success',
            'revision' => 'info',
            'test' => 'gray',
        ];

        $sessionNames = [
            'hifz' => 'حفظ',
            'revision' => 'مراجعة',
            'test' => 'اختبار',
        ];

        $evalColors = [
            'excellent' => 'success',
            'very_good' => 'info',
            'good' => 'primary',
            'acceptable' => 'warning',
            'needs_review' => 'danger',
        ];

        $evalNames = [
            'excellent' => 'ممتاز',
            'very_good' => 'جيد جداً',
            'good' => 'جيد',
            'acceptable' => 'مقبول',
            'needs_review' => 'ضعيف',
        ];
    @endphp

    {{-- ✅ Summary --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 text-center">
            <div class="text-2xl font-bold text-gray-950 dark:text-white">{{ $summary['total_sessions'] }}</div>
            <div class="text-xs text-gray-600 dark:text-gray-400">جلسات</div>
        </div>

        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 text-center">
            <div class="text-2xl font-bold text-gray-950 dark:text-white">{{ $summary['hifz_ayahs'] }}</div>
            <div class="text-xs text-gray-600 dark:text-gray-400">آيات حفظ</div>
        </div>

        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 text-center">
            <div class="text-2xl font-bold text-gray-950 dark:text-white">{{ $summary['rev_ayahs'] }}</div>
            <div class="text-xs text-gray-600 dark:text-gray-400">آيات مراجعة</div>
        </div>

        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 text-center">
            <div class="text-2xl font-bold text-gray-950 dark:text-white">{{ $summary['students_count'] }}</div>
            <div class="text-xs text-gray-600 dark:text-gray-400">طلاب شاركوا</div>
        </div>
    </div>

    @if($sortedRecords->count() > 0)
        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="p-3 text-right font-semibold text-gray-950 dark:text-white">#</th>
                        <th class="p-3 text-right font-semibold text-gray-950 dark:text-white">الطالب</th>
                        <th class="p-3 text-center font-semibold text-gray-950 dark:text-white">السورة</th>
                        <th class="p-3 text-center font-semibold text-gray-950 dark:text-white">الآيات</th>
                        <th class="p-3 text-center font-semibold text-gray-950 dark:text-white">عدد الآيات</th>
                        <th class="p-3 text-center font-semibold text-gray-950 dark:text-white">النوع</th>
                        <th class="p-3 text-center font-semibold text-gray-950 dark:text-white">التقييم</th>
                        <th class="p-3 text-center font-semibold text-gray-950 dark:text-white">إجراءات</th>
                    </tr>
                </thead>

                <tbody class="bg-white dark:bg-gray-900">
                    @foreach($sortedRecords as $index => $record)
                        @php
                            $rankIcon = $index === 0 ? '🥇' : ($index === 1 ? '🥈' : ($index === 2 ? '🥉' : null));
                            $sessionType = $record['session_type'] ?? 'test';
                            $evaluation  = $record['evaluation'] ?? null;

                            $rowBg = $index < 3 ? 'bg-primary-50/40 dark:bg-primary-500/10' : '';

                            // تقرير الطالب (صفحة)
                            $studentReportUrl = route('filament.admin.pages.student-report', ['student' => $record['student_id']]);

                            // PDF: نفس التقرير لكن بنمط تحميل PDF (مثال)
                            // إما تعمل route خاص للـ PDF أو controller يطلع PDF
                            $studentPdfUrl = route('teacher.today.pdf', ['student' => $record['student_id']]);
                        @endphp

                        <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50/70 dark:hover:bg-gray-800/40 transition-colors {{ $rowBg }}">
                            <td class="p-3">
                                @if($rankIcon)
                                    <span class="text-xl">{{ $rankIcon }}</span>
                                @else
                                    <span class="font-bold text-gray-950 dark:text-white">{{ $index + 1 }}</span>
                                @endif
                            </td>

                            <td class="p-3">
                                <div class="font-semibold text-gray-950 dark:text-white">{{ $record['student_name'] ?? '-' }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $record['student_phone'] ?? '-' }}</div>
                            </td>

                            <td class="p-3 text-center">
                                <x-filament::badge color="info">{{ $record['surah_name'] ?? '-' }}</x-filament::badge>
                            </td>

                            <td class="p-3 text-center text-gray-950 dark:text-white">
                                {{ $record['from_ayah'] ?? '-' }} - {{ $record['to_ayah'] ?? '-' }}
                            </td>

                            <td class="p-3 text-center">
                                <x-filament::badge color="primary">{{ (int)($record['ayahs_count'] ?? 0) }}</x-filament::badge>
                            </td>

                            <td class="p-3 text-center">
                                <x-filament::badge :color="$sessionColors[$sessionType] ?? 'gray'">
                                    {{ $sessionNames[$sessionType] ?? 'اختبار' }}
                                </x-filament::badge>
                            </td>

                            <td class="p-3 text-center">
                                <x-filament::badge :color="$evalColors[$evaluation] ?? 'gray'">
                                    {{ $evalNames[$evaluation] ?? '-' }}
                                </x-filament::badge>
                            </td>

                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <x-filament::button
                                        size="sm"
                                        color="primary"
                                        icon="heroicon-o-chart-bar"
                                        tag="a"
                                        :href="$studentReportUrl"
                                    >
                                        تقرير
                                    </x-filament::button>

                                    <x-filament::button
                                        size="sm"
                                        color="gray"
                                        icon="heroicon-o-arrow-down-tray"
                                        tag="a"
                                        :href="$studentPdfUrl"
                                        target="_blank"
                                    >
                                        PDF
                                    </x-filament::button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4 p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-center">
            <span class="text-gray-950 dark:text-white font-semibold">
                إجمالي: {{ $sortedRecords->count() }} جلسة | مجموع الآيات: {{ $sortedRecords->sum('ayahs_count') }}
            </span>
        </div>
    @else
        <div class="text-center py-10">
            <x-heroicon-o-inbox class="w-12 h-12 mx-auto text-gray-400" />
            <p class="mt-2 text-gray-600 dark:text-gray-400">لا يوجد تسميع اليوم بعد</p>
        </div>
    @endif
</x-filament::section>