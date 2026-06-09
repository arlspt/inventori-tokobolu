<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleAndAdminSeeder extends Seeder
{
    public function run(): void
    {
        // ✅ buat role
        $admin    = Role::firstOrCreate(['name' => 'admin']);
        $karyawan = Role::firstOrCreate(['name' => 'karyawan']);

        // ✅ buat user admin pertama
        $user = User::firstOrCreate(
            ['email' => 'admin@bolulegendag.com'],
            [
                'name'     => 'Admin',
                'password' => Hash::make('admin123'),
            ]
        );

        $user->assignRole($admin);
    }
}
