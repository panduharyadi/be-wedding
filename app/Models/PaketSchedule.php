<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaketSchedule extends Model
{
    protected $primaryKey = 'pk_paket_schedule_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $appends = ['id'];

    protected $fillable = [
        'paket_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai'
    ];

    public function getIdAttribute()
    {
        return $this->pk_paket_schedule_id;
    }

    public function paket()
    {
        return $this->belongsTo(Paket::class, 'paket_id', 'pk_paket_id');
    }
}
