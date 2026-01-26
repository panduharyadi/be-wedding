<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Silabus extends Model
{
    protected $primaryKey = 'pk_silabus_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $appends = ['id'];

    protected $fillable = [
        'title',
        'date',
        'time',
        'option_change',
    ];

    public function getIdAttribute()
    {
        return $this->pk_silabus_id;
    }

    public function paket()
    {
        return $this->belongsTo(Paket::class, 'paket_id', 'pk_paket_id');
    }
}
