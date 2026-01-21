<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferencePicture extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'picture',
        'barberId',
    ];

    public function barber()
    {
        return $this->belongsTo(Barber::class, 'barberId');
    }
}
