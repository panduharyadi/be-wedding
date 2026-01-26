<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    protected $primaryKey = 'pk_transaction_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $appends = ['id'];

    protected $fillable = [
        'user_id',
        'nama_paket',
        'total',
        'tanggal',
        'status',
        'order_id',
        'paket_id'
    ];

    public function getIdAttribute()
    {
        return $this->pk_transaction_id;
    }

    public function paket()
    {
        return $this->belongsTo(Paket::class, 'paket_id', 'pk_paket_id');
    }

    public function rating()
    {
        return $this->hasOne(Rating::class, 'transaction_id', 'pk_transaction_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reschedule()
    {
        return $this->hasMany(Reschedule::class, 'transaction_id', 'pk_transaction_id');
    }

    public function certificate()
    {
        return $this->hasOne(Certificate::class, 'transaction_id', 'pk_transaction_id');
    }
}
