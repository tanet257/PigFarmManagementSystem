<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Simulation: PigEntry + Cancel ===\n\n";

echo "Step 1: Create PigEntry with 1500 pigs\n";
echo "  - addPigs() called 40 times (one per pen)\n";
echo "  - Each pen: allocated +38, current +38\n";
echo "  - Batch: total_pig_amount +1500, current_quantity +1500\n\n";

echo "Step 2: Create 2nd PigEntry with 1500 pigs (same pens)\n";
echo "  - addPigs() called 40 times (for same pens)\n";
echo "  - Pen already has 38, so:\n";
echo "    * allocated_pigs = 38 + 38 = 76 ✗ (STACK!)\n";
echo "    * current_quantity = 38 + 38 = 76 ✗\n";
echo "  - Batch: total_pig_amount = 3000, current_quantity = 3000\n\n";

echo "After 4 entries:\n";
echo "  - Each pen: allocated = 38 + 38 + 38 + 38 = 152 ✗ (STACKED)\n";
echo "  - Each pen: current = 152\n";
echo "  - Total allocation: 40 pens x 152 = 6080 ✗ (over 6000)\n";
echo "  - Batch total_pig_amount: 6000 ✓ (tracked separately)\n";
echo "  - Batch current_quantity: 6000 ✓ (tracked separately)\n\n";

echo "When Cancel PigEntry 1:\n";
echo "  - reducePigInventory() for each detail (40 times x 38 pigs)\n";
echo "  - Each pen: current -= 38\n";
echo "  - Batch: current_quantity -= 1500\n\n";

echo "Problem: addPigs() has BUG!\n";
echo "  - ❌ allocated_pigs + quantity (should not stack)\n";
echo "  - ✓ current_quantity + quantity (correct)\n";
echo "  - But when cancel, it uses PigEntryDetail quantity\n";
echo "  - Which only tracks what THIS entry added (38 per pen)\n\n";

echo "=== FIX NEEDED ===\n";
echo "Option A: Prevent duplicate pens in PigEntry\n";
echo "  - Check if pen already has allocation for this batch\n";
echo "  - Only add if new\n\n";

echo "Option B: Track original allocation per PigEntry\n";
echo "  - Store original quantity in PigEntryDetail\n";
echo "  - Use that for cancel\n\n";

echo "Option C: Fix addPigs() logic\n";
echo "  - Make allocated_pigs never stack\n";
echo "  - Keep only the original allocation value\n";
