<?php

namespace Database\Seeders;

use App\Models\PropertyCustodian;
use App\Models\School;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PropertyCustodianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some schools for assignment
        $schools = School::all();

        $custodians = [
            [
                'username' => 'pc.manila001',
                'first_name' => 'John',
                'last_name' => 'Dela Cruz',
                'email' => 'john.delacruz@deped.gov.ph',
                'password' => Hash::make('password123'),
                'phone' => '0912-345-6789',
                'employee_id' => 'EMP-PC-001',
                'position' => 'Property Custodian',
                'is_active' => true,
                'school_id' => $schools->where('deped_code', '101001')->first()?->id,
            ],
            [
                'username' => 'pc.qc002',
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'email' => 'maria.santos@deped.gov.ph',
                'password' => Hash::make('password123'),
                'phone' => '0913-456-7890',
                'employee_id' => 'EMP-PC-002',
                'position' => 'Property Custodian',
                'is_active' => true,
                'school_id' => $schools->where('deped_code', '101002')->first()?->id,
            ],
            [
                'username' => 'pc.makati003',
                'first_name' => 'Roberto',
                'last_name' => 'Garcia',
                'email' => 'roberto.garcia@deped.gov.ph',
                'password' => Hash::make('password123'),
                'phone' => '0914-567-8901',
                'employee_id' => 'EMP-PC-003',
                'position' => 'Property Custodian',
                'is_active' => true,
                'school_id' => $schools->where('deped_code', '101003')->first()?->id,
            ],
            [
                'username' => 'pc.pasig004',
                'first_name' => 'Ana',
                'last_name' => 'Martinez',
                'email' => 'ana.martinez@deped.gov.ph',
                'password' => Hash::make('password123'),
                'phone' => '0915-678-9012',
                'employee_id' => 'EMP-PC-004',
                'position' => 'Property Custodian',
                'is_active' => true,
                'school_id' => $schools->where('deped_code', '101004')->first()?->id,
            ],
            [
                'username' => 'pc.taguig005',
                'first_name' => 'Carlos',
                'last_name' => 'Reyes',
                'email' => 'carlos.reyes@deped.gov.ph',
                'password' => Hash::make('password123'),
                'phone' => '0916-789-0123',
                'employee_id' => 'EMP-PC-005',
                'position' => 'Property Custodian',
                'is_active' => true,
                'school_id' => $schools->where('deped_code', '101005')->first()?->id,
            ],
            [
                'username' => 'pc.caloocan006',
                'first_name' => 'Lourdes',
                'last_name' => 'Torres',
                'email' => 'lourdes.torres@deped.gov.ph',
                'password' => Hash::make('password123'),
                'phone' => '0917-890-1234',
                'employee_id' => 'EMP-PC-006',
                'position' => 'Property Custodian',
                'is_active' => true,
                'school_id' => $schools->where('deped_code', '101006')->first()?->id,
            ],
            [
                'username' => 'pc.laspinas007',
                'first_name' => 'Elena',
                'last_name' => 'Fernandez',
                'email' => 'elena.fernandez@deped.gov.ph',
                'password' => Hash::make('password123'),
                'phone' => '0918-901-2345',
                'employee_id' => 'EMP-PC-007',
                'position' => 'Property Custodian',
                'is_active' => true,
                'school_id' => $schools->where('deped_code', '101007')->first()?->id,
            ],
            [
                'username' => 'pc.muntinlupa008',
                'first_name' => 'Roberto',
                'last_name' => 'Cruz',
                'email' => 'roberto.cruz@deped.gov.ph',
                'password' => Hash::make('password123'),
                'phone' => '0919-012-3456',
                'employee_id' => 'EMP-PC-008',
                'position' => 'Property Custodian',
                'is_active' => true,
                'school_id' => $schools->where('deped_code', '101008')->first()?->id,
            ],
            [
                'username' => 'pc.marikina009',
                'first_name' => 'Fernando',
                'last_name' => 'Lopez',
                'email' => 'fernando.lopez@deped.gov.ph',
                'password' => Hash::make('password123'),
                'phone' => '0920-123-4567',
                'employee_id' => 'EMP-PC-009',
                'position' => 'Property Custodian',
                'is_active' => true,
                'school_id' => $schools->where('deped_code', '101009')->first()?->id,
            ],
            [
                'username' => 'pc.mandaluyong010',
                'first_name' => 'Patricia',
                'last_name' => 'Villanueva',
                'email' => 'patricia.villanueva@deped.gov.ph',
                'password' => Hash::make('password123'),
                'phone' => '0921-234-5678',
                'employee_id' => 'EMP-PC-010',
                'position' => 'Property Custodian',
                'is_active' => false,
                'school_id' => $schools->where('deped_code', '101010')->first()?->id,
            ],
        ];

        foreach ($custodians as $custodian) {
            PropertyCustodian::firstOrCreate(
                ['username' => $custodian['username']],
                $custodian
            );
        }

        $this->command->info('Property Custodians seeded successfully!');
    }
}

