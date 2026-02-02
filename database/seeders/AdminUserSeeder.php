<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Create the initial admin user.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['phone' => '0500000000'],
            [
                'name' => 'أمير المركز',
                'phone' => '0500000000',
                'email' => 'admin@quran-center.test',
                'password' => Hash::make('password'),
                'gender' => 'male',
                'is_active' => true,
                'phone_verified_at' => now(),
            ]
        );

        $this->command->newLine();
        $this->command->info('╔══════════════════════════════════════════╗');
        $this->command->info('║     ✅ تم إنشاء حساب المدير بنجاح!      ║');
        $this->command->info('╠══════════════════════════════════════════╣');
        $this->command->info('║  📱 رقم الجوال: 0500000000              ║');
        $this->command->info('║  🔑 كلمة المرور: password               ║');
        $this->command->info('╠══════════════════════════════════════════╣');
        $this->command->warn('║  ⚠️  غيّر كلمة المرور في بيئة الإنتاج!   ║');
        $this->command->info('╚══════════════════════════════════════════╝');
        $this->command->newLine();
    }
}
