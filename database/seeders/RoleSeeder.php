<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $salesRole = Role::firstOrCreate(['name' => 'salesperson']);

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@webexcels.com'],
            [
                'name' => 'Ayaz',
                'password' => bcrypt('password'),
            ]
        );
        $admin->assignRole($adminRole);

        // Create manager user
        $manager = User::firstOrCreate(
            ['email' => 'manager@webexcels.com'],
            [
                'name' => 'Asad',
                'password' => bcrypt('password'),
            ]
        );
        $manager->assignRole($managerRole);

        // Create salesperson user
        $sales = User::firstOrCreate(
            ['email' => 'sales@webexcels.com'],
            [
                'name' => 'Badar',
                'password' => bcrypt('password'),
            ]
        );
        $sales->assignRole($salesRole);
    }
}
