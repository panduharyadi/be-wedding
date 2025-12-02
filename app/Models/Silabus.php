<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Silabus extends Model
{
    protected $fillable = [
        'title',
        'date',
        'time',
        'option_change',
    ];

    public function paket()
    {
        return $this->belongsTo(Paket::class);
    }
}
