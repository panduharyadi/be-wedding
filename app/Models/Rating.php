<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $fillable = [
        'user_id',
        'transaction_id',
        'rating',
        'review'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transactions::class);
    }
}
