<x-filament::section>
    <x-slot name="heading">
        <div class="flex items-center gap-2">
            <x-heroicon-o-trophy class="w-5 h-5 text-warning-500" />
            <span>أفضل الطلاب (آخر 7 أيام)</span>
        </div>
    </x-slot>

    <x-slot name="description">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
            <span class="text-sm text-gray-600 dark:text-gray-400">
                الترتيب: مجموع الآيات (الأكثر) ثم الأخطاء (الأقل) ثم الجلسات (الأكثر)
            </span>

            <span class="text-xs px-3 py-1 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 ring-1 ring-black/5 dark:ring-white/10">
                الفترة: {{ $periodLabel }}
            </span>
        </div>
    </x-slot>

    @php
        $students = collect($students ?? []);
        $top = $students->take(3);
    @endphp

    @if($students->count() === 0)
        <div class="text-center py-10 text-gray-600 dark:text-gray-400">
            لا يوجد طلاب مرتبطون بالمحفّظ حالياً.
        </div>
    @else
        {{-- ✅ Top 3 Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
            @foreach($top as $i => $s)
                @php
                    $medal = $i === 0 ? '🥇' : ($i === 1 ? '🥈' : '🥉');
                    $card = $i === 0 ? 'border-yellow-400 bg-yellow-50 dark:bg-yellow-500/10'
                          : ($i === 1 ? 'border-gray-300 bg-gray-50 dark:border-gray-600 dark:bg-gray-800'
                          : 'border-orange-400 bg-orange-50 dark:bg-orange-500/10');
                @endphp

                <div class="rounded-xl p-4 border-2 ring-1 ring-black/5 dark:ring-white/10 {{ $card }}">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-2xl">{{ $medal }}</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">#{{ $s['rank'] }}</div>
                    </div>

                    <div class="font-bold text-gray-950 dark:text-white truncate">
                        {{ $s['student_name'] }}
                    </div>

                    <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                        <div class="p-2 rounded-lg bg-white/70 dark:bg-gray-900/30 ring-1 ring-black/5 dark:ring-white/10">
                            <div class="text-sm font-bold text-gray-950 dark:text-white">{{ $s['total_ayahs'] }}</div>
                            <div class="text-[11px] text-gray-600 dark:text-gray-400">آيات</div>
                        </div>
                        <div class="p-2 rounded-lg bg-white/70 dark:bg-gray-900/30 ring-1 ring-black/5 dark:ring-white/10">
                            <div class="text-sm font-bold text-gray-950 dark:text-white">{{ $s['sessions_count'] }}</div>
                            <div class="text-[11px] text-gray-600 dark:text-gray-400">جلسات</div>
                        </div>
                        <div class="p-2 rounded-lg bg-white/70 dark:bg-gray-900/30 ring-1 ring-black/5 dark:ring-white/10">
                            <div class="text-sm font-bold text-gray-950 dark:text-white">{{ $s['total_mistakes'] }}</div>
                            <div class="text-[11px] text-gray-600 dark:text-gray-400">أخطاء</div>
                        </div>
                    </div>

                    @if(($s['total_ayahs'] ?? 0) === 0 && ($s['sessions_count'] ?? 0) === 0)
                        <div class="mt-3 text-xs text-warning-700 dark:text-warning-300">
                            لا يوجد تسميع ضمن الفترة — الطالب ظاهر لأنه من طلاب الحلقة.
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- ✅ Full Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="p-3 text-right font-semibold text-gray-950 dark:text-white">#</th>
                        <th class="p-3 text-right font-semibold text-gray-950 dark:text-white">الطالب</th>
                        <th class="p-3 text-center font-semibold text-gray-950 dark:text-white">آيات</th>
                        <th class="p-3 text-center font-semibold text-gray-950 dark:text-white">جلسات</th>
                        <th class="p-3 text-center font-semibold text-gray-950 dark:text-white">أخطاء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $s)
                        @php
                            $highlight = ($s['rank'] ?? 999) <= 3 ? 'bg-primary-50/60 dark:bg-primary-500/10' : '';
                            $rankIcon = ($s['rank'] ?? 0) === 1 ? '🥇' : (($s['rank'] ?? 0) === 2 ? '🥈' : (($s['rank'] ?? 0) === 3 ? '🥉' : null));
                        @endphp

                        <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50/70 dark:hover:bg-gray-800/50 transition-colors {{ $highlight }}">
                            <td class="p-3">
                                @if($rankIcon)
                                    <span class="text-lg">{{ $rankIcon }}</span>
                                @else
                                    <span class="w-7 h-7 inline-flex items-center justify-center rounded-full bg-gray-200 text-gray-900 dark:bg-gray-700 dark:text-white font-bold text-xs">
                                        {{ $s['rank'] }}
                                    </span>
                                @endif
                            </td>

                            <td class="p-3">
                                <div class="font-semibold text-gray-950 dark:text-white">{{ $s['student_name'] }}</div>
                                @if(!empty($s['phone']))
                                    <div class="text-xs text-gray-600 dark:text-gray-400">{{ $s['phone'] }}</div>
                                @endif
                            </td>

                            <td class="p-3 text-center">
                                <span class="inline-flex px-3 py-1 rounded-full bg-primary-50 dark:bg-primary-500/10 text-primary-700 dark:text-primary-300 ring-1 ring-black/5 dark:ring-white/10 font-semibold">
                                    {{ $s['total_ayahs'] }}
                                </span>
                            </td>

                            <td class="p-3 text-center">
                                <span class="inline-flex px-3 py-1 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 ring-1 ring-black/5 dark:ring-white/10 font-semibold">
                                    {{ $s['sessions_count'] }}
                                </span>
                            </td>

                            <td class="p-3 text-center">
                                @php
                                    $m = (int) ($s['total_mistakes'] ?? 0);
                                    $badge = $m <= 2 ? 'bg-success-50 dark:bg-success-500/10 text-success-700 dark:text-success-300'
                                          : ($m <= 5 ? 'bg-warning-50 dark:bg-warning-500/10 text-warning-700 dark:text-warning-300'
                                          : 'bg-danger-50 dark:bg-danger-500/10 text-danger-700 dark:text-danger-300');
                                @endphp
                                <span class="inline-flex px-3 py-1 rounded-full ring-1 ring-black/5 dark:ring-white/10 font-semibold {{ $badge }}">
                                    {{ $m }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Footer Summary --}}
        <div class="mt-4 p-3 rounded-xl bg-gray-50 dark:bg-gray-800 ring-1 ring-black/5 dark:ring-white/10 text-center">
            <span class="text-gray-950 dark:text-white font-semibold">
                إجمالي الطلاب: {{ $students->count() }} |
                مجموع الآيات: {{ $students->sum('total_ayahs') }} |
                مجموع الجلسات: {{ $students->sum('sessions_count') }} |
                مجموع الأخطاء: {{ $students->sum('total_mistakes') }}
            </span>
        </div>
    @endif
</x-filament::section>