<?php

namespace Database\Seeders;

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $items = [
                [
                    'item_code' => 'INV-LAP-001',
                    'name' => 'Dell Latitude 3420',
                    'description' => '14" laptop for classroom teachers',
                    'category' => 'Laptops',
                    'brand' => 'Dell',
                    'model' => 'Latitude 3420',
                    'serial_number' => Str::upper(Str::random(10)),
                    'unit_price' => 48500,
                    'quantity' => 10,
                    'available_quantity' => 8,
                    'unit_of_measure' => 'pcs',
                    'location' => 'ICT Stockroom',
                    'status' => 'available',
                    'purchase_date' => now()->subMonths(10),
                    'warranty_expiry' => now()->addMonths(14),
                    'supplier' => 'Dell Technologies PH',
                    'notes' => 'Includes Windows 11 Pro and MS Office licenses.',
                ],
                [
                    'item_code' => 'INV-DESK-002',
                    'name' => 'Teacher Desktop Set',
                    'description' => 'i3 desktop bundle with monitor and AVR',
                    'category' => 'Desktop Computers',
                    'brand' => 'Acer',
                    'model' => 'Veriton Essentials',
                    'serial_number' => Str::upper(Str::random(10)),
                    'unit_price' => 37500,
                    'quantity' => 6,
                    'available_quantity' => 4,
                    'unit_of_measure' => 'sets',
                    'location' => 'Admin Office',
                    'status' => 'assigned',
                    'purchase_date' => now()->subYear(),
                    'warranty_expiry' => now()->addMonths(9),
                    'supplier' => 'Acer Philippines',
                    'notes' => 'Two units currently assigned for grading use.',
                ],
                [
                    'item_code' => 'INV-PROJ-003',
                    'name' => 'Epson XGA Projector',
                    'description' => 'Portable projector for classrooms and events',
                    'category' => 'Projectors',
                    'brand' => 'Epson',
                    'model' => 'EB-X06',
                    'serial_number' => Str::upper(Str::random(10)),
                    'unit_price' => 29500,
                    'quantity' => 4,
                    'available_quantity' => 3,
                    'unit_of_measure' => 'pcs',
                    'location' => 'Library AV Cabinet',
                    'status' => 'available',
                    'purchase_date' => now()->subMonths(6),
                    'warranty_expiry' => now()->addMonths(18),
                    'supplier' => 'Epson Philippines',
                    'notes' => 'Includes spare lamps and ceiling mounts.',
                ],
                [
                    'item_code' => 'INV-SPEAK-004',
                    'name' => 'Multimedia Speaker Set',
                    'description' => 'Powered speakers for presentations',
                    'category' => 'Audio Equipment',
                    'brand' => 'Logitech',
                    'model' => 'Z407',
                    'serial_number' => Str::upper(Str::random(10)),
                    'unit_price' => 6500,
                    'quantity' => 5,
                    'available_quantity' => 5,
                    'unit_of_measure' => 'sets',
                    'location' => 'AV Room',
                    'status' => 'available',
                    'purchase_date' => now()->subMonths(4),
                    'warranty_expiry' => now()->addMonths(20),
                    'supplier' => 'Thinking Tools',
                    'notes' => 'Bluetooth capable; assigned per request.',
                ],
                [
                    'item_code' => 'INV-CAM-005',
                    'name' => 'HD Webcam Kit',
                    'description' => 'Webcam plus tripod for hybrid classes',
                    'category' => 'AV Accessories',
                    'brand' => 'Logitech',
                    'model' => 'C922',
                    'serial_number' => Str::upper(Str::random(10)),
                    'unit_price' => 8500,
                    'quantity' => 8,
                    'available_quantity' => 7,
                    'unit_of_measure' => 'kits',
                    'location' => 'ICT Storage',
                    'status' => 'available',
                    'purchase_date' => now()->subMonths(3),
                    'warranty_expiry' => now()->addMonths(21),
                    'supplier' => 'PC Express',
                    'notes' => 'Tripod + USB extension included.',
                ],
                [
                    'item_code' => 'INV-MAINT-006',
                    'name' => 'Network Switch - 24 Port',
                    'description' => 'Managed switch for ICT lab backbone',
                    'category' => 'Networking',
                    'brand' => 'Cisco',
                    'model' => 'CBS250-24T-4G',
                    'serial_number' => Str::upper(Str::random(10)),
                    'unit_price' => 42000,
                    'quantity' => 2,
                    'available_quantity' => 1,
                    'unit_of_measure' => 'pcs',
                    'location' => 'Server Rack',
                    'status' => 'maintenance',
                    'purchase_date' => now()->subMonths(8),
                    'warranty_expiry' => now()->addMonths(16),
                    'supplier' => 'Cisco Partner PH',
                    'notes' => 'One unit undergoing firmware update.',
                ],
            ];

            $categories = collect($items)
                ->pluck('category')
                ->unique()
                ->filter()
                ->mapWithKeys(function ($name) {
                    $record = InventoryCategory::firstOrCreate(
                        ['name' => $name],
                        ['description' => null]
                    );

                    return [$name => $record];
                });

            foreach ($items as $item) {
                $category = $categories->get($item['category']);
                $item['category_id'] = $category?->id;
                $item['tracking_mode'] = 'detailed';
                $item['image_path'] = null;

                InventoryItem::updateOrCreate(
                    ['item_code' => $item['item_code']],
                    $item
                );
            }
        });
    }
}

