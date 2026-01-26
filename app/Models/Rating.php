<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $primaryKey = 'pk_rating_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $appends = ['id'];

    protected $fillable = [
        'user_id',
        'transaction_id',
        'rating',
        'review'
    ];

    public function getIdAttribute()
    {
        return $this->pk_rating_id;
    }

    public function transaction()
    {
        return $this->belongsTo(Transactions::class, 'transaction_id', 'pk_transaction_id');
    }
}
