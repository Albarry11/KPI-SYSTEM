<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Users ──
        $manager = User::updateOrCreate(
            ['username' => 'manager'],
            [
                'name'       => 'Budi Manajer',
                'email'      => 'manager@kpi.app',
                'password'   => Hash::make('password123'),
                'role'       => 'manager',
                'department' => 'Engineering',
                'position'   => 'Engineering Manager',
                'is_active'  => true,
            ]
        );

        $andi = User::updateOrCreate(
            ['username' => 'andi'],
            [
                'name'       => 'Andi Pratama',
                'email'      => 'andi@kpi.app',
                'password'   => Hash::make('password123'),
                'role'       => 'employee',
                'department' => 'Engineering',
                'position'   => 'Backend Developer',
                'is_active'  => true,
            ]
        );

        $sari = User::updateOrCreate(
            ['username' => 'sari'],
            [
                'name'       => 'Sari Lestari',
                'email'      => 'sari@kpi.app',
                'password'   => Hash::make('password123'),
                'role'       => 'employee',
                'department' => 'Sales',
                'position'   => 'Sales Executive',
                'is_active'  => true,
            ]
        );

        $budi = User::updateOrCreate(
            ['username' => 'budi'],
            [
                'name'       => 'Budi Santoso',
                'email'      => 'budi@kpi.app',
                'password'   => Hash::make('password123'),
                'role'       => 'employee',
                'department' => 'Engineering',
                'position'   => 'QA Engineer',
                'is_active'  => true,
            ]
        );

        $dewi = User::updateOrCreate(
            ['username' => 'dewi'],
            [
                'name'       => 'Dewi Rahayu',
                'email'      => 'dewi@kpi.app',
                'password'   => Hash::make('password123'),
                'role'       => 'employee',
                'department' => 'Support',
                'position'   => 'Customer Support',
                'is_active'  => true,
            ]
        );
    }
}
