<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Farm extends Model
{
    use HasFactory;

    protected $table = 'farms'; // ชื่อตาราง

    protected $fillable = [
        'farm_name',
        'barn_capacity',
    ];
    public function barns()
    {
        return $this->hasMany(Barn::class); // assumes 'farm_id' เป็น foreign key ใน table barns
    }
}
