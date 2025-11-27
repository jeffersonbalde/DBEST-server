<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schools = [
            [
                'name' => 'Manila Elementary School',
                'deped_code' => '101001',
                'region' => 'National Capital Region (NCR)',
                'division' => 'Manila',
                'district' => 'District I',
                'address' => '123 Rizal Avenue, Manila',
                'contact_person' => 'Dr. Maria Santos',
                'contact_email' => 'manila.es@deped.gov.ph',
                'contact_phone' => '0912-345-6789',
                'website' => 'https://manila-es.deped.gov.ph',
                'is_active' => true,
            ],
            [
                'name' => 'Quezon City High School',
                'deped_code' => '101002',
                'region' => 'National Capital Region (NCR)',
                'division' => 'Quezon City',
                'district' => 'District II',
                'address' => '456 Commonwealth Avenue, Quezon City',
                'contact_person' => 'Mr. Juan Dela Cruz',
                'contact_email' => 'qc.hs@deped.gov.ph',
                'contact_phone' => '0913-456-7890',
                'website' => 'https://qc-hs.deped.gov.ph',
                'is_active' => true,
            ],
            [
                'name' => 'Makati Science High School',
                'deped_code' => '101003',
                'region' => 'National Capital Region (NCR)',
                'division' => 'Makati',
                'district' => 'District III',
                'address' => '789 Ayala Avenue, Makati City',
                'contact_person' => 'Dr. Ana Garcia',
                'contact_email' => 'makati.shs@deped.gov.ph',
                'contact_phone' => '0914-567-8901',
                'website' => 'https://makati-shs.deped.gov.ph',
                'is_active' => true,
            ],
            [
                'name' => 'Pasig City Elementary School',
                'deped_code' => '101004',
                'region' => 'National Capital Region (NCR)',
                'division' => 'Pasig',
                'district' => 'District IV',
                'address' => '321 Ortigas Avenue, Pasig City',
                'contact_person' => 'Mrs. Rosa Martinez',
                'contact_email' => 'pasig.es@deped.gov.ph',
                'contact_phone' => '0915-678-9012',
                'website' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Taguig Integrated School',
                'deped_code' => '101005',
                'region' => 'National Capital Region (NCR)',
                'division' => 'Taguig',
                'district' => 'District V',
                'address' => '654 BGC Road, Taguig City',
                'contact_person' => 'Mr. Carlos Reyes',
                'contact_email' => 'taguig.is@deped.gov.ph',
                'contact_phone' => '0916-789-0123',
                'website' => 'https://taguig-is.deped.gov.ph',
                'is_active' => true,
            ],
            [
                'name' => 'Caloocan North Elementary School',
                'deped_code' => '101006',
                'region' => 'National Capital Region (NCR)',
                'division' => 'Caloocan',
                'district' => 'District VI',
                'address' => '987 EDSA, Caloocan City',
                'contact_person' => 'Dr. Lourdes Torres',
                'contact_email' => 'caloocan.es@deped.gov.ph',
                'contact_phone' => '0917-890-1234',
                'website' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Las Piñas National High School',
                'deped_code' => '101007',
                'region' => 'National Capital Region (NCR)',
                'division' => 'Las Piñas',
                'district' => 'District VII',
                'address' => '147 Alabang-Zapote Road, Las Piñas',
                'contact_person' => 'Mrs. Elena Fernandez',
                'contact_email' => 'laspinas.nhs@deped.gov.ph',
                'contact_phone' => '0918-901-2345',
                'website' => 'https://laspinas-nhs.deped.gov.ph',
                'is_active' => true,
            ],
            [
                'name' => 'Muntinlupa Science High School',
                'deped_code' => '101008',
                'region' => 'National Capital Region (NCR)',
                'division' => 'Muntinlupa',
                'district' => 'District VIII',
                'address' => '258 National Road, Muntinlupa City',
                'contact_person' => 'Dr. Roberto Cruz',
                'contact_email' => 'muntinlupa.shs@deped.gov.ph',
                'contact_phone' => '0919-012-3456',
                'website' => 'https://muntinlupa-shs.deped.gov.ph',
                'is_active' => true,
            ],
            [
                'name' => 'Marikina Elementary School',
                'deped_code' => '101009',
                'region' => 'National Capital Region (NCR)',
                'division' => 'Marikina',
                'district' => 'District IX',
                'address' => '369 Shoe Avenue, Marikina City',
                'contact_person' => 'Mr. Fernando Lopez',
                'contact_email' => 'marikina.es@deped.gov.ph',
                'contact_phone' => '0920-123-4567',
                'website' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Mandaluyong High School',
                'deped_code' => '101010',
                'region' => 'National Capital Region (NCR)',
                'division' => 'Mandaluyong',
                'district' => 'District X',
                'address' => '741 Boni Avenue, Mandaluyong City',
                'contact_person' => 'Mrs. Patricia Villanueva',
                'contact_email' => 'mandaluyong.hs@deped.gov.ph',
                'contact_phone' => '0921-234-5678',
                'website' => 'https://mandaluyong-hs.deped.gov.ph',
                'is_active' => false,
            ],
        ];

        foreach ($schools as $school) {
            School::firstOrCreate(
                ['deped_code' => $school['deped_code']],
                $school
            );
        }

        $this->command->info('Schools seeded successfully!');
    }
}

