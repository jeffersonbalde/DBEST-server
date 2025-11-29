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
        ];

        foreach ($records as $record) {
            Personnel::updateOrCreate(
                [
                    'employee_id' => $record['employee_id'],
                ],
                array_merge($record, [
                    'password' => Hash::make('DepEd@123'),
                    'is_active' => true,
                ])
            );
        }
    }
}


