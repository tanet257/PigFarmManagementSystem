<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
