<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchTreatment extends Model
{
    use HasFactory;

    protected $table = 'batch_treatments'; // ชื่อตาราง

    protected $fillable = [

        'pen_id',
        'batch_id',
        'dairy_record_id',

        'medicine_name',
        'medicine_code',
        'quantity',
        'unit',
        'status',
        'note',
        'date',
    ];

    //---------------relation ship------------------------//

    public function pen()
{
    return $this->belongsTo(Pen::class, 'pen_id', 'id'); // pen_id ใน batch_treatments ชี้ไป id ของ pens
}


    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function dairy_record()
    {
        return $this->belongsTo(DairyRecord::class);
    }




}
