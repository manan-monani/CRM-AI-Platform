<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class ,
            CRMRoleSeeder::class ,
            // AdminUserSeeder::class,
            BusinessSettingSeeder::class ,
            // CRMSeeder::class,
            // CRMTestDataSeeder::class,
            DemoDataSeeder::class , // New Master Seeder
            SalesRoleSyncSeeder::class ,
            LeadSourceSeeder::class ,
        ]);

    // User::factory(10)->create();
    }
}