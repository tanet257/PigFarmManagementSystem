<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchMetric extends Model
{
    protected $table = 'batch_metrics';

    protected $fillable = [
        'batch_id',
        'adg',
        'fcr',
        'fcg',
        'total_feed_used',
        'total_mortality',
    ];

    public $timestamps = true;

    protected $casts = [
        'adg' => 'float',
        'fcr' => 'float',
        'fcg' => 'float',
        'total_feed_used' => 'float',
        'total_mortality' => 'integer',
    ];

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
}
