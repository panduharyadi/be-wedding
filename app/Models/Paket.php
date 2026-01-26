<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paket extends Model
{
    protected $primaryKey = 'pk_paket_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $appends = ['id'];

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
        return $this->hasMany(PaketSchedule::class, 'paket_id', 'pk_paket_id');
    }

    public function silabus()
    {
        return $this->hasMany(Silabus::class, 'paket_id', 'pk_paket_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transactions::class, 'paket_id', 'pk_paket_id');
    }

    public function reschedule()
    {
        return $this->hasMany(Reschedule::class, 'paket_id', 'pk_paket_id');
    }

    public function getIdAttribute()
    {
        return $this->pk_paket_id;
    }
}
