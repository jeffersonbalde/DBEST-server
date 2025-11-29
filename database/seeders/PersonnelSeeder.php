<?php

namespace Database\Seeders;

use App\Models\Personnel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PersonnelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $records = [
            [
                'first_name' => 'Filmor Urdaneta Jr.',
                'last_name' => 'Macion',
                'employee_id' => '8410001',
                'id_number' => '6421',
                'username' => 'filmor.macion001',
                'email' => 'filmor.macion001@deped.gov.ph',
                'phone' => '09606661128',
                'position' => 'Head Teacher V (Step 1)',
                'employment_status' => 'Permanent',
                'employment_level' => 'Non Teaching',
                'type' => 'admin',
            ],
            [
                'first_name' => 'Jomie Flor Varquez',
                'last_name' => 'Baludo',
                'employee_id' => '1500989',
                'id_number' => '6450',
                'username' => 'jomiefler.baludo',
                'email' => 'jomiefler.baludo@deped.gov.ph',
                'phone' => '09606661277',
                'position' => 'Teacher III (Step 1)',
                'employment_status' => 'Permanent',
                'employment_level' => 'Elementary',
                'type' => 'teacher',
            ],
            [
                'first_name' => 'Richabita Romo',
                'last_name' => 'Oliver',
                'employee_id' => '8060031',
                'id_number' => '6663',
                'username' => 'richabita.oliver001',
                'email' => 'richabita.oliver001@deped.gov.ph',
                'phone' => '09811030726',
                'position' => 'Teacher III (Step 1)',
                'employment_status' => 'Permanent',
                'employment_level' => 'Elementary',
                'type' => 'teacher',
            ],
            [
                'first_name' => 'Julius Ceasar Salinasal',
                'last_name' => 'Oliver',
                'employee_id' => '8001069',
                'id_number' => '6656',
                'username' => 'juliusceasar.oliver',
                'email' => 'juliusceasar.oliver@deped.gov.ph',
                'phone' => '09092459678',
                'position' => 'Teacher III (Step 1)',
                'employment_status' => 'Permanent',
                'employment_level' => 'Elementary',
                'type' => 'teacher',
            ],
            [
                'first_name' => 'Maria',
                'last_name' => 'Cruz',
                'employee_id' => 'PER-005',
                'id_number' => '7001',
                'username' => 'maria.cruz',
                'email' => 'maria.cruz@deped.gov.ph',
                'phone' => '0912-345-6789',
                'position' => 'Teacher II',
                'employment_status' => 'Permanent',
                'employment_level' => 'Secondary',
                'subject_area' => 'Mathematics',
                'type' => 'teacher',
            ],
            [
                'first_name' => 'Juan',
                'last_name' => 'Santos',
                'employee_id' => 'PER-006',
                'id_number' => '7002',
                'username' => 'juan.santos',
                'email' => 'juan.santos@deped.gov.ph',
                'phone' => '0913-456-7890',
                'position' => 'Teacher III',
                'employment_status' => 'Permanent',
                'employment_level' => 'Secondary',
                'subject_area' => 'Science',
                'type' => 'teacher',
            ],
            [
                'first_name' => 'Ana',
                'last_name' => 'Reyes',
                'employee_id' => 'PER-007',
                'id_number' => '7003',
                'username' => 'ana.reyes',
                'email' => 'ana.reyes@deped.gov.ph',
                'phone' => '0914-567-8901',
                'position' => 'Master Teacher I',
                'employment_status' => 'Permanent',
                'employment_level' => 'Elementary',
                'subject_area' => 'English',
                'type' => 'teacher',
            ],
            [
                'first_name' => 'Carlos',
                'last_name' => 'Garcia',
                'employee_id' => 'PER-008',
                'id_number' => '7004',
                'username' => 'carlos.garcia',
                'email' => 'carlos.garcia@deped.gov.ph',
                'phone' => '0915-678-9012',
                'position' => 'Teacher I',
                'employment_status' => 'Contractual',
                'employment_level' => 'Elementary',
                'subject_area' => 'Social Studies',
                'type' => 'teacher',
            ],
            [
                'first_name' => 'Rosa',
                'last_name' => 'Martinez',
                'employee_id' => 'PER-009',
                'id_number' => '7005',
                'username' => 'rosa.martinez',
                'email' => 'rosa.martinez@deped.gov.ph',
                'phone' => '0916-789-0123',
                'position' => 'Head Teacher III',
                'employment_status' => 'Permanent',
                'employment_level' => 'Secondary',
                'subject_area' => 'Filipino',
                'type' => 'admin',
            ],
            [
                'first_name' => 'Roberto',
                'last_name' => 'Lopez',
                'employee_id' => 'PER-010',
                'id_number' => '7006',
                'username' => 'roberto.lopez',
                'email' => 'roberto.lopez@deped.gov.ph',
                'phone' => '0917-890-1234',
                'position' => 'Teacher II',
                'employment_status' => 'Permanent',
                'employment_level' => 'Secondary',
                'subject_area' => 'Physical Education',
                'type' => 'teacher',
            ],
            [
                'first_name' => 'Elena',
                'last_name' => 'Fernandez',
                'employee_id' => 'PER-011',
                'id_number' => '7007',
                'username' => 'elena.fernandez',
                'email' => 'elena.fernandez@deped.gov.ph',
                'phone' => '0918-901-2345',
                'position' => 'Teacher III',
                'employment_status' => 'Permanent',
                'employment_level' => 'Elementary',
                'subject_area' => 'Arts',
                'type' => 'teacher',
            ],
            [
                'first_name' => 'Fernando',
                'last_name' => 'Torres',
                'employee_id' => 'PER-012',
                'id_number' => '7008',
                'username' => 'fernando.torres',
                'email' => 'fernando.torres@deped.gov.ph',
                'phone' => '0919-012-3456',
                'position' => 'ICT Coordinator',
                'employment_status' => 'Permanent',
                'employment_level' => 'Secondary',
                'subject_area' => 'ICT',
                'type' => 'staff',
            ],
            [
                'first_name' => 'Patricia',
                'last_name' => 'Villanueva',
                'employee_id' => 'PER-013',
                'id_number' => '7009',
                'username' => 'patricia.villanueva',
                'email' => 'patricia.villanueva@deped.gov.ph',
                'phone' => '0920-123-4567',
                'position' => 'Teacher I',
                'employment_status' => 'Permanent',
                'employment_level' => 'Elementary',
                'subject_area' => 'General Education',
                'type' => 'teacher',
            ],
            [
                'first_name' => 'Luis',
                'last_name' => 'Ramos',
                'employee_id' => 'PER-014',
                'id_number' => '7010',
                'username' => 'luis.ramos',
                'email' => 'luis.ramos@deped.gov.ph',
                'phone' => '0921-234-5678',
                'position' => 'Teacher II',
                'employment_status' => 'Permanent',
                'employment_level' => 'Secondary',
                'subject_area' => 'Science',
                'type' => 'teacher',
                'is_active' => false,
            ],
        ];

        foreach ($records as $record) {
            $defaults = [
                'password' => Hash::make('DepEd@123'),
                'is_active' => true,
            ];
            
            // Preserve is_active if explicitly set to false
            if (isset($record['is_active']) && $record['is_active'] === false) {
                $defaults['is_active'] = false;
                unset($record['is_active']);
            }
            
            Personnel::updateOrCreate(
                [
                    'employee_id' => $record['employee_id'],
                ],
                array_merge($record, $defaults)
            );
        }
        
        $this->command->info('Personnel seeded successfully!');
    }
}


