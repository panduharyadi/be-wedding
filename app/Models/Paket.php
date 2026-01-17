<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paket extends Model
{
    protected $fillable = [
        'image',
        'name',
        'price',
        'durasi',
        'benefits',
        'is_rias',
    ];

    protected $casts = [
        'benefits' => 'array',
        'is_rias' => 'boolean',
    ];

    public function schedules()
    {
        return $this->hasMany(PaketSchedule::class);
    }

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
