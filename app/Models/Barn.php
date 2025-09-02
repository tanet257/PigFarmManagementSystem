<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barn extends Model
{
    use HasFactory;

    protected $table = 'barns'; // ชื่อตาราง

    protected $fillable = [
        'barns_code',
        'pig_capacity',
        'pen_capacity',
        'note'
    ];
}
