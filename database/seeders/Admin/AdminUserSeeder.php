<?php

namespace Database\Seeders\Admin;

use App\Models\Admin\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        AdminUser::query()->updateOrCreate(
            ['email' => 'admin@petpay-card.com'],
            [
                'first_name' => 'Admin',
                'last_name' => 'PETPAY-CARD',
                'name' => 'Admin PETPAY-CARD',
                'phone' => null,
                'position' => 'Administrador principal',
                'department' => 'Operación',
                'can_manage_system' => true,
                'password' => Hash::make('PetpayCard2026*'),
                'status' => 'active',
                'email_verified_at' => now(),
                'last_login_at' => null,
            ]
        );
    }
}