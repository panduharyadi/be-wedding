<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    protected $fillable = [
        'user_id',
        'nama_paket',
        'total',
        'tanggal',
        'status',
        'order_id',
        'paket_id'
    ];

    public function paket()
    {
        return $this->belongsTo(Paket::class, 'paket_id');
    }

    public function rating()
    {
        return $this->hasOne(Rating::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reschedule()
    {
        return $this->hasMany(Reschedule::class);
    }

    public function certificate()
    {
        return $this->hasOne(Certificate::class, 'transaction_id');
    }
}
