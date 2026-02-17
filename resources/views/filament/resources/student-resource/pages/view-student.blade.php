<x-filament-panels::page>
    @php
        $student = $this->record;
        $plans = $this->getPlans();
        $recentRecords = $this->getRecentRecords();
        $weeklyStats = $this->getWeeklyStats();
        $monthlyStats = $this->getMonthlyStats();
    @endphp

    <div class="space-y-6">
        {{-- بيانات الطالب الأساسية --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-user class="w-5 h-5 text-primary-500" />
                    <span>بيانات الطالب</span>
                </div>
            </x-slot>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-500">الاسم</div>
                    <div class="font-bold">{{ $student->user->name }}</div>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-500">الهاتف</div>
                    <div class="font-medium" dir="ltr">{{ $student->user->phone }}</div>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-500">المحفظ</div>
                    <div class="font-medium">{{ $student->teacher?->user?->name ?? 'غير معين' }}</div>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-500">المستوى</div>
                    <x-filament::badge color="info">
                        @switch($student->current_level)
                            @case('beginner') مبتدئ @break
                            @case('elementary') أساسي @break
                            @case('intermediate') متوسط @break
                            @case('advanced') متقدم @break
                            @case('memorizer') حافظ @break
                            @default -
                        @endswitch
                    </x-filament::badge>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-500">الأجزاء المحفوظة</div>
                    <div class="font-bold text-primary-600">{{ $student->memorized_juz ?? 0 }} جزء</div>
                </div>
            </div>
        </x-filament::section>

        {{-- إحصائيات سريعة --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="p-4 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl text-center text-white">
                <div class="text-3xl font-bold">{{ $weeklyStats['total_sessions'] }}</div>
                <div class="text-sm opacity-90">جلسات الأسبوع</div>
            </div>
            <div class="p-4 bg-gradient-to-br from-success-500 to-success-600 rounded-xl text-center text-white">
                <div class="text-3xl font-bold">{{ $weeklyStats['total_ayahs'] }}</div>
                <div class="text-sm opacity-90">آيات الأسبوع</div>
            </div>
            <div class="p-4 bg-gradient-to-br from-warning-500 to-warning-600 rounded-xl text-center text-white">
                <div class="text-3xl font-bold">{{ $monthlyStats['total_sessions'] }}</div>
                <div class="text-sm opacity-90">جلسات الشهر</div>
            </div>
            <div class="p-4 bg-gradient-to-br from-info-500 to-info-600 rounded-xl text-center text-white">
                <div class="text-3xl font-bold">{{ $plans->whereIn('status', ['pending', 'in_progress'])->count() }}</div>
                <div class="text-sm opacity-90">خطط نشطة</div>
            </div>
        </div>

        {{-- خطط الحفظ --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-calendar-days class="w-5 h-5 text-warning-500" />
                    <span>خطط الحفظ ({{ $plans->count() }})</span>
                </div>
            </x-slot>

            @if($plans->count() > 0)
                <div class="space-y-4">
                    @foreach($plans as $plan)
                        <div class="p-4 border rounded-lg dark:border-gray-700 {{ $plan->status === 'completed' ? 'bg-success-50 dark:bg-success-900/10' : 'bg-white dark:bg-gray-800' }}">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h4 class="font-bold text-lg">{{ $plan->title }}</h4>
                                    <p class="text-sm text-gray-500">
                                        من {{ $this->getSurahName($plan->from_surah_id) }} (آية {{ $plan->from_ayah }})
                                        إلى {{ $this->getSurahName($plan->to_surah_id) }} (آية {{ $plan->to_ayah }})
                                    </p>
                                    @if($plan->from_page && $plan->to_page)
                                        <p class="text-xs text-gray-400">
                                            الصفحات: {{ $plan->from_page }} - {{ $plan->to_page }}
                                        </p>
                                    @endif
                                </div>
                                <x-filament::badge :color="match($plan->status) {
                                    'pending' => 'warning',
                                    'in_progress' => 'info',
                                    'completed' => 'success',
                                    'cancelled' => 'danger',
                                    default => 'gray'
                                }">
                                    @switch($plan->status)
                                        @case('pending') قيد الانتظار @break
                                        @case('in_progress') قيد التنفيذ @break
                                        @case('completed') مكتملة @break
                                        @case('cancelled') ملغاة @break
                                        @default {{ $plan->status }}
                                    @endswitch
                                </x-filament::badge>
                            </div>
                            
                            {{-- شريط التقدم --}}
                            <div class="mb-2">
                                <div class="flex justify-between text-sm mb-1">
                                    <span>نسبة الإنجاز</span>
                                    <span class="font-bold">{{ $plan->progress_percentage ?? 0 }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3 dark:bg-gray-700">
                                    <div class="h-3 rounded-full transition-all duration-500 {{ 
                                        ($plan->progress_percentage ?? 0) >= 100 ? 'bg-success-500' : 
                                        (($plan->progress_percentage ?? 0) >= 50 ? 'bg-info-500' : 'bg-warning-500') 
                                    }}" style="width: {{ min($plan->progress_percentage ?? 0, 100) }}%"></div>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-4 text-sm text-gray-500">
                                <span>📅 البداية: {{ $plan->start_date?->format('Y/m/d') }}</span>
                                <span>🏁 النهاية: {{ $plan->end_date?->format('Y/m/d') }}</span>
                                @if($plan->total_ayahs)
                                    <span>📖 إجمالي الآيات: {{ $plan->total_ayahs }}</span>
                                @endif
                                @if($plan->completed_ayahs)
                                    <span>✅ المحفوظ: {{ $plan->completed_ayahs }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <x-heroicon-o-calendar class="w-12 h-12 mx-auto mb-2 opacity-30" />
                    <p>لا توجد خطط حفظ لهذا الطالب</p>
                </div>
            @endif
        </x-filament::section>

        {{-- سجلات التسميع الأخيرة --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-clipboard-document-check class="w-5 h-5 text-success-500" />
                    <span>سجلات التسميع (آخر 15)</span>
                </div>
            </x-slot>

            @if($recentRecords->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100 dark:bg-gray-800">
                            <tr>
                                <th class="p-3 text-right">التاريخ</th>
                                <th class="p-3 text-right">النوع</th>
                                <th class="p-3 text-right">السورة</th>
                                <th class="p-3 text-right">الآيات</th>
                                <th class="p-3 text-right">الصفحات</th>
                                <th class="p-3 text-right">التقييم</th>
                                <th class="p-3 text-right">الأخطاء</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($recentRecords as $record)
                                @php
                                    $fromSurah = $this->getSurahName($record->surah_id);
                                    $toSurah = $record->to_surah_id && $record->to_surah_id != $record->surah_id 
                                        ? $this->getSurahName($record->to_surah_id) 
                                        : null;
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="p-3">{{ $record->session_date?->format('Y/m/d') }}</td>
                                    <td class="p-3">
                                        <x-filament::badge :color="match($record->session_type) {
                                            'hifz' => 'success',
                                            'revision' => 'info',
                                            'test' => 'warning',
                                            default => 'gray'
                                        }" size="sm">
                                            @switch($record->session_type)
                                                @case('hifz') حفظ @break
                                                @case('revision') مراجعة @break
                                                @case('test') اختبار @break
                                                @default {{ $record->session_type }}
                                            @endswitch
                                        </x-filament::badge>
                                    </td>
                                    <td class="p-3">
                                        @if($toSurah)
                                            {{ $fromSurah }} ← {{ $toSurah }}
                                        @else
                                            {{ $fromSurah }}
                                        @endif
                                    </td>
                                    <td class="p-3">
                                        <span class="text-success-600">{{ $record->from_ayah }}</span>
                                        -
                                        <span class="text-danger-600">{{ $record->to_ayah }}</span>
                                        <span class="text-gray-400 text-xs">({{ $record->ayahs_count ?? 0 }})</span>
                                    </td>
                                    <td class="p-3 text-gray-500">
                                        @if($record->from_page && $record->to_page)
                                            {{ $record->from_page }} - {{ $record->to_page }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="p-3">
                                        <x-filament::badge :color="match($record->evaluation) {
                                            'excellent' => 'success',
                                            'very_good' => 'info',
                                            'good' => 'primary',
                                            'acceptable' => 'warning',
                                            'needs_review' => 'danger',
                                            default => 'gray'
                                        }" size="sm">
                                            @switch($record->evaluation)
                                                @case('excellent') ممتاز @break
                                                @case('very_good') جيد جداً @break
                                                @case('good') جيد @break
                                                @case('acceptable') مقبول @break
                                                @case('needs_review') يحتاج مراجعة @break
                                                @default -
                                            @endswitch
                                        </x-filament::badge>
                                    </td>
                                    <td class="p-3">
                                        <x-filament::badge :color="($record->mistakes_count ?? 0) > 5 ? 'danger' : (($record->mistakes_count ?? 0) > 2 ? 'warning' : 'success')" size="sm">
                                            {{ $record->mistakes_count ?? 0 }}
                                        </x-filament::badge>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <x-heroicon-o-clipboard class="w-12 h-12 mx-auto mb-2 opacity-30" />
                    <p>لا توجد سجلات تسميع لهذا الطالب</p>
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>