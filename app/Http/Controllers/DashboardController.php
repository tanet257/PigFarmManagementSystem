<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Carbon\Carbon;
use Illuminate\Validation\Rule;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\Barn;
use App\Models\Pen;
use App\Models\Farm;
use App\Models\Batch;
use App\Models\BatchTreatment;
use App\Models\Cost;
use App\Models\PigSale;
use App\Models\Feeding;
use App\Models\PigDeath;
use App\Models\PigEntryRecord;
use App\Models\DairyRecord;
use App\Models\StoreHouse;
use App\Models\InventoryMovement;


class DashboardController extends Controller
{

public function dashboard()
    {
        $totalPigs = PigEntryRecord::count();
        $totalCosts = Cost::sum('total_price');
        $totalSales = PigSale::sum('total_price');

        return view('admin.view.dashboard', compact('totalPigs', 'totalCosts', 'totalSales'));
    }
}
