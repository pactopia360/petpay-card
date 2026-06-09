<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = DB::table('roles')->where('key', 'admin')->first();

        if (! $adminRole) {
            return;
        }

        $adminId = DB::table('users')->updateOrInsert(
            ['email' => 'admin@petpay-card.com'],
            [
                'first_name' => 'Admin',
                'last_name' => 'PETPAY-CARD',
                'name' => 'Admin PETPAY-CARD',
                'phone' => null,
                'password' => Hash::make('PetpayCard2026*'),
                'status' => 'active',
                'email_verified_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $user = DB::table('users')->where('email', 'admin@petpay-card.com')->first();

        if (! $user) {
            return;
        }

        DB::table('role_user')->updateOrInsert(
            [
                'role_id' => $adminRole->id,
                'user_id' => $user->id,
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('admin_profiles')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'position' => 'Administrador principal',
                'department' => 'Operación',
                'can_manage_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}