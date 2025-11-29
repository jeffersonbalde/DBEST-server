<?php

namespace Database\Seeders;

use App\Models\DcpInventoryItem;
use App\Models\DcpPackage;
use App\Models\Personnel;
use App\Models\School;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DcpInventoryItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schools = School::all();
        $packages = DcpPackage::all();
        $personnel = Personnel::where('is_active', true)->get();

        if ($schools->isEmpty() || $packages->isEmpty()) {
            $this->command->warn('Skipping DcpInventoryItemSeeder: Missing required data (Schools or DCP Packages)');
            return;
        }

        $conditionStatuses = ['SERVICEABLE', 'SERVICEABLE', 'SERVICEABLE', 'NEEDS REPAIR', 'UNSERVICEABLE', 'MISSING/LOST'];
        $categories = [
            'Laptop 14"',
            'Projector',
            'Multimedia Speaker',
            'Desktop Virtualization Device',
            'Networking Peripherals',
        ];

        $itemsCreated = 0;

        foreach ($packages as $package) {
            $school = $schools->find($package->school_id);
            if (!$school) {
                continue;
            }

            // Create 3-5 items per package
            $itemCount = rand(3, 5);

            for ($i = 0; $i < $itemCount; $i++) {
                $category = $categories[array_rand($categories)];
                $conditionStatus = $conditionStatuses[array_rand($conditionStatuses)];
                $assignedPersonnel = $personnel->isNotEmpty() && rand(0, 100) < 60 ? $personnel->random() : null;

                $itemData = [
                    'school_id' => $school->id,
                    'dcp_package_id' => $package->id,
                    'batch_name' => $package->batch_name,
                    'category' => $category,
                    'description' => $this->getDescriptionForCategory($category),
                    'manufacturer' => $this->getManufacturerForCategory($category),
                    'model' => $this->getModelForCategory($category),
                    'serial_number' => 'DCP-' . Str::upper(Str::random(8)),
                    'unit_of_measure' => 'pcs',
                    'unit_value' => $this->getUnitValueForCategory($category),
                    'quantity' => 1,
                    'property_no' => 'PROP-' . $school->deped_code . '-' . Str::upper(Str::random(6)),
                    'personnel_id' => $assignedPersonnel?->id,
                    'condition_status' => $conditionStatus,
                    'last_checked_at' => now()->subDays(rand(1, 90)),
                    'validation_status' => rand(0, 100) < 80 ? 'Verified' : 'Unverified',
                    'remarks' => $this->getRemarksForStatus($conditionStatus),
                ];

                DcpInventoryItem::create($itemData);
                $itemsCreated++;
            }
        }

        $this->command->info("Created {$itemsCreated} DCP inventory items successfully!");
    }

    private function getDescriptionForCategory(string $category): string
    {
        return match ($category) {
            'Laptop 14"' => '14-inch laptop for teaching',
            'Projector' => 'Portable projector for classroom presentations',
            'Multimedia Speaker' => 'Powered speaker system for audio',
            'Desktop Virtualization Device' => 'Desktop virtualization terminal',
            'Networking Peripherals' => 'Network equipment and accessories',
            default => 'DCP Package Item',
        };
    }

    private function getManufacturerForCategory(string $category): string
    {
        return match ($category) {
            'Laptop 14"' => 'Dell',
            'Projector' => 'Epson',
            'Multimedia Speaker' => 'Logitech',
            'Desktop Virtualization Device' => 'HP',
            'Networking Peripherals' => 'TP-Link',
            default => 'Generic',
        };
    }

    private function getModelForCategory(string $category): string
    {
        return match ($category) {
            'Laptop 14"' => 'Latitude 3420',
            'Projector' => 'EB-X06',
            'Multimedia Speaker' => 'Z407',
            'Desktop Virtualization Device' => 't640',
            'Networking Peripherals' => 'Archer C50',
            default => 'Standard Model',
        };
    }

    private function getUnitValueForCategory(string $category): float
    {
        return match ($category) {
            'Laptop 14"' => 48500.00,
            'Projector' => 29500.00,
            'Multimedia Speaker' => 6500.00,
            'Desktop Virtualization Device' => 35000.00,
            'Networking Peripherals' => 2500.00,
            default => 10000.00,
        };
    }

    private function getRemarksForStatus(string $status): ?string
    {
        return match ($status) {
            'SERVICEABLE' => 'Item in good working condition.',
            'NEEDS REPAIR' => 'Item requires maintenance or repair.',
            'UNSERVICEABLE' => 'Item is no longer functional.',
            'MISSING/LOST' => 'Item reported missing during inventory check.',
            default => null,
        };
    }
}

