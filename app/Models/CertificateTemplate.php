<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateTemplate extends Model
{
    protected $fillable = [
        'owner_id',
        'name',
        'background_path',
        'fields',
    ];

    protected $casts = [
        'fields' => 'array',
    ];

    public function owner() {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function certificates() {
        return $this->hasMany(Certificate::class);
    }
}
