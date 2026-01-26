<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $primaryKey = 'pk_chat_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $appends = ['id'];

    protected $fillable = [
        'customer_id',
        'sender',
        'message',
    ];

    public function getIdAttribute()
    {
        return $this->pk_chat_id;
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
