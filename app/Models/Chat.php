<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = [
        'customer_id',
        'sender',
        'message',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
