<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Profit extends Model
{
    use HasFactory;

    protected $fillable = [
        'farm_id',
        'batch_id',
        'total_revenue',
        'total_cost',
        'gross_profit',
        'profit_margin_percent',
        'feed_cost',
        'medicine_cost',
        'transport_cost',
        'excess_weight_cost',
        'labor_cost',
        'utility_cost',
        'other_cost',
        'total_pig_sold',
        'total_pig_dead',
        'profit_per_pig',
        'period_start',
        'period_end',
        'days_in_farm',
        'status',
        // ✅ KPI metrics
        'adg',
        'fcr',
        'fcg',
        'starting_avg_weight',
        'ending_avg_weight',
        'total_feed_bags',
        'total_feed_kg',
        'total_weight_gained',
    ];

    protected $casts = [
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'total_revenue' => 'float',
        'total_cost' => 'float',
        'gross_profit' => 'float',
        'profit_margin_percent' => 'float',
        'feed_cost' => 'float',
        'medicine_cost' => 'float',
        'transport_cost' => 'float',
        'labor_cost' => 'float',
        'utility_cost' => 'float',
        'other_cost' => 'float',
        'profit_per_pig' => 'float',
        // ✅ KPI metrics
        'adg' => 'float',
        'fcr' => 'float',
        'fcg' => 'float',
        'starting_avg_weight' => 'float',
        'ending_avg_weight' => 'float',
        'total_feed_kg' => 'float',
        'total_weight_gained' => 'float',
    ];

    // ความสัมพันธ์
    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function profitDetails()
    {
        return $this->hasMany(ProfitDetail::class);
    }

    public function revenues()
    {
        return $this->hasMany(Revenue::class);
    }

    /**
     * ✅ Calculate Projected Profit based on current KPI metrics
     * Uses ADG, FCR to estimate final weight and profit
     */
    public function calculateProjectedProfit()
    {
        try {
            // Get current batch data
            $batch = $this->batch;
            if (!$batch) {
                return null;
            }

            // Get current data from profit record
            $currentAdg = $this->adg ?? 0;
            $currentFcr = $this->fcr ?? 0;
            $daysInFarm = $this->days_in_farm ?? 0;
            $startingWeight = $this->starting_avg_weight ?? 0;
            $currentWeight = $this->ending_avg_weight ?? 0;
            $totalPigSold = $this->pig_sold_count ?? 0;
            $pigSalePrice = $batch->price_per_pig ?? 0;
            $totalFeedKg = $this->total_feed_kg ?? 0;
            $avgFeedPrice = $totalFeedKg > 0 ? ($this->feed_cost ?? 0) / $totalFeedKg : 0;

            // Target ending weight (usually 100-120 kg based on farm standards)
            $targetWeight = 110; // Can be customized per farm

            // Calculate days remaining to reach target
            if ($currentAdg > 0) {
                $weightRemaining = $targetWeight - $currentWeight;
                $daysRemaining = $weightRemaining / $currentAdg;
            } else {
                $daysRemaining = 0;
            }

            // Total projected days in farm
            $projectedDaysInFarm = $daysInFarm + $daysRemaining;

            // Projected final weight
            $projectedFinalWeight = $currentWeight + ($currentAdg * $daysRemaining);

            // Projected weight gained
            $projectedTotalWeightGained = $projectedFinalWeight - $startingWeight;

            // Projected feed consumption (based on current FCR)
            $projectedTotalFeedKg = $projectedTotalWeightGained * ($currentFcr ?? 2.5); // Default FCR 2.5 if not set

            // Projected feed cost
            $projectedFeedCost = $projectedTotalFeedKg * $avgFeedPrice;

            // Estimate other costs (assume they remain proportional)
            $daysCompletedRatio = $daysInFarm > 0 ? $daysInFarm / $projectedDaysInFarm : 0;
            $currentTotalCost = $this->total_cost ?? 0;
            $projectedTotalCost = $currentTotalCost / max($daysCompletedRatio, 0.5); // Avoid division by very small numbers

            // Projected revenue (assuming current pig count and price remain same)
            $projectedRevenue = $totalPigSold * $pigSalePrice;

            // Projected profit
            $projectedProfit = $projectedRevenue - $projectedTotalCost;
            $projectedMargin = $projectedRevenue > 0 ? ($projectedProfit / $projectedRevenue) * 100 : 0;

            return [
                'projected_final_weight' => round($projectedFinalWeight, 2),
                'projected_total_weight_gained' => round($projectedTotalWeightGained, 2),
                'projected_feed_kg' => round($projectedTotalFeedKg, 2),
                'projected_feed_cost' => round($projectedFeedCost, 2),
                'projected_total_cost' => round($projectedTotalCost, 2),
                'projected_revenue' => round($projectedRevenue, 2),
                'projected_profit' => round($projectedProfit, 2),
                'projected_margin' => round($projectedMargin, 2),
                'projected_days_in_farm' => round($projectedDaysInFarm, 0),
                'days_remaining' => round($daysRemaining, 0),
            ];
        } catch (\Exception $e) {
            Log::error('Profit::calculateProjectedProfit Error: ' . $e->getMessage());
            return null;
        }
    }
}

