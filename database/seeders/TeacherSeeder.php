<?php

namespace Database\Seeders;

use App\Models\Teacher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teachers = [
            [
                'employee_id' => 'TCH-001',
                'first_name' => 'Maria',
                'last_name' => 'Cruz',
                'email' => 'maria.cruz@deped.gov.ph',
                'password' => Hash::make('password123'),
                'phone' => '0912-345-6789',
                'department' => 'Mathematics',
                'subject_area' => 'Algebra, Geometry',
                'is_active' => true,
            ],
            [
                'employee_id' => 'TCH-002',
                'first_name' => 'Juan',
                'last_name' => 'Santos',
                'email' => 'juan.santos@deped.gov.ph',
                'password' => Hash::make('password123'),
                'phone' => '0913-456-7890',
                'department' => 'Science',
                'subject_area' => 'Biology, Chemistry',
                'is_active' => true,
            ],
            [
                'employee_id' => 'TCH-003',
                'first_name' => 'Ana',
                'last_name' => 'Reyes',
                'email' => 'ana.reyes@deped.gov.ph',
                'password' => Hash::make('password123'),
                'phone' => '0914-567-8901',
                'department' => 'English',
                'subject_area' => 'Literature, Grammar',
                'is_active' => true,
            ],
            [
                'employee_id' => 'TCH-004',
                'first_name' => 'Carlos',
                'last_name' => 'Garcia',
                'email' => 'carlos.garcia@deped.gov.ph',
                'password' => Hash::make('password123'),
                'phone' => '0915-678-9012',
                'department' => 'Social Studies',
                'subject_area' => 'History, Geography',
                'is_active' => true,
            ],
            [
                'employee_id' => 'TCH-005',
                'first_name' => 'Rosa',
                'last_name' => 'Martinez',
                'email' => 'rosa.martinez@deped.gov.ph',
                'password' => Hash::make('password123'),
                'phone' => '0916-789-0123',
                'department' => 'Filipino',
                'subject_area' => 'Filipino Language, Literature',
                'is_active' => true,
            ],
            [
                'employee_id' => 'TCH-006',
                'first_name' => 'Roberto',
                'last_name' => 'Lopez',
                'email' => 'roberto.lopez@deped.gov.ph',
                'password' => Hash::make('password123'),
                'phone' => '0917-890-1234',
                'department' => 'Physical Education',
                'subject_area' => 'PE, Health',
                'is_active' => true,
            ],
            [
                'employee_id' => 'TCH-007',
                'first_name' => 'Elena',
                'last_name' => 'Fernandez',
                'email' => 'elena.fernandez@deped.gov.ph',
                'password' => Hash::make('password123'),
                'phone' => '0918-901-2345',
                'department' => 'Arts',
                'subject_area' => 'Visual Arts, Music',
                'is_active' => true,
            ],
            [
                'employee_id' => 'TCH-008',
                'first_name' => 'Fernando',
                'last_name' => 'Torres',
                'email' => 'fernando.torres@deped.gov.ph',
                'password' => Hash::make('password123'),
                'phone' => '0919-012-3456',
                'department' => 'Technology',
                'subject_area' => 'ICT, Computer Science',
                'is_active' => true,
            ],
            [
                'employee_id' => 'TCH-009',
                'first_name' => 'Patricia',
                'last_name' => 'Villanueva',
                'email' => 'patricia.villanueva@deped.gov.ph',
                'password' => Hash::make('password123'),
                'phone' => '0920-123-4567',
                'department' => 'Elementary',
                'subject_area' => 'General Education',
                'is_active' => true,
            ],
            [
                'employee_id' => 'TCH-010',
                'first_name' => 'Luis',
                'last_name' => 'Ramos',
                'email' => 'luis.ramos@deped.gov.ph',
                'password' => Hash::make('password123'),
                'phone' => '0921-234-5678',
                'department' => 'Science',
                'subject_area' => 'Physics, Earth Science',
                'is_active' => false,
            ],
        ];

        foreach ($teachers as $teacher) {
            Teacher::firstOrCreate(
                ['employee_id' => $teacher['employee_id']],
                $teacher
            );
        }

        $this->command->info('Teachers seeded successfully!');
    }
}

