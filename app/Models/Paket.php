<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paket extends Model
{
    protected $fillable = [
        'name',
        'price',
        'durasi',
        'benefits',
        'is_rias',
    ];

    public function silabus()
    {
        return $this->hasMany(Silabus::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transactions::class);
    }

    public function reschedule()
    {
        return $this->hasMany(Reschedule::class);
    }
}
