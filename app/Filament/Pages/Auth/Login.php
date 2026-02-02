<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    /**
     * تخصيص نموذج تسجيل الدخول
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getPhoneFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getRememberFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    /**
     * حقل رقم الهاتف
     */
    protected function getPhoneFormComponent(): Component
    {
        return TextInput::make('phone')
            ->label('رقم الهاتف')
            ->placeholder('05xxxxxxxx')
            ->tel()
            ->required()
            ->autocomplete('tel')
            ->autofocus()
            ->extraInputAttributes(['inputmode' => 'tel', 'dir' => 'ltr']);
    }

    /**
     * حقل كلمة المرور
     */
    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('كلمة المرور')
            ->password()
            ->revealable()
            ->required()
            ->autocomplete('current-password')
            ->extraInputAttributes(['dir' => 'ltr']);
    }

    /**
     * خيار تذكرني
     */
    protected function getRememberFormComponent(): Component
    {
        return Checkbox::make('remember')
            ->label('تذكرني');
    }

    /**
     * بيانات الدخول تستخدم الهاتف
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'phone' => $data['phone'],
            'password' => $data['password'],
        ];
    }

    /**
     * رسالة خطأ الدخول
     */
    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.phone' => __('filament-panels::pages/auth/login.messages.failed'),
        ]);
    }
}