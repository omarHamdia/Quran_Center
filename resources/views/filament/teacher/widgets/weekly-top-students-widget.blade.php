<x-filament::section class="antialiased">
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
        {{-- Top 3 Cards - improved borders & dark support --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
            @foreach($top as $i => $s)
                @php
                    $medal = $i === 0 ? '🥇' : ($i === 1 ? '🥈' : '🥉');

                    // ألوان واضحة للوضعين (light / dark)
                    if ($i === 0) {
                        $cardBg = 'bg-amber-50 dark:bg-amber-900/10';
                        $cardBorder = 'border-amber-400 dark:border-amber-500';
                    } elseif ($i === 1) {
                        // CENTER (المركز الثاني) — درجات Slate لئلا تكون فاتحة جداً في الليل
                        $cardBg = 'bg-slate-50 dark:bg-slate-800/10';
                        $cardBorder = 'border-slate-300 dark:border-slate-600';
                    } else {
                        $cardBg = 'bg-orange-50 dark:bg-orange-900/10';
                        $cardBorder = 'border-orange-300 dark:border-orange-500';
                    }

                    // داخل الصناديق الصغيرة داخل البطاقة
                    $innerBox = 'p-2 rounded-lg bg-white dark:bg-gray-900/10 ring-1 ring-black/5 dark:ring-white/6';
                    $titleClass = 'font-bold text-gray-800 dark:text-gray-100 truncate';
                @endphp

                <div class="rounded-xl p-4 border-2 {{ $cardBorder }} ring-1 ring-black/5 dark:ring-white/10 {{ $cardBg }} shadow-sm dark:shadow-none">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 flex items-center justify-center rounded-full bg-white dark:bg-gray-900/30 ring-1 ring-black/5 dark:ring-white/10 text-sm">
                                {{ $medal }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">#{{ $s['rank'] }}</div>
                        </div>
                        {{-- مسافة صغيرة جهة اليمين لوضع التاريخ أو أيقونة لاحقاً --}}
                    </div>

                    <div class="{{ $titleClass }} mb-3">
                        {{ $s['student_name'] }}
                    </div>

                    <div class="mt-1 grid grid-cols-3 gap-3 text-center">
                        <div class="{{ $innerBox }}">
                            <div class="text-sm font-bold text-emerald-700 dark:text-emerald-300 leading-tight">{{ $s['total_ayahs'] }}</div>
                            <div class="text-[11px] text-gray-500 dark:text-gray-400">آيات</div>
                        </div>

                        <div class="{{ $innerBox }}">
                            <div class="text-sm font-bold text-sky-700 dark:text-sky-300 leading-tight">{{ $s['sessions_count'] }}</div>
                            <div class="text-[11px] text-gray-500 dark:text-gray-400">جلسات</div>
                        </div>

                        <div class="{{ $innerBox }}">
                            <div class="text-sm font-bold text-rose-700 dark:text-rose-300 leading-tight">{{ $s['total_mistakes'] }}</div>
                            <div class="text-[11px] text-gray-500 dark:text-gray-400">أخطاء</div>
                        </div>
                    </div>

                    @if(($s['total_ayahs'] ?? 0) === 0 && ($s['sessions_count'] ?? 0) === 0)
                        <div class="mt-3 text-xs text-amber-700 dark:text-amber-300">
                            لا يوجد تسميع ضمن الفترة — الطالب ظاهر لأنه من طلاب الحلقة.
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Full Table --}}
        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="p-3 text-right font-semibold text-gray-700 dark:text-gray-200">#</th>
                        <th class="p-3 text-right font-semibold text-gray-700 dark:text-gray-200">الطالب</th>
                        <th class="p-3 text-center font-semibold text-gray-700 dark:text-gray-200">آيات</th>
                        <th class="p-3 text-center font-semibold text-gray-700 dark:text-gray-200">جلسات</th>
                        <th class="p-3 text-center font-semibold text-gray-700 dark:text-gray-200">أخطاء</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900">
                    @foreach($students as $s)
                        @php
                            $highlight = ($s['rank'] ?? 999) <= 3 ? 'bg-emerald-50/50 dark:bg-emerald-900/10' : '';
                            $rankIcon = ($s['rank'] ?? 0) === 1 ? '🥇' : (($s['rank'] ?? 0) === 2 ? '🥈' : (($s['rank'] ?? 0) === 3 ? '🥉' : null));
                        @endphp
                        <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50/70 dark:hover:bg-gray-800/50 transition-colors {{ $highlight }}">
                            <td class="p-3 align-middle">
                                @if($rankIcon)
                                    <span class="text-lg leading-none">{{ $rankIcon }}</span>
                                @else
                                    <span class="w-7 h-7 inline-flex items-center justify-center rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-bold text-xs">
                                        {{ $s['rank'] }}
                                    </span>
                                @endif
                            </td>

                            <td class="p-3 align-middle">
                                <div class="font-semibold text-gray-800 dark:text-gray-100 leading-tight">{{ $s['student_name'] }}</div>
                                @if(!empty($s['phone']))
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $s['phone'] }}</div>
                                @endif
                            </td>

                            <td class="p-3 text-center align-middle">
                                <span class="inline-flex px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 ring-1 ring-emerald-200 dark:ring-emerald-700 font-semibold">
                                    {{ $s['total_ayahs'] }}
                                </span>
                            </td>

                            <td class="p-3 text-center align-middle">
                                <span class="inline-flex px-3 py-1 rounded-full bg-sky-50 dark:bg-sky-900/20 text-sky-700 dark:text-sky-300 ring-1 ring-sky-200 dark:ring-sky-700 font-semibold">
                                    {{ $s['sessions_count'] }}
                                </span>
                            </td>

                            <td class="p-3 text-center align-middle">
                                @php
                                    $m = (int) ($s['total_mistakes'] ?? 0);
                                    $badge = $m <= 2
                                        ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 ring-green-200 dark:ring-green-700'
                                        : ($m <= 5
                                            ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 ring-amber-200 dark:ring-amber-700'
                                            : 'bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-300 ring-rose-200 dark:ring-rose-700');
                                @endphp
                                <span class="inline-flex px-3 py-1 rounded-full ring-1 font-semibold {{ $badge }}">
                                    {{ $m }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4 p-3 rounded-xl bg-gray-50 dark:bg-gray-800 ring-1 ring-black/5 dark:ring-white/10 text-center">
            <span class="text-gray-700 dark:text-gray-200 font-semibold">
                إجمالي الطلاب: {{ $students->count() }} |
                مجموع ��لآيات: {{ $students->sum('total_ayahs') }} |
                مجموع الجلسات: {{ $students->sum('sessions_count') }} |
                مجموع الأخطاء: {{ $students->sum('total_mistakes') }}
            </span>
        </div>
    @endif
</x-filament::section>