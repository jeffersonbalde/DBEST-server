<?php

namespace Database\Seeders;

use App\Models\InventoryCategory;
use Illuminate\Database\Seeder;

class InventoryCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Laptops',
                'description' => 'Portable computers and notebooks',
                'color' => '#3B82F6', // Blue
            ],
            [
                'name' => 'Desktop Computers',
                'description' => 'Desktop PC units and workstations',
                'color' => '#10B981', // Green
            ],
            [
                'name' => 'Projectors',
                'description' => 'LCD and DLP projectors for presentations',
                'color' => '#F59E0B', // Amber
            ],
            [
                'name' => 'Audio Equipment',
                'description' => 'Speakers, microphones, and audio systems',
                'color' => '#8B5CF6', // Purple
            ],
            [
                'name' => 'AV Accessories',
                'description' => 'Webcams, tripods, cables, and AV peripherals',
                'color' => '#EC4899', // Pink
            ],
            [
                'name' => 'Networking',
                'description' => 'Switches, routers, access points, and network equipment',
                'color' => '#06B6D4', // Cyan
            ],
            [
                'name' => 'Printers',
                'description' => 'Inkjet and laser printers',
                'color' => '#84CC16', // Lime
            ],
            [
                'name' => 'Tablets',
                'description' => 'Tablet devices and iPads',
                'color' => '#F97316', // Orange
            ],
            [
                'name' => 'Monitors',
                'description' => 'Computer monitors and displays',
                'color' => '#6366F1', // Indigo
            ],
            [
                'name' => 'Peripherals',
                'description' => 'Keyboards, mice, and other input devices',
                'color' => '#14B8A6', // Teal
            ],
        ];

        foreach ($categories as $category) {
            InventoryCategory::firstOrCreate(
                ['name' => $category['name']],
                $category
            );
        }

        $this->command->info('Inventory categories seeded successfully!');
    }
}

