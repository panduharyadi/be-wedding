<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaketSchedule extends Model
{
    protected $fillable = [
        'paket_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai'
    ];

    public function paket()
    {
        return $this->belongsTo(Paket::class);
    }
}
