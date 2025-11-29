<?php

namespace Database\Seeders;

use App\Models\DcpPackage;
use App\Models\Ict;
use App\Models\School;
use Illuminate\Database\Seeder;

class DcpPackageSeeder extends Seeder
{
    /**
     * Seed a few sample DCP packages per school for testing.
     */
    public function run(): void
    {
        if (School::count() === 0) {
            $this->command?->warn('No schools found. Run SchoolSeeder first before DcpPackageSeeder.');
            return;
        }

        $ict = Ict::first();

        $samplePackages = [
            [
                'batch_name' => 'Batch 40',
                'details' => 'K to G3 - Laptop 14" (1), Projector (1), Multimedia Speaker (1)',
                'quantity' => 1,
                'package_count' => 1,
                'delivery_status' => 'Delivered',
                'installation_status' => 'Completed',
                'remarks' => 'Delivered ahead of schedule.',
                'dr_number' => 'DR-2024-001',
                'ptr_number' => 'PTR-2024-045',
                'iar_number' => 'IAR-2024-010',
            ],
            [
                'batch_name' => 'FY 2023 L4T',
                'details' => 'Laptop for Teaching - 5 Laptops',
                'quantity' => 5,
                'package_count' => 1,
                'delivery_status' => 'In Transit',
                'installation_status' => 'Not Started',
                'remarks' => 'Awaiting delivery confirmation.',
                'dr_number' => null,
                'ptr_number' => null,
                'iar_number' => null,
            ],
            [
                'batch_name' => 'Batch 26',
                'details' => '1 Host PC, 6 Desktop Virtual Terminals, peripherals included',
                'quantity' => 8,
                'package_count' => 1,
                'delivery_status' => 'Pending',
                'installation_status' => 'Not Started',
                'remarks' => 'Pending release from supplier.',
                'dr_number' => null,
                'ptr_number' => null,
                'iar_number' => null,
            ],
        ];

        $schools = School::orderBy('id')->get();

        foreach ($schools as $index => $school) {
            $payload = $samplePackages[$index % count($samplePackages)];
            DcpPackage::updateOrCreate(
                [
                    'school_id' => $school->id,
                    'batch_name' => $payload['batch_name'],
                ],
                array_merge($payload, [
                    'school_id' => $school->id,
                    'delivery_date' => now()->subDays(rand(5, 40)),
                    'dr_filename' => $payload['dr_number']
                        ? "{$payload['dr_number']}.pdf"
                        : null,
                    'ptr_filename' => $payload['ptr_number']
                        ? "{$payload['ptr_number']}.pdf"
                        : null,
                    'iar_filename' => $payload['iar_number']
                        ? "{$payload['iar_number']}.pdf"
                        : null,
                    'created_by' => $ict?->id,
                ])
            );
        }
    }
}

