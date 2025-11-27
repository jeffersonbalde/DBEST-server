<?php

namespace Database\Seeders;

use App\Models\Accounting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AccountingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accountings = [
            [
                'username' => 'acc.admin001',
                'first_name' => 'Michael',
                'last_name' => 'Tan',
                'email' => 'michael.tan@deped.gov.ph',
                'password' => Hash::make('password123'),
                'phone' => '0922-345-6789',
                'employee_id' => 'EMP-ACC-001',
                'position' => 'Senior Accounting Officer',
                'is_active' => true,
            ],
            [
                'username' => 'acc.officer002',
                'first_name' => 'Jennifer',
                'last_name' => 'Lim',
                'email' => 'jennifer.lim@deped.gov.ph',
                'password' => Hash::make('password123'),
                'phone' => '0923-456-7890',
                'employee_id' => 'EMP-ACC-002',
                'position' => 'Accounting Officer',
                'is_active' => true,
            ],
            [
                'username' => 'acc.specialist003',
                'first_name' => 'David',
                'last_name' => 'Chua',
                'email' => 'david.chua@deped.gov.ph',
                'password' => Hash::make('password123'),
                'phone' => '0924-567-8901',
                'employee_id' => 'EMP-ACC-003',
                'position' => 'Accounting Specialist',
                'is_active' => true,
            ],
            [
                'username' => 'acc.analyst004',
                'first_name' => 'Sarah',
                'last_name' => 'Wong',
                'email' => 'sarah.wong@deped.gov.ph',
                'password' => Hash::make('password123'),
                'phone' => '0925-678-9012',
                'employee_id' => 'EMP-ACC-004',
                'position' => 'Financial Analyst',
                'is_active' => true,
            ],
            [
                'username' => 'acc.clerk005',
                'first_name' => 'James',
                'last_name' => 'Ong',
                'email' => 'james.ong@deped.gov.ph',
                'password' => Hash::make('password123'),
                'phone' => '0926-789-0123',
                'employee_id' => 'EMP-ACC-005',
                'position' => 'Accounting Clerk',
                'is_active' => true,
            ],
            [
                'username' => 'acc.assistant006',
                'first_name' => 'Michelle',
                'last_name' => 'Sy',
                'email' => 'michelle.sy@deped.gov.ph',
                'password' => Hash::make('password123'),
                'phone' => '0927-890-1234',
                'employee_id' => 'EMP-ACC-006',
                'position' => 'Accounting Assistant',
                'is_active' => true,
            ],
            [
                'username' => 'acc.coordinator007',
                'first_name' => 'Richard',
                'last_name' => 'Yu',
                'email' => 'richard.yu@deped.gov.ph',
                'password' => Hash::make('password123'),
                'phone' => '0928-901-2345',
                'employee_id' => 'EMP-ACC-007',
                'position' => 'Budget Coordinator',
                'is_active' => true,
            ],
            [
                'username' => 'acc.manager008',
                'first_name' => 'Grace',
                'last_name' => 'Chen',
                'email' => 'grace.chen@deped.gov.ph',
                'password' => Hash::make('password123'),
                'phone' => '0929-012-3456',
                'employee_id' => 'EMP-ACC-008',
                'position' => 'Accounting Manager',
                'is_active' => true,
            ],
            [
                'username' => 'acc.supervisor009',
                'first_name' => 'Daniel',
                'last_name' => 'Kho',
                'email' => 'daniel.kho@deped.gov.ph',
                'password' => Hash::make('password123'),
                'phone' => '0930-123-4567',
                'employee_id' => 'EMP-ACC-009',
                'position' => 'Accounting Supervisor',
                'is_active' => true,
            ],
            [
                'username' => 'acc.inactive010',
                'first_name' => 'Lisa',
                'last_name' => 'Ang',
                'email' => 'lisa.ang@deped.gov.ph',
                'password' => Hash::make('password123'),
                'phone' => '0931-234-5678',
                'employee_id' => 'EMP-ACC-010',
                'position' => 'Accounting Officer',
                'is_active' => false,
            ],
        ];

        foreach ($accountings as $accounting) {
            Accounting::firstOrCreate(
                ['username' => $accounting['username']],
                $accounting
            );
        }

        $this->command->info('Accounting users seeded successfully!');
    }
}

