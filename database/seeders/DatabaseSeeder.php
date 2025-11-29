<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Ict;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create default ICT account
        Ict::firstOrCreate(
            ['employee_id' => 'admin@admin.com'],
            [
                'first_name' => 'Admin',
                'last_name' => 'ICT',
                'email' => 'admin@admin.com',
                'password' => Hash::make('123456'),
                'phone' => null,
                'position' => 'ICT Administrator',
                'is_active' => true,
            ]
        );

        // Seed all data in correct order
        $this->call([
            SchoolSeeder::class,              // 1. Seed schools first
            InventoryCategorySeeder::class,   // 2. Seed inventory categories
            PropertyCustodianSeeder::class,   // 3. Property custodians (depends on schools)
            AccountingSeeder::class,          // 4. Accounting users
            TeacherSeeder::class,             // 5. Teacher users
            PersonnelSeeder::class,           // 6. Personnel directory
            InventorySeeder::class,           // 7. Inventory items (depends on categories)
            AssignedItemSeeder::class,         // 8. Assigned items (depends on personnel, inventory, custodians)
            DcpPackageSeeder::class,          // 9. DCP packages (depends on schools)
        ]);
    }
}
