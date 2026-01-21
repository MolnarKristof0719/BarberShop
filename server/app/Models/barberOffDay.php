<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarberOffDay extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'barberId',
        'offDay',
    ];

    protected $casts = [
        'offDay' => 'date',
    ];

    public function barber()
    {
        return $this->belongsTo(Barber::class, 'barberId');
    }
}
