<?php

namespace Database\Seeders;

use App\Models\AssignedItem;
use App\Models\InventoryItem;
use App\Models\Personnel;
use App\Models\PropertyCustodian;
use Illuminate\Database\Seeder;

class AssignedItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $personnel = Personnel::where('is_active', true)->get();
        $inventoryItems = InventoryItem::where('status', 'available')
            ->orWhere('status', 'assigned')
            ->get();
        $propertyCustodians = PropertyCustodian::where('is_active', true)->get();

        if ($personnel->isEmpty() || $inventoryItems->isEmpty() || $propertyCustodians->isEmpty()) {
            $this->command->warn('Skipping AssignedItemSeeder: Missing required data (Personnel, InventoryItems, or PropertyCustodians)');
            return;
        }

        $statuses = ['active', 'active', 'active', 'returned', 'active']; // More active than returned
        $conditions = [
            'Good condition, fully functional',
            'Minor scratches, working properly',
            'Excellent condition',
            'Some wear but operational',
            'Like new',
        ];

        // Assign items to personnel
        $assignedCount = 0;
        $maxAssignments = min(15, $inventoryItems->count(), $personnel->count() * 2);

        foreach ($inventoryItems->take($maxAssignments) as $item) {
            $person = $personnel->random();
            $custodian = $propertyCustodians->random();
            $status = $statuses[array_rand($statuses)];
            $assignedDate = now()->subDays(rand(1, 180));
            $returnDate = $status === 'returned' ? $assignedDate->copy()->addDays(rand(30, 120)) : null;

            AssignedItem::firstOrCreate(
                [
                    'inventory_item_id' => $item->id,
                    'personnel_id' => $person->id,
                    'assigned_by' => $custodian->id,
                ],
                [
                    'quantity' => 1,
                    'assigned_date' => $assignedDate,
                    'return_date' => $returnDate,
                    'status' => $status,
                    'condition_notes' => $conditions[array_rand($conditions)],
                    'return_notes' => $status === 'returned' ? 'Item returned in good condition' : null,
                ]
            );

            // Update inventory item status if assigned
            if ($status === 'active' && $item->status === 'available') {
                $item->update(['status' => 'assigned']);
                $item->decrement('available_quantity');
            }

            $assignedCount++;
        }

        $this->command->info("Assigned {$assignedCount} items to personnel successfully!");
    }
}

