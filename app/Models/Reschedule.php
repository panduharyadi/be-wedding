<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reschedule extends Model
{
    protected $fillable = [
        'user_id',
        'paket_id',
        'transaction_id',
        'tanggal',
        'jam',
        'alasan',
        'status'
    ];

    public function paket()
    {
        return $this->belongsTo(Paket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transactions::class);
    }
}
