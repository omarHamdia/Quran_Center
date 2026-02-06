<?php

namespace App\Providers;

use App\Models\MemorizationRecord;
use App\Observers\MemorizationRecordObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // تسجيل Observer لتحديث التقدم تلقائياً
        MemorizationRecord::observe(MemorizationRecordObserver::class);
    }
}