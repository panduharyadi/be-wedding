<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reschedule extends Model
{
    protected $primaryKey = 'pk_reschedule_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $appends = ['id'];

    protected $fillable = [
        'user_id',
        'paket_id',
        'transaction_id',
        'tanggal',
        'jam',
        'alasan',
        'status'
    ];

    public function getIdAttribute()
    {
        return $this->pk_reschedule_id;
    }

    public function paket()
    {
        return $this->belongsTo(Paket::class, 'paket_id', 'pk_paket_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transactions::class, 'transaction_id', 'pk_transaction_id');
    }
}
