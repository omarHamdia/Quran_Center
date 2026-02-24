<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class TeacherPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('teacher')
            ->path('teacher')
            ->login() // ✅ صفحة الدخول
            ->passwordReset()
            ->profile()
            ->authGuard('web')

            // ✅ Middleware الأساسية فقط (بدون auth)
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->login(\App\Filament\Pages\Auth\Login::class)

            // ✅ المصادقة هنا
            ->authMiddleware([
                Authenticate::class,
                'role:teacher', // تحقق من الدور بعد المصادقة
            ])

            ->colors([
                'primary' => Color::Emerald,
                'gray' => Color::Slate,
                'danger' => Color::Rose,
                'info' => Color::Sky,
                'success' => Color::Green,
                'warning' => Color::Amber,
            ])

            ->brandName('مركز تحفيظ القرآن الكريم')
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('favicon.ico'))

            ->spa()

            ->discoverResources(in: app_path('Filament/Teacher/Resources'), for: 'App\\Filament\\Teacher\\Resources')
            ->discoverPages(in: app_path('Filament/Teacher/Pages'), for: 'App\\Filament\\Teacher\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Teacher/Widgets'), for: 'App\\Filament\\Teacher\\Widgets')
            ->widgets([
                \App\Filament\Teacher\Widgets\WeeklyStatsWidget::class,
                // Widgets\AccountWidget::class,
            ])

            ->navigationGroups([
                'الطلاب',
                'الحفظ والمراجعة',
                'الخطط',
                'التقارير',
                'الإعدادات',
            ])

            ->maxContentWidth('full')
            ->sidebarCollapsibleOnDesktop()
            ->sidebarFullyCollapsibleOnDesktop();
    }
}
