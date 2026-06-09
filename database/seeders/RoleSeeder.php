<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'key' => 'admin',
                'name' => 'Administrador',
                'description' => 'Usuario con acceso al portal Admin y control general del sistema.',
                'is_system' => true,
            ],
            [
                'key' => 'cliente',
                'name' => 'Cliente',
                'description' => 'Usuario comprador de productos y servicios para mascotas.',
                'is_system' => true,
            ],
            [
                'key' => 'proveedor',
                'name' => 'Proveedor / Vendedor / POS',
                'description' => 'Usuario proveedor que administra negocio, productos, servicios, inventario y tickets.',
                'is_system' => true,
            ],
            [
                'key' => 'repartidor',
                'name' => 'Repartidor',
                'description' => 'Usuario repartidor que recibe tickets de entrega y administra rutas e ingresos.',
                'is_system' => true,
            ],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['key' => $role['key']],
                [
                    'name' => $role['name'],
                    'description' => $role['description'],
                    'is_system' => $role['is_system'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}